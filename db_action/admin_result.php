<?php
  session_start();
  if (!isset($_SESSION["is_admin"])) {
    header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden", true, 403);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = $_GET["mode"];
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  function switch_class($class) {
    if ($class == "color1") {
      return "color2";
    } else {
      return "color1";
    }
  }
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
  if ($mode == "std") {
    $full_term = "/\d{10} .+/";
    if (preg_match($full_term, $term)) {
      $term = substr($term, 0, 10);
    }
    $stmt = $link->prepare("SELECT registration.course_id, course_en_name, GROUP_CONCAT(sect_num) AS section, credit, date_time AS time
                            FROM registration, course
                            WHERE registration.course_id = course.course_id
                            AND registration.std_id = ?
                            AND registration.semester_id = $_SESSION[semester_id]
                            GROUP BY registration.course_id, time
                            UNION
                            SELECT registration_t.course_id, course_en_name, GROUP_CONCAT(sect_num) AS section, selected_credit AS credit, date_time AS time
                            FROM registration_t, course
                            WHERE registration_t.course_id = course.course_id
                            AND registration_t.std_id = ?
                            AND registration_t.semester_id = $_SESSION[semester_id]
                            GROUP BY registration_t.course_id, time
                            ORDER BY time");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    if (mysqli_num_rows($result) == 0) {
      mysqli_close($link);
      echo "<h1>ไม่พบข้อมูล</h1>";
      exit();
    }
    echo "<table class=enrolled_course>";
    echo "<tr>";
    echo "<th>ที่</th>";
    echo "<th>รหัสรายวิชา</th>";
    echo "<th>ชื่อรายวิชา</th>";
    echo "<th>ตอนเรียน</th>";
    echo "<th>หน่วยกิต</th>";
    echo "<th>เวลา</th>";
    echo "</tr>";
    $num = 1;
    $class = "color2";
    while ($row = mysqli_fetch_array($result)) {
      echo "<tr class=$class>";
      echo "<td>$num</td>";
      echo "<td class=course_id>$row[course_id]</td>";
      echo "<td class=course_name>$row[course_en_name]</td>";
      $grouped_sect = join(",", group_num($row["section"]));
      echo "<td>$grouped_sect</td>";
      $course_credit = $row["credit"];
      if (is_null($course_credit)) {
        $course_credit = "-";
      }
      echo "<td class=course_credit>$course_credit</td>";
      echo "<td>$row[time]</td>";
      echo "</tr>";
      $num++;
      $class = switch_class($class);
    }
    echo "</table>";
  } elseif ($mode == "course") {
    $full_term = "/\d{7} .+/";
    if (preg_match($full_term, $term)) {
      $term = substr($term, 0, 7);
    }
    $stmt = $link->prepare("SELECT student.std_id, CONCAT(fname_th, ' ', lname_th) AS std_name, GROUP_CONCAT(sect_num) AS section, date_time AS time
                            FROM student, registration
                            WHERE student.std_id = registration.std_id
                            AND semester_id = $_SESSION[semester_id]
                            AND course_id = ?
                            GROUP BY registration.std_id, time
                            UNION
                            SELECT student.std_id, CONCAT(fname_th, ' ', lname_th) AS std_name, GROUP_CONCAT(sect_num) AS section, date_time AS time
                            FROM student, registration_t
                            WHERE student.std_id = registration_t.std_id
                            AND semester_id = $_SESSION[semester_id]
                            AND course_id = ?
                            GROUP BY registration_t.std_id, time
                            ORDER BY time");
    $stmt->bind_param("ss", $term, $term);
    $stmt->execute();
    $result = $stmt->get_result();
    if (mysqli_num_rows($result) == 0) {
      mysqli_close($link);
      echo "<h1>ไม่พบข้อมูล</h1>";
      exit();
    }
    echo "<table>";
    echo "<tr>";
    echo "<th>รหัสนิสิต</th>";
    echo "<th>ชื่อนิสิต</th>";
    echo "<th>ตอนเรียน</th>";
    echo "<th>เวลา</th>";
    echo "</tr>";
    $class = "color2";
    while ($row = mysqli_fetch_array($result)) {
      echo "<tr class=$class>";
      echo "<td>$row[std_id]</td>";
      echo "<td>$row[std_name]</td>";
      $grouped_sect = join(",", group_num($row["section"]));
      echo "<td>$grouped_sect</td>";
      echo "<td>$row[time]</td>";
      echo "</tr>";
      $class = switch_class($class);
    }
    echo "</table>";
  } else {
    mysqli_close($link);
    echo "<h1>ไม่พบข้อมูล</h1>";
    exit();
  }
  mysqli_close($link);
?>