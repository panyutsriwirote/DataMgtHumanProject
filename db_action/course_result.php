<?php
  session_start();
  if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
    header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden", true, 403);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $search_term = mysqli_real_escape_string($link, trim($_GET["search_term"]));
  $regex = "/^\d{7} .+$/";
  if (preg_match($regex, $search_term)) {
    $search_term = substr($search_term, 0, 7);
  }
  $stmt = $link->prepare("SELECT course_id AS id
                          FROM course
                          WHERE course_id LIKE CONCAT(?, '%')
                          OR course_en_name LIKE CONCAT('%', ?, '%')
                          OR course_short_name LIKE CONCAT('%', ?, '%')
                          OR course_th_name LIKE CONCAT('%', ?, '%')
                          UNION
                          SELECT group_course_id AS id
                          from group_course
                          WHERE group_course_id LIKE CONCAT(?, '%')");
  $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $search_term);
  $stmt->execute();
  $result = $stmt->get_result();
  $num_row = mysqli_num_rows($result);
  if ($num_row != 1) {
    mysqli_close($link);
    echo ($num_row == 0) ? "<h1>ไม่พบรายวิชา</h1>" : "<h1>พบรายวิชามากกว่า 1 รายวิชา</h1>";
    exit();
  }
  while ($row = mysqli_fetch_array($result)) {
    $course_id = $row["id"];
  }
  $query = "SELECT *
            FROM course, section, slot
            WHERE course.course_id = '$course_id'
            AND course.course_id = section.course_id
            AND section.course_id = slot.course_id
            AND section.sect_num = slot.sect_num
            ORDER BY section.sect_num, slot_id";
  $result = mysqli_query($link, $query);
  if (mysqli_num_rows($result) == 0) {
    $query = "SELECT group_course_id, group_course.course_id, course_en_name, credit, GROUP_CONCAT(sect_num) AS sections
              FROM group_course, course
              WHERE group_course_id = '$course_id'
              AND group_course.course_id = course.course_id
              GROUP BY group_course.course_id";
    $result = mysqli_query($link, $query);
    if (mysqli_num_rows($result) == 0) {
      mysqli_close($link);
      echo "<h1>ไม่พบรายวิชา</h1>";
      exit();
    }
    echo "<form id=gr_enroll_form>";
    echo "<table>";
    echo "<td>&nbsp</td>";
    echo "<tr>";
    echo "<th>ลำดับที่</th>";
    echo "<th>รหัสรายวิชา</th>";
    echo "<th>ชื่อรายวิชา</th>";
    echo "<th>ตอนเรียน</th>";
    echo "<th>หน่วยกิต</th>";
    echo "</tr>";
    $cur_num = 1;
    $total_credit = 0;
    function group_num($string) {
      $arr = explode(",", $string);
      sort($arr);
      $grouped_num = array();
      for ($i = 0; $i < count($arr); $i++) {
        $num = intval($arr[$i]);
        if ($i > 0 && $arr[$i-1] == $num-1) {
          array_push($grouped_num[count($grouped_num)-1], $num);
          continue;
        }
        array_push($grouped_num, [$num]);
      }
      $num_range = array();
      foreach ($grouped_num as $group) {
        if (count($group) == 1) {
          array_push($num_range, $group[0]);
        } else {
          $begin = $group[0];
          $end = end($group);
          array_push($num_range, "$begin-$end");
        }
      }
      return $num_range;
    }
    while ($row = mysqli_fetch_array($result)) {
      if ($cur_num == 1) {
        echo "<p id=course_info style=text-align:center;>$row[group_course_id]&nbsp&nbspรายวิชาแบบกลุ่ม</p>";
      }
      $class = ($cur_num % 2) + 1;
      echo "<tr class=color$class>";
      echo "<td>$cur_num</td>";
      echo "<td>$row[course_id]</td>";
      echo "<td>$row[course_en_name]</td>";
      $section_range = join(",", group_num($row["sections"]));
      echo "<td>$section_range</td>";
      echo "<td>$row[credit]</td>";
      echo "</tr>";
      $total_credit += $row["credit"];
      $cur_num++;
    }
    echo "<p style=text-align:center;>[$total_credit&nbspหน่วยกิต]</p>";
    echo "<p style=text-align:center;><input type=submit value=ลงทะเบียนรายวิชา></p>";
    echo "</form>";
    mysqli_close($link);
  } else {
    $sect_query = "SELECT GROUP_CONCAT(sect_num) AS sections, NULL AS credit
                  FROM registration
                  WHERE course_id = '$course_id'
                  AND std_id = '$_SESSION[student_id]'
                  GROUP BY course_id
                  UNION
                  SELECT GROUP_CONCAT(sect_num) AS sections, selected_credit AS credit
                  FROM registration_t
                  WHERE course_id = '$course_id'
                  AND std_id = '$_SESSION[student_id]'
                  GROUP BY course_id
                  LIMIT 1";
    $sect_result = mysqli_query($link, $sect_query);
    $default_credit = null;
    $enrolled_sect = array();
    if (mysqli_num_rows($sect_result) != 0) {
      while ($row = mysqli_fetch_array($sect_result)) {
          array_push($enrolled_sect, $row["sections"]);
          $sect_credit = $row["credit"];
          if (!is_null($sect_credit)) {
            $default_credit = $sect_credit;
          }
      }
      sort($enrolled_sect);
      $string_enrolled_sect = join(",", $enrolled_sect);
      echo "<div id=already_enrolled_sect style=display:none;>$string_enrolled_sect</div>";
    }
    echo "<form id=enroll_form>";
    echo "<table>";
    echo "<td><label for=select_all>เลือกทั้งหมด</label><br><input type=checkbox id=select_all></td>";
    echo "<tr>";
    echo "<th>ลงทะเบียน</th>";
    echo "<th>จำนวนนิสิต</th>";
    echo "<th>ตอนเรียน</th>";
    echo "<th>วิธีสอน</th>";
    echo "<th>วัน</th>";
    echo "<th>เวลาเรียน</th>";
    echo "<th>อาคาร</th>";
    echo "<th>ห้อง</th>";
    echo "<th>ผู้สอน</th>";
    echo "<th>หมายเหตุ</th>";
    echo "</tr>";
    $cur_sect = "";
    $class = "color1";
    function switch_class($class) {
      if ($class == "color1") {
        return "color2";
      } else {
        return "color1";
      }
    }
    while ($row = mysqli_fetch_array($result)) {
      if ($cur_sect == "") {
        echo "<p id=course_info style=text-align:center;>$row[course_id]&nbsp&nbsp$row[course_en_name]</p>";
        $course_credit = $row["credit"];
        if (is_null($course_credit)) {
          $course_credit = "-";
        }
        echo "<p style=text-align:center;>$row[course_th_name]&nbsp&nbsp[$course_credit&nbspหน่วยกิต]</p>";
        $button_text = (count($enrolled_sect) == 0) ? "ลงทะเบียนรายวิชา" : "ยืนยันการแก้ไข";
        echo "<p style=text-align:center;><input id=submit_form type=submit value=$button_text></p>";
        if (in_array($row["course_en_name"], ["THESIS", "DISSERTATION"])) {
          echo "<p style=text-align:center;>";
          echo "<label for=credit>เลือกหน่วยกิต</label>&nbsp&nbsp";
          if (is_null($default_credit)) {
            echo "<input type=number step=0.5 min=0.5 max=$row[credit] id=credit placeholder=หน่วยกิต style=text-align:center;>";
          } else {
            echo "<input type=number step=0.5 min=0.5 max=$row[credit] value=$default_credit id=credit placeholder=หน่วยกิต style=text-align:center;>";
          }
          echo "</p>";
        }
      }
      $sect_num = $row["sect_num"];
      if ($cur_sect != $sect_num) {
        $class = switch_class($class);
        echo "<tr class=$class>";
        if ($row["sect_status"] == "close") {
          echo "<td valign=TOP>-</td>";
          echo "<td valign=TOP><span style=color:red>ปิด</span></td>";
        } elseif ($row["registered"] >= $row["maximum"]) {
          echo "<td valign=TOP>-</td>";
          echo "<td valign=TOP><span style=color:red>$row[registered]/$row[maximum]</span></td>";
        } else {
          echo "<td valign=TOP><input class=enroll type=checkbox value=$sect_num></td>";
          echo "<td valign=TOP>$row[registered]/$row[maximum]</td>";
        }
        echo "<td valign=TOP>$sect_num</td>";
        $cur_sect = $sect_num;
      } else {
        echo "<tr class=$class>";
        echo "<td></td>";
        echo "<td></td>";
        echo "<td></td>";
      }
      echo "<td valign=TOP>&nbsp$row[teach_method]&nbsp</td>";
      echo "<td valign=TOP>&nbsp$row[day]&nbsp</td>";
      echo "<td valign=TOP>&nbsp$row[time]&nbsp</td>";
      echo "<td valign=TOP>&nbsp$row[building]&nbsp</td>";
      echo "<td valign=TOP>&nbsp$row[room]&nbsp</td>";
      echo "<td valign=TOP>&nbsp$row[teacher]&nbsp</td>";
      $note = str_replace("\n", "<br>", $row["note"]);
      echo "<td valign=TOP>$note</td>";
      echo "</tr>";
    }
    echo "</table>";
    echo "</form>";
    mysqli_close($link);
  }
?>