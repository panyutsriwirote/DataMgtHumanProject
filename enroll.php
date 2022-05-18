<?php
	session_start();
	if (!isset($_SESSION["login"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	$student_id = $_SESSION["student_id"];
	$academic_year = $_SESSION["academic_year"];
	$semester = $_SESSION["semester"];
	// $link = mysqli_connect("localhost", "root", "", "regchula_courses");
	// $stmt = $link->prepare("");
	// $stmt->bind_param("s", $id);
	// $stmt->execute();
	// $result = $stmt->get_result();
	echo "|$_POST[course_id]|";
	if (empty($_POST["credit"])) {
		echo "|NULL|";
	} else {
		echo "|$_POST[credit]|";
	}
	if ($_POST["enrolled_sect"] == "group") {
		echo "group";
	} else {
		foreach ($_POST["enrolled_sect"] as $elem) {
			echo "|$elem|";
		}
	}
?>