<?php
  $link = mysqli_connect("localhost", "root", "", "regchula_initial");
  $mode = $_GET["mode"];
  if ($mode == "num") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name FROM course WHERE course_id LIKE concat(?, '%') LIMIT 10");
    $stmt->bind_param("s", $term);
  } elseif ($mode == "en") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name FROM course WHERE course_en_name LIKE concat('%', ?, '%') OR course_short_name LIKE concat('%', ?, '%') LIMIT 10");
    $stmt->bind_param("ss", $term, $term);
  } elseif ($mode == "th") {
    $stmt = $link->prepare("SELECT course_id, course_th_name AS course_name FROM course WHERE course_th_name LIKE concat('%', ?, '%') LIMIT 10");
    $stmt->bind_param("s", $term);
  }
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  $stmt->execute();
  $result = $stmt->get_result();
  $return = array();
  while ($row = mysqli_fetch_array($result)) {
    array_push($return, array("value"=>$row["course_id"], "label"=>$row["course_id"]." ".$row["course_name"]));
  }
  echo json_encode($return);
  mysqli_close($link);
?>