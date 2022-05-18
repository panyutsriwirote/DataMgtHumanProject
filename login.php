<?php
  session_start();
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $std_id = mysqli_real_escape_string($link, $_POST["id"]);
  $stmt = $link->prepare("SELECT *
                          FROM student, faculty
                          WHERE std_id = ?
                          AND SUBSTRING(std_id, -2, 2) = faculty_id
                          LIMIT 1");
  $stmt->bind_param("s", $std_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if (mysqli_num_rows($result) == 0) {
    echo "0";
    session_unset();
    session_destroy();
    exit();
  }
  $password = mysqli_real_escape_string($link, $_POST["password"]);
  while ($row = mysqli_fetch_array($result)) {
    if ($password == $row["std_pass"]) {
      $_SESSION["login"] = true;
      $_SESSION["academic_year"] = "2565";
      $_SESSION["semester"] = "ต้น";
      $_SESSION["int_semester"] = 1;
      $_SESSION["student_name"] = $row["fname_th"]." ".$row["lname_th"];
      $_SESSION["student_id"] = $row["std_id"];
      $_SESSION["student_faculty"] = $row["faculty_th_name"];
      echo "1";
    } else {
      session_unset();
      session_destroy();
      echo "0";
    }
  }
?>