<?php
	session_start();
	if (!isset($_SESSION["login"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	if ($_POST["registration_code"] == $_SESSION["registration_code"]) {
		$link = mysqli_connect("localhost", "root", "", "regchula_courses");
		$query = "UPDATE registration_code
					SET login_status = 1
					WHERE std_id = '$_SESSION[student_id]'
					AND year = '$_SESSION[academic_year]'
					AND semester = $_SESSION[int_semester]";
		$result = mysqli_query($link, $query);
		echo "1";
	} else {
		echo "0";
	}
?>