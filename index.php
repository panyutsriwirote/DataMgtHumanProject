<?php
    session_start();
    if (isset($_SESSION["login"])) {
        header("Location: registration_code.php");
        exit();
    } elseif (isset($_SESSION["is_admin"])) {
        header("Location: admin_view.php");
        exit();
    }
?>
<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>เข้าสู่ระบบลงทะเบียนเรียน</title>
        <style>
            body {background-color: #ffcccc;}
            body, input {text-align: center;}
            table {
                margin-left: auto;
                margin-right: auto;
                margin-bottom: 10px;
                border-spacing: 20px;
            }
            td {font-weight: bold;}
            #result {color: red;}
            @media only screen and (max-width: 600px) {
                h1 {font-size: 25px;}
                h2 {font-size: 20px;}
                h3 {font-size: 15px;}
                table {border-spacing: 0;}
            }
        </style>
        <script src="js/jquery-3.6.0.min.js"></script>
        <script>
            $(function() {
                $("#login").submit(function(e) {
                    e.preventDefault();
                    const id = $("#id").val().trim();
                    const password = $("#password").val();
                    $.post("db_action/login.php", {id: id, password: password}, function(data) {
                        if (data == "1") {
                            window.location.replace("registration_code.php");
                        } else if (data == "2") {
                            window.location.replace("admin_view.php");
                        } else {
                            $("#result").html("เลขประจำตัวหรือรหัสผ่านไม่ถูกต้อง");
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
        <h1>เข้าสู่ระบบลงทะเบียนเรียน</h1>
        <h2>กรุณาป้อนเลขประจำตัวนิสิตและรหัสผ่าน</h2>
        <h3>รหัสผ่าน คือรหัสผ่านที่ใช้กับระบบอินเตอร์เน็ตของสำนักบริหารเทคโนโลยีสารสนเทศ จุฬาลงกรณ์มหาวิทยาลัย</h3>
        <div id="result">&nbsp</div>
        <form id="login">
            <table>
                <tr>
                    <td>เลขประจำตัว</td>
                    <td><input type="text" id="id" placeholder="เลขประจำตัวนิสิต" required></td>
                </tr>
                <tr>
                    <td>รหัสผ่าน</td>
                    <td><input type="password" id="password" placeholder="รหัสผ่าน" required></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="เข้าสู่ระบบ"></td>
                </tr>
            </table>
        </form>
    </body>
</html>