<?php
  session_start();
  if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $course_id = $_POST["course_id"];
  $regex = "/^\d{7}$/";
  if (!preg_match($regex, $course_id)) {
    mysqli_close($link);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $delete1 = "DELETE FROM registration
              WHERE std_id = '$_SESSION[student_id]'
              AND semester_id = $_SESSION[semester_id]
              AND course_id = '$course_id'";
  $delete2 = "DELETE FROM registration_t
              WHERE std_id = '$_SESSION[student_id]'
              AND semester_id = $_SESSION[semester_id]
              AND course_id = '$course_id'";
  mysqli_query($link, $delete1);
  mysqli_query($link, $delete2);
  mysqli_close($link);
?>