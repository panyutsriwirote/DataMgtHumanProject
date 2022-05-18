<?php
  session_start();
  if (!isset($_SESSION["login"])) {
    header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
    exit();
  }
  echo "YOYO";
?>