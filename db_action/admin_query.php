<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = $_GET["mode"];
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  $is_std_id = "/^\d{10} .+$/";
  $is_course_id = "/^\d{7} .+$/";
  if (preg_match($is_std_id, $term)) {
    $term = substr($term, 0, 10);
  } elseif (preg_match($is_course_id, $term)) {
    $term = substr($term, 0, 7);
  }
  if ($mode == "std") {
    $stmt = $link->prepare("SELECT std_id AS id, CONCAT(fname_th, ' ', lname_th) AS name
                            FROM student
                            WHERE std_id LIKE CONCAT(?, '%')
                            OR CONCAT(fname_th, ' ', lname_th) LIKE CONCAT('%', ?, '%')
                            OR CONCAT(fname_en, ' ', lname_en) LIKE CONCAT('%', ?, '%')
                            LIMIT 10");
    $stmt->bind_param("sss", $term, $term, $term);
  } elseif ($mode == "course") {
    $stmt = $link->prepare("SELECT course_id AS id, course_en_name AS name
                            FROM course
                            WHERE course_id LIKE CONCAT(?, '%')
                            OR course_en_name LIKE CONCAT('%', ?, '%')
                            OR course_th_name LIKE CONCAT('%', ?, '%')
                            OR course_short_name LIKE CONCAT('%', ?, '%')
                            UNION
                            SELECT group_course_id AS id, NULL AS name
                            FROM group_course
                            WHERE group_course_id LIKE CONCAT(?, '%')
                            LIMIT 10");
    $stmt->bind_param("sssss", $term, $term, $term, $term, $term);
  } else {
    mysqli_close($link);
    exit();
  }
  $stmt->execute();
  $result = $stmt->get_result();
  $return = array();
  while ($row = mysqli_fetch_array($result)) {
    $result_name = $row["name"];
    $name = (is_null($result_name)) ? "รายวิชาแบบกลุ่ม" : $result_name;
    array_push($return, $row["id"]." ".$name);
  }
  echo json_encode($return);
  mysqli_close($link);
?>