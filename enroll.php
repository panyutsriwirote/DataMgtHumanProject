<?php
	session_start();
	if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	$link = mysqli_connect("localhost", "root", "", "regchula_courses");
	$course_id = mysqli_real_escape_string($link, $_POST["course_id"]);
	$regex = "/^\d{7}$/";
	if (!preg_match($regex, $course_id)) {
		mysqli_close($link);
		exit();
	}
	$std_id = $_SESSION["student_id"];
	$semester_id = $_SESSION["semester_id"];
	if ($_POST["enrolled_sect"] == "group") {
		$stmt = $link->prepare("SELECT course_id, sect_num
								FROM group_course
								WHERE group_course_id = ?");
		$stmt->bind_param("s", $course_id);
		$stmt->execute();
		$result = $stmt->get_result();
		$query = "INSERT INTO registration VALUES ";
		$values = array();
		while ($row = mysqli_fetch_array($result)) {
			array_push($values, "('$std_id', $semester_id, '$row[course_id]', $row[sect_num], NULL)");
		}
		$query = $query.join(",", $values);
		$enroll = mysqli_query($link, $query);
	} elseif (empty($_POST["credit"])) {
		$query = "INSERT INTO registration VALUES ";
		$values = array();
		$regex = "/^\d+$/";
		foreach ($_POST["enrolled_sect"] as $sect) {
			if (!preg_match($regex, $sect)) {
				mysqli_close($link);
				exit();
			}
			$sect_num = mysqli_real_escape_string($link, $sect);
			array_push($values, "('$std_id', $semester_id, '$course_id', $sect_num, NULL)");
		}
		$query = $query.join(",", $values);
		$enroll = mysqli_query($link, $query);
	} else {
		$credit = mysqli_real_escape_string($link, $_POST["credit"]);
		$regex = "/^([123456789]\d*(.0|.5)?|0.5)$/";
		if (!preg_match($regex, $credit)) {
			mysqli_close($link);
			exit();
		}
		$check_max_credit = $link->prepare("SELECT credit FROM course WHERE course_id = ? LIMIT 1");
		$check_max_credit->bind_param("s", $course_id);
		$check_max_credit->execute();
		$result = $check_max_credit->get_result();
		while ($row = mysqli_fetch_array($result)) {
			$max_credit = $row["credit"];
		}
		if (intval($credit) > $max_credit) {
			mysqli_close($link);
			exit();
		}
		$query = "INSERT INTO registration_t VALUES ";
		$values = array();
		$regex = "/^\d+$/";
		foreach ($_POST["enrolled_sect"] as $sect) {
			if (!preg_match($regex, $sect)) {
				mysqli_close($link);
				exit();
			}
			$sect_num = mysqli_real_escape_string($link, $sect);
			array_push($values, "('$std_id', $semester_id, '$course_id', $sect_num, $credit, NULL)");
		}
		$query = $query.join(",", $values);
		$enroll = mysqli_query($link, $query);
	}
	mysqli_close($link);
?>