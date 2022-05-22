<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = $_GET["mode"];
  $term = mysqli_real_escape_string($link, $_GET["term"]);
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
                            LIMIT 10");
    $stmt->bind_param("ssss", $term, $term, $term, $term);
  } else {
    mysqli_close($link);
    exit();
  }
  $stmt->execute();
  $result = $stmt->get_result();
  $return = array();
  while ($row = mysqli_fetch_array($result)) {
    array_push($return, $row["id"]." ".$row["name"]);
  }
  echo json_encode($return);
  mysqli_close($link);
?>