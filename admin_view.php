<?php
    session_start();
    if (!isset($_SESSION["is_admin"])) {
        header($_SERVER['SERVER_PROTOCOL']." 403 Forbidden", true, 403);
        exit();
    }
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>สรุปข้อมูลการลงทะเบียน</title>
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <link rel="stylesheet" href="css/admin_view.css">
        <script src="js/jquery-3.6.0.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/admin_view.js"></script>
    </head>
    <body>
        <form id="logout" action="db_action/logout.php">
            <input type="submit" value="ออกจากระบบ">
        </form>
        <h1>สรุปข้อมูลการลงทะเบียนเรียน</h1>
        <form id="search_form">
            <label for="mode">สรุปตาม:</label>
            <select id="mode">
                <option value="course">รายวิชา</option>
                <option value="std">นิสิต</option>
            </select>
            <br>
            <br>
            <input type="text" id="search_term" placeholder="รหัสหรือชื่อรายวิชา">
            <input type="submit" value="สรุป">
        </form>
        <br>
        <div id="result"></div>
    </body>
</html>