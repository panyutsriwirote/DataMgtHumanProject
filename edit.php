<?php
	session_start();
	if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
?>