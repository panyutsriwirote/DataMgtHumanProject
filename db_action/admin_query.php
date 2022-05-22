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
    $stmt = $link->prepare("SELECT *
                            FROM student
                            WHERE std_id LIKE CONCAT(?, '%')
                            OR CONCAT(fname_th, ' ', lname_th) LIKE CONCAT('%', ?, '%')
                            OR CONCAT(fname_en, ' ', lname_en) LIKE CONCAT('%', ?, '%')
                            LIMIT 10");
    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();
    $result = $stmt->get_result;
  } elseif ($mode == "course") {
    $stmt = $link->prepare("SELECT *
                            FROM course
                            WHERE std_id LIKE CONCAT(?, '%')
                            OR CONCAT(fname_th, ' ', lname_th) LIKE CONCAT('%', ?, '%')
                            OR CONCAT(fname_en, ' ', lname_en) LIKE CONCAT('%', ?, '%')
                            LIMIT 10");
    $stmt->bind_param("sss", $term, $term, $term);
    $stmt->execute();
    $result = $stmt->get_result;
  } else {
    mysqli_close($link);
    exit();
  }
  $return = [$mode, $term];
  echo json_encode($return);
  mysqli_close($link);
?>