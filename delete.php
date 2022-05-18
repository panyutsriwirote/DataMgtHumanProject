<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $student_id = $_SESSION["student_id"];
	$academic_year = $_SESSION["academic_year"];
	$semester = $_SESSION["semester"];
  echo "YOYO";
?>