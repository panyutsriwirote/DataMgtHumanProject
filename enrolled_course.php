<?php
	session_start();
	if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
	$link = mysqli_connect("localhost", "root", "", "regchula_courses");
	$query = "SELECT registration.course_id, course_en_name, GROUP_CONCAT(sect_num) AS section, credit
				FROM registration, course
				WHERE registration.course_id = course.course_id
				AND registration.std_id = '$_SESSION[student_id]'
				AND registration.semester_id = $_SESSION[semester_id]
				GROUP BY registration.course_id
				UNION
				SELECT registration_t.course_id, course_en_name, GROUP_CONCAT(sect_num) AS section, selected_credit AS credit
				FROM registration_t, course
				WHERE registration_t.course_id = course.course_id
				AND registration_t.std_id = '$_SESSION[student_id]'
				AND registration_t.semester_id = $_SESSION[semester_id]
				GROUP BY registration_t.course_id";
	$result = mysqli_query($link, $query);
	if (mysqli_num_rows($result) == 0) {
		mysqli_close($link);
		echo "<h1>ยังไม่มีรายวิชาที่ลงทะเบียนเรียน</h1>";
		exit();
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
	function switch_class($class) {
		if ($class == "color1") {
		  return "color2";
		} else {
		  return "color1";
		}
	}
	function group_num($string) {
		$arr = explode(",", $string);
		$grouped_num = array();
		for ($i = 0; $i < count($arr); $i++) {
			$num = intval($arr[$i]);
			if ($i > 0 && $arr[$i-1] == $num-1) {
				array_push($grouped_num[count($grouped_num)-1], $num);
				continue;
			}
			array_push($grouped_num, [$num]);
		}
		$num_range = array();
		foreach ($grouped_num as $group) {
			if (count($group) == 1) {
				array_push($num_range, $group[0]);
			} else {
				$begin = $group[0];
				$end = end($group);
				array_push($num_range, "$begin-$end");
			}
		}
		return $num_range;
	}
	while ($row = mysqli_fetch_array($result)) {
		echo "<tr class=$class>";
		echo "<td>$num</td>";
		echo "<td class=course_id>$row[course_id]</td>";
		echo "<td class=course_name>$row[course_en_name]</td>";
		$grouped_sect = join(",", group_num($row["section"]));
		echo "<td>$grouped_sect</td>";
		echo "<td>$row[credit]</td>";
		echo "<td><input type=button class=edit value=แก้ไข></td>";
		echo "<td><input type=button class=delete value=ลบ></td>";
		echo "</tr>";
		$num++;
		$class = switch_class($class);
	}
	echo "</table>";
	mysqli_close($link);
?>