<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = $_GET["mode"];
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  if ($mode == "std") {
    $regex = "/\d{10} .+/";
    if (preg_match($regex, $term)) {
      $term = substr($term, 0, 10);
    }
    $stmt = $link->prepare("");
  } elseif ($mode == "course") {
    $regex = "/\d{7} .+/";
    if (preg_match($regex, $term)) {
      $term = substr($term, 0, 7);
    }
  } else {
    mysqli_close($link);
    echo "<h1>ไม่พบข้อมูล</h1>";
    exit();
  }
  mysqli_close($link);
?>