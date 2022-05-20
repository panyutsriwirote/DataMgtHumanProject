<?php
	session_start();
	if (!isset($_SESSION["login"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	if ($_POST["registration_code"] == $_SESSION["registration_code"]) {
		$link = mysqli_connect("localhost", "root", "", "regchula_courses");
		$update = "UPDATE student_reg
					SET login_status = 1
					WHERE std_id = '$_SESSION[student_id]'
					AND semester_id = $_SESSION[semester_id]";
		$result = mysqli_query($link, $update);
		mysqli_close($link);
		$_SESSION["entered_registration_code"] = true;
		echo "1";
	} else {
		echo "0";
	}
?>