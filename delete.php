<?php
  session_start();
  if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $course_id = mysqli_real_escape_string($link, $_POST["course_id"]);
  $stmt = $link->prepare("DELETE FROM registration
                          WHERE std_id = '$_SESSION[student_id]'
                          AND semester_id = $_SESSION[semester_id]
                          AND course_id = ?");
  $stmt->bind_param("s", $course_id);
  $stmt->execute();
  mysqli_close($link);
?>