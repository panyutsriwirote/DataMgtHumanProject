<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $course_id = mysqli_real_escape_string($link, $_GET["course_id"]);
  $stmt = $link->prepare("SELECT *
                          FROM course, section, slot
                          WHERE course.course_id = ?
                          AND course.course_id = section.course_id
                          AND section.course_id = slot.course_id
                          AND section.sect_num = slot.sect_num
                          ORDER BY section.sect_num, slot_id");
  $stmt->bind_param("s", $course_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if (mysqli_num_rows($result) == 0) {
    $stmt = $link->prepare("SELECT *
                            FROM group_course, course
                            WHERE group_course_id = ?
                            AND group_course.course_id = course.course_id");
    $stmt->bind_param("s", $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if (mysqli_num_rows($result) == 0) {
      exit("<h1>ไม่พบรายวิชา</h1>");
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
    while ($row = mysqli_fetch_array($result)) {
      if ($cur_num == 1) {
        echo "<p id=course_info style=text-align:center;>$row[group_course_id]&nbsp&nbspรายวิชาแบบกลุ่ม</p>";
      }
      $class = ($cur_num % 2) + 1;
      echo "<tr class=color$class>";
      echo "<td>$cur_num</td>";
      echo "<td>$row[course_id]</td>";
      echo "<td>$row[course_en_name]</td>";
      echo "<td class=gr_sect>$row[section]</td>";
      echo "<td>$row[credit]</td>";
      echo "</tr>";
      $total_credit += $row["credit"];
      $cur_num++;
    }
    echo "<p style=text-align:center;>[$total_credit&nbspหน่วยกิต]</p>";
    echo "<p style=text-align:center;><input type=submit value=ลงทะเบียนรายวิชา></p>";
    echo "</form>";
  } else {
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
        echo "<p style=text-align:center;>$row[course_th_name]&nbsp&nbsp[$row[credit]&nbspหน่วยกิต]</p>";
        echo "<p style=text-align:center;><input type=submit value=ลงทะเบียนรายวิชา></p>";
        if (in_array($row["course_en_name"], ["THESIS", "DISSERTATION"])) {
          echo "<p style=text-align:center;><label for=credit>เลือกหน่วยกิต</label>&nbsp&nbsp
            <input
              type=number
              step=0.5
              min=0.5
              max=$row[credit]
              id=credit
              placeholder=หน่วยกิต
              style=text-align:center;>
          </p>";
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
  }
  mysqli_close($link);
?>