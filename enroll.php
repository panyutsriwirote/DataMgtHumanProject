<?php
	session_start();
	if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	$course_id = $_POST["course_id"];
	$regex = "/^\d{7}$/";
	if (!preg_match($regex, $course_id)) {
		exit();
	}
	$link = mysqli_connect("localhost", "root", "", "regchula_courses");
	$query = "SELECT course_en_name AS name, course_id AS id, NULL AS course_id, NULL AS sect_num
				FROM course
				WHERE course_id = '$course_id'
				UNION
				SELECT NULL AS name, group_course_id AS id, course_id, sect_num
				FROM group_course
				WHERE group_course_id = '$course_id'";
	$result = mysqli_query($link, $query);
	$num_row = mysqli_num_rows($result);
	if ($num_row == 0) {
		mysqli_close($link);
		exit();
	}
	$std_id = $_SESSION["student_id"];
	$semester_id = $_SESSION["semester_id"];
	$is_group = ($num_row == 1) ? false : true;
	if ($is_group) {
		$insert = "INSERT INTO registration VALUES ";
		$values = array();
		while ($course = mysqli_fetch_array($result)) {
			array_push($values, "('$std_id', $semester_id, '$course[course_id]', $course[sect_num], NULL)");
		}
		$insert = $insert.join(",", $values);
		mysqli_query($link, $insert);
		mysqli_close($link);
	} else {
		while ($course = mysqli_fetch_array($result)) {
			$is_thesis = (in_array($course["name"], ["THESIS", "DISSERTATION"])) ? true : false;
			if ($is_thesis) {
				$credit = $_POST["credit"];
				$regex = "/^([123456789]\d*(.0|.5)?|0.5)$/";
				if (!preg_match($regex, $credit)) {
					mysqli_close($link);
					exit();
				}
				$check_max_credit = "SELECT credit FROM course WHERE course_id = '$course_id'";
				$credit_result = mysqli_query($link, $check_max_credit);
				while ($max_credit = mysqli_fetch_array($credit_result)) {
					if (intval($credit) > $max_credit["credit"]) {
						mysqli_close($link);
						exit();
					}
				}
				$insert = "REPLACE INTO registration_t VALUES ";
				$values = array();
				$regex = "/^\d+$/";
				foreach ($_POST["enrolled_sect"] as $sect) {
					if (!preg_match($regex, $sect)) {
						mysqli_close($link);
						exit();
					}
					array_push($values, "('$std_id', $semester_id, '$course[id]', $sect, $credit, NULL)");
				}
				$insert = $insert.join(",", $values);
				mysqli_query($link, $insert);
				if (empty($_POST["to_delete"])) {
					mysqli_close($link);
					exit();
				}
				$delete = "DELETE FROM registration_t
							WHERE std_id = '$std_id'
							AND semester_id = $semester_id
							AND course_id = '$course[id]'
							AND sect_num IN (";
				$sect_arr = array();
				foreach ($_POST["to_delete"] as $sect) {
					if (!preg_match($regex, $sect)) {
						mysqli_close($link);
						exit();
					}
					array_push($sect_arr, "$sect");
				}
				$delete = $delete.join(",", $sect_arr).")";
				mysqli_query($link, $delete);
				mysqli_close($link);
			} else {
				$insert = "INSERT IGNORE INTO registration VALUES ";
				$values = array();
				$regex = "/^\d+$/";
				foreach ($_POST["enrolled_sect"] as $sect) {
					if (!preg_match($regex, $sect)) {
						mysqli_close($link);
						exit();
					}
					array_push($values, "('$std_id', $semester_id, '$course[id]', $sect, NULL)");
				}
				$insert = $insert.join(",", $values);
				mysqli_query($link, $insert);
				if (empty($_POST["to_delete"])) {
					mysqli_close($link);
					exit();
				}
				$delete = "DELETE FROM registration
							WHERE std_id = '$std_id'
							AND semester_id = $semester_id
							AND course_id = '$course[id]'
							AND sect_num IN (";
				$sect_arr = array();
				foreach ($_POST["to_delete"] as $sect) {
					if (!preg_match($regex, $sect)) {
						mysqli_close($link);
						exit();
					}
					array_push($sect_arr, "$sect");
				}
				$delete = $delete.join(",", $sect_arr).")";
				echo $delete;
				mysqli_query($link, $delete);
				mysqli_close($link);
			}
		}
	}
?>