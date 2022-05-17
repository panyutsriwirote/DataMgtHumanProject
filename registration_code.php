<?php
	session_start();
	if (!isset($_SESSION["login"])) {
		header($_SERVER['SERVER_PROTOCOL']." 404 Not Found", true, 404);
		exit();
	}
?>
<!DOCTYPE html>
<html lang="th">
	<head>
		<meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>กรอกรหัสลงทะเบียนเรียน</title>
		<style>
			input, h1, form, #result, .student_info, p {text-align: center;}
			#result {color: red;}
			p {
				margin-bottom: 5px;
				margin-top: 5px;
			}
			@media only screen and (max-width: 600px) {
                h1 {font-size: 20px;}
				p, div {font-size: 13px;}
				p {text-align: left;}
				.logout-container {text-align: center;}
            }
		</style>
		<script src="jquery-3.6.0.min.js"></script>
		<script>
			$(function() {
				$("#logout").click(function() {
					window.location.replace("logout.php");
				});
				$("#registration_code_form").submit(function(e) {
					e.preventDefault();
					$.post("registration_code_check.php", {registration_code: $("#registration_code").val()}, function(data) {
						if (data == "1") {
							window.location.replace("registration.php");
						} else {
							$("#result").html(data);
						}
					});
				});
			});
		</script>
	</head>
	<body>
		<?php
			echo "<h1>สำนักงานลงทะเบียนจุฬาลงกรณ์มหาวิทยาลัย<br>ภาคการศึกษา$_SESSION[semester] ปีการศึกษา $_SESSION[academic_year]</h1>";
			echo "<p class=student_info>$_SESSION[student_faculty]</p>";
			echo "<p class=student_info>$_SESSION[student_id]</p>";
			echo "<p class=student_info>$_SESSION[student_name]</p>";
		?>
		<p>
			การบันทึกรหัสการลงทะเบียนเรียนของนิสิตระดับปริญญาบัณฑิต<br>
			1. นิสิตสามารถติดต่อรับรหัสการลงทะเบียนเรียนได้ที่อาจารย์ที่ปรึกษา<br>
			2. รหัสการลงทะเบียนเรียนจะใช้สำหรับการลงทะเบียนเรียนปกติ หรือลงทะเบียนเรียนสาย<br>
			3. รหัสการลงทะเบียนเรียนภาคฤดูร้อนและภาคการศึกษาต้นของปีการศึกษาถัดไปใช้รหัสเดียวกัน<br>
			4. หากพบปัญหาเกี่ยวกับรหัสการลงทะเบียนเรียน โปรดติดต่อคณะที่นิสิตสังกัด<br>
			หรือติดต่อเจ้าหน้าที่ผ่านช่องแชท หรือ Email webreg@chula.ac.th
		</p>
		<div id="result">&nbsp</div>
		<br>
		<form id="registration_code_form">
				<label for="registration_code">รหัสการลงทะเบียนเรียน</label>
				<input type="text" id="registration_code" placeholder="รหัสลงทะเบียนเรียน">
				<input type="submit" value="ยืนยัน">
		</form>
		<br>
		<p class="logout-container"><input type="button" id="logout" value="ออกจากระบบ"></p>
	</body>
</html>