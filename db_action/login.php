<?php
  session_start();
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $std_id = mysqli_real_escape_string($link, $_POST["id"]);
  if ($std_id == "admin" && $_POST["password"] == "admin") {
    $query = "SELECT * FROM semester ORDER BY semester_id DESC LIMIT 1";
    $semester_result = mysqli_query($link, $query);
    $row = $semester_result->fetch_assoc();
    $_SESSION["semester_id"] = $row["semester_id"];
    mysqli_close($link);
    $_SESSION["is_admin"] = true;
    echo "2";
    exit();
  }
  $stmt = $link->prepare("SELECT *
                          FROM student, faculty
                          WHERE std_id = ?
                          AND SUBSTRING(std_id, -2, 2) = faculty_id
                          LIMIT 1");
  $stmt->bind_param("s", $std_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if (mysqli_num_rows($result) == 0) {
    mysqli_close($link);
    echo "0";
    exit();
  }
  $row = $result->fetch_assoc();
  if ($_POST["password"] == $row["std_pass"]) {
    $query = "SELECT * FROM semester ORDER BY semester_id DESC LIMIT 1";
    $semester_result = mysqli_query($link, $query);
    $row2 = $semester_result->fetch_assoc();
    $_SESSION["academic_year"] = $row2["year"];
    $_SESSION["semester_id"] = $row2["semester_id"];
    switch ($row2["semester"]) {
      case "1":
        $_SESSION["semester"] = "ต้น";
        break;
      case "2":
        $_SESSION["semester"] = "ปลาย";
        break;
      case "3":
        $_SESSION["semester"] = "ฤดูร้อน";
        break;
    }
    $_SESSION["login"] = true;
    $_SESSION["student_name"] = $row["fname_th"]." ".$row["lname_th"];
    $_SESSION["student_id"] = $row["std_id"];
    $_SESSION["student_faculty"] = $row["faculty_th_name"];
    mysqli_close($link);
    echo "1";
  } else {
    mysqli_close($link);
    echo "0";
  }
?>