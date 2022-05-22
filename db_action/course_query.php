<?php
  session_start();
  if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = $_GET["mode"];
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  if ($mode == "num") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name
                            FROM course
                            WHERE course_id LIKE CONCAT(?, '%')
                            UNION
                            SELECT DISTINCT group_course_id AS course_id, NULL AS course_name
                            FROM group_course
                            WHERE group_course_id LIKE CONCAT(?, '%')
                            LIMIT 10");
    $stmt->bind_param("ss", $term, $term);
  } elseif ($mode == "en") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name
                            FROM course
                            WHERE course_en_name LIKE CONCAT('%', ?, '%')
                            OR course_short_name LIKE CONCAT('%', ?, '%')
                            LIMIT 10");
    $stmt->bind_param("ss", $term, $term);
  } elseif ($mode == "th") {
    $stmt = $link->prepare("SELECT course_id, course_th_name AS course_name
                            FROM course
                            WHERE course_th_name LIKE CONCAT('%', ?, '%')
                            LIMIT 10");
    $stmt->bind_param("s", $term);
  } else {
    mysqli_close($link);
    exit();
  }
  $stmt->execute();
  $result = $stmt->get_result();
  $return = array();
  while ($row = mysqli_fetch_array($result)) {
    $result_name = $row["course_name"];
    $course_name = (is_null($result_name)) ? "รายวิชาแบบกลุ่ม" : $result_name;
    array_push($return, $row["course_id"]." ".$course_name);
  }
  echo json_encode($return);
  mysqli_close($link);
?>