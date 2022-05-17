<?php
	session_start();
	$link = mysqli_connect("localhost", "root", "", "regchula_courses");
	$registration_code = mysqli_real_escape_string($link, $_POST["registration_code"]);
	$academic_year = $_SESSION["academic_year"];
	$semester = $_SESSION["semester"];
	$student_id = $_SESSION["student_id"];
	if ($registration_code == "12345678") {
		echo "1";
	} else {
		echo "รหัสลงทะเบียนเรียนไม่ถูกต้อง";
	}
?>