<?php
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $mode = mysqli_real_escape_string($link, $_GET["mode"]);
  $term = mysqli_real_escape_string($link, $_GET["term"]);
  if ($mode == "num") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name FROM course WHERE course_id LIKE concat(?, '%') LIMIT 10");
    $stmt->bind_param("s", $term);
    $gr_stmt = $link->prepare("SELECT DISTINCT group_course_id FROM group_course WHERE group_course_id LIKE concat(?, '%') LIMIT 10");
    $gr_stmt->bind_param("s", $term);
    $gr_stmt->execute();
    $gr_result = $gr_stmt->get_result();
  } elseif ($mode == "en") {
    $stmt = $link->prepare("SELECT course_id, course_en_name AS course_name FROM course WHERE course_en_name LIKE concat('%', ?, '%') OR course_short_name LIKE concat('%', ?, '%') LIMIT 10");
    $stmt->bind_param("ss", $term, $term);
  } elseif ($mode == "th") {
    $stmt = $link->prepare("SELECT course_id, course_th_name AS course_name FROM course WHERE course_th_name LIKE concat('%', ?, '%') LIMIT 10");
    $stmt->bind_param("s", $term);
  }
  $stmt->execute();
  $result = $stmt->get_result();
  $return = array();
  while ($row = mysqli_fetch_array($result)) {
    array_push($return, array("value"=>$row["course_id"], "label"=>$row["course_id"]." ".$row["course_name"]));
  }
  if ($mode == "num") {
    while ($row = mysqli_fetch_array($gr_result)) {
      if (count($return) == 10) {
        break;
      }
      array_push($return, array("value"=>$row["group_course_id"], "label"=>$row["group_course_id"]." รายวิชาแบบกลุ่ม"));
    }
  }
  echo json_encode($return);
  mysqli_close($link);
?>