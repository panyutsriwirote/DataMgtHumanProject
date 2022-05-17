<?php
  session_start();
  $_SESSION["login"] = 1;
  $_SESSION["academic_year"] = "2564";
  $_SESSION["semester"] = "ปลาย";
  $_SESSION["student_name"] = "ปานญุตม์ ศรีวิโรจน์";
  $_SESSION["student_id"] = "6340138322";
	$_SESSION["student_faculty"] = "คณะอักษรศาสตร์";
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $id = mysqli_real_escape_string($link, $_POST["id"]);
  $password = mysqli_real_escape_string($link, $_POST["password"]);
  if ($id == "6340138322" && $password == "12345678") {
    echo "1";
  } else {
    echo "เลขประจำตัวหรือรหัสผ่านไม่ถูกต้อง";
  }
?>