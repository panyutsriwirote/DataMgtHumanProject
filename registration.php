<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ลงทะเบียนเรียน</title>
        <style>
            .color1 {
                background-color: #D6EEEE;
            }
            .color2 {
                background-color: #FFFFFF;
            }
            #search {width: 80%;}
            h1 {font-size: 20px;}
            h2 {font-size: 15px;}
            form {margin-bottom: 10px;}
            .header {display: flex;}
            #student_info {
                margin-left: auto;
                text-align: right;
            }
            table {
                margin-left: auto;
                margin-right: auto;
            }
            #course_result {
                border: 1px solid black;
                height: 50vh;
                overflow-y: scroll;
                overflow-x: scroll;
            }
            #enrolled_course {
                border: 1px solid black;
                height: 20vh;
                display: grid;
                justify-content: center;
                align-content: center;
                overflow-y: scroll;
            }
            th {
                padding-left: 5px;
                padding-right: 5px;
                position: -webkit-sticky;
                position: sticky;
                top: 0;
                background-color: #FFCCFF;}
            th:not(:first-child) {border-left: 5px solid white;}
            h1, h2, td {text-align: center;}
            .ui-autocomplete {
                max-width: 80%;
                overflow-x: break-word;
            }
            @media only screen and (max-width: 600px) {
                .ui-menu-item-wrapper {
                    font-size: 12px;
                }
            }
            ::-webkit-scrollbar {-webkit-appearance: none;}
            ::-webkit-scrollbar:vertical {width: 10px;}
            ::-webkit-scrollbar:horizontal {height: 10px;}
            ::-webkit-scrollbar-thumb {
                background-color: #ccc;
                border-radius: 10px;
                border: 2px solid #eee;
            }
            ::-webkit-scrollbar-track {background-color: #eee;}
        </style>
        <link rel="stylesheet" href="jquery-ui.min.css">
        <script src="jquery-3.6.0.min.js"></script>
        <script src="jquery-ui.min.js"></script>
        <script>
            setInterval(gettime, 1000);
            function gettime() {
                const d = new Date();
                let year = String(d.getFullYear()),
                    month = String(d.getMonth() + 1),
                    date = String(d.getDate()),
                    hour = String(d.getHours()),
                    minute = String(d.getMinutes()),
                    second = String(d.getSeconds());
                if (minute.length == 1) {
                    minute = "0" + minute;
                }
                if (hour.length == 1) {
                    hour = "0" + hour;
                }
                if (second.length == 1) {
                    second = "0" + second;
                }
                $("#time").html(`${date}/${month}/${year}<br>เวลา ${hour}:${minute}:${second}`);
            }
            $(function() {
                const search_cache = {};
                $("#search").autocomplete({select: function(e, ui) {$("#search").val(ui.item.value);$("#course_search").submit();},source: function(request, response) {
                    const term = request.term.toLowerCase().trim();
                    if (term in search_cache) {
                        response(search_cache[term]);
                        return;
                    }
                    if (/^[0-9]{1,7}$/.test(term)) {
                        request.mode = "num";
                    } else if (/[a-z]/.test(term)) {
                        request.mode = "en";
                    } else if (/[ก-์]/.test(term)) {
                        request.mode = "th"
                    } else {
                        response();
                        return;
                    }
                    request.term = term;
                    $.getJSON("course_query.php", request, function(data) {
                        search_cache[term] = data;
                        response(data);
                    });
                }});
                $("#course_search").submit(function(e) {
                    e.preventDefault();
                    $.get("course_result.php", {course_id: $("#search").val()}, function(data) {
                        $("#course_result").html(data);
                    });
                });
            });
        </script>
    </head>
    <body onload="gettime()">
        <div class="header">
            <div id="time"></div>
            <div id="student_info">ปานญุตม์ ศรีวิโรจน์<br>6340138322</div>
        </div>
        <h1>แสดงความจำนงขอลงทะเบียนเรียน (จท 11)</h1>
        <h2>ภาคการศึกษา... ปีการศึกษา...</h2>
        <p><b>รายวิชาที่ลงทะเบียนแล้ว</b></p>
        <div id="enrolled_course">ยังไม่มีรายวิชาที่ลงทะเบียนเรียน</div>
        <p><b>ลงทะเบียนรายวิชาเพิ่มเติม</b></p>
        <form id="course_search">
            <input type="text" id="search" placeholder="ค้นหาด้วยชื่อวิชาหรือรหัสวิชา">
            <input type="submit" value="ค้นหา">
        </form>
        <div id="course_result"></div>
    </body>
</html>