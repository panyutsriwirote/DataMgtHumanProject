<?php
    session_start();
    if (!isset($_SESSION["login"]) || !isset($_SESSION["entered_registration_code"])) {
      header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
      exit();
    }
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ลงทะเบียนเรียน</title>
        <link rel="stylesheet" href="css/registration.css">
        <link rel="stylesheet" href="css/jquery-ui.min.css">
        <script src="js/jquery-3.6.0.min.js"></script>
        <script src="js/jquery-ui.min.js"></script>
        <script src="js/registration.js"></script>
        <style>
            body {background-color: #ffcccc;}
        </style>
    </head>
    <body onload="gettime()">
        <?php
         echo "<div id=middle>แสดงความจำนงขอลงทะเบียนเรียน<br>ภาค$_SESSION[semester] ปีการศึกษา $_SESSION[academic_year]</div>";
        ?>
        <div class="header">
            <div id="time"></div>
            <?php
                echo "<div id=student_info>
                        $_SESSION[student_name]<br>
                        $_SESSION[student_id]<br>
                        <form action=db_action/logout.php><input type=submit value=ออกจากระบบ></form>
                    </div>";
            ?>
        </div>
        <p><b>รายวิชาที่ลงทะเบียนแล้ว</b><input type="button" id="refresh" value="รีเฟรช"></p>
        <div id="enrolled_course_view"></div>
        <p><b>ลงทะเบียนรายวิชาเพิ่มเติม</b></p>
        <form id="course_search">
            <input type="text" id="search" placeholder="ค้นหาด้วยชื่อวิชาหรือรหัสวิชา">
            <input type="submit" value="ค้นหา">
        </form>
        <div id="course_result"></div>
    </body>
</html>