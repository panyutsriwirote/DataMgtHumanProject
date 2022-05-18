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
	$result = [
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["0123101", "PARAGRAPH WRITINGXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX", "2-10,12-20", "3"],
		["2228202", "ARABIC IV", "1-2", "3"]
	];
	// $result = [];
	if ($result == []) {
		echo "<table><td>ยังไม่มีรายวิชาที่ลงทะเบียนเรียน</td></table>";
		exit();
	}
	function switch_class($class) {
		if ($class == "color1") {
		  return "color2";
		} else {
		  return "color1";
		}
	}
	echo "<table class=enrolled_course>";
	echo "<tr>";
	echo "<th>ที่</th>";
	echo "<th>รหัสรายวิชา</th>";
	echo "<th>ชื่อรายวิชา</th>";
	echo "<th>ตอนเรียน</th>";
	echo "<th>หน่วยกิต</th>";
	echo "</tr>";
	$num = 1;
	$class = "color2";
	foreach ($result as $course) {
		echo "<tr class=$class>";
		echo "<td>$num</td>";
		echo "<td class=course_id>$course[0]</td>";
		echo "<td>$course[1]</td>";
		echo "<td>$course[2]</td>";
		echo "<td>$course[3]</td>";
		echo "<td><input type=button class=edit value=แก้ไข></td>";
		echo "<td><input type=button class=delete value=ลบ></td>";
		echo "</tr>";
		$num++;
		$class = switch_class($class);
	}
	echo "</table>";
?>