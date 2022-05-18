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
        <title>ลงทะเบียนเรียน</title>
        <style>
            p {margin: 5px;}
            .color1 {background-color: #D6EEEE;}
            .color2 {background-color: #FFFFFF;}
            #search {width: 80%;}
            form {
                margin-bottom: 10px;
                margin-top: 5px;
            }
            .header {
                display: flex;
                justify-content: space-between;
            }
            #student_info {text-align: right;}
            #middle {
                position: fixed;
                width: 100vw;
                top: 0;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
            }
            table {
                margin-left: auto;
                margin-right: auto;
            }
            #course_result {
                border: 1px solid black;
                height: 49vh;
                overflow-y: scroll;
                overflow-x: scroll;
            }
            #enrolled_course {
                margin-top: 5px;
                margin-bottom: 5px;
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
            #credit {width: 10ch;}
            @media only screen and (max-width: 600px) {
                table, p, .header, .ui-menu-item-wrapper {font-size: 13px;}
                #middle {display: none;}
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
                    minute = String(d.getMinutes())
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
            function group_num(list) {
                const grouped_num = [];
                for (let i=0;i<list.length;i++) {
                    const num = parseInt(list[i]);
                    if (i>0 && list[i-1] == num-1) {
                        grouped_num[grouped_num.length-1].push(num);
                        continue;
                    }
                    grouped_num.push([num])
                }
                const num_range = [];
                for (const group of grouped_num) {
                    if (group.length == 1) {
                        num_range.push(String(group[0]));
                    } else {
                        num_range.push(group[0] + "-" + group[group.length-1]);
                    }
                }
                return num_range;
            }
            function degroup_num(string) {
                if (/^\d+$/.test(string)) {
                    return string;
                } else {
                    const range = string.split("-");
                    const degrouped_num = [];
                    for (let i = range[0]; i <= range[1]; i++) {
                        degrouped_num.push(i);
                    }
                    return degrouped_num;
                }
            }
            $(function() {
                $("#logout").click(function() {
                    window.location.replace("logout.php");
                });
                let submitted = false, prev_term = "";
                $("#search").on("input", function() {
                    submitted = false;
                }).focusin(function() {
                    if (submitted) {
                        $(this).val("");
                    }
                }).focusout(function() {
                    if (submitted) {
                        $(this).val(prev_term);
                    }
                });
                const search_cache = {};
                $("#search").autocomplete({
                    select: function(e, ui) {
                        $(this).val(ui.item.value);
                        $("#course_search").submit();
                        submitted = true;
                        prev_term = $(this).val();
                        $(this).blur();
                    },
                    source: function(request, response) {
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
                    $.get("course_result.php", {course_id: $("#search").val().substr(0, 7)}, function(data) {
                        $("#course_result").html(data);
                        $(document).trigger("form_loaded");
                    });
                });
            });
            $(document).on("form_loaded", function() {
                $("#select_all").click(function() {
                    if (this.checked) {
                        $(".enroll").each(function() {
                            this.checked = true;
                        });
                    } else {
                        $(".enroll").each(function() {
                            this.checked = false;
                        });
                    }
                });
                $(".enroll").click(function() {
                    let all_selected = true;
                    $(".enroll").each(function() {
                        if (!this.checked) {
                            all_selected = false;
                            return false;
                        }
                    });
                    if (all_selected) {
                        $("#select_all").prop("checked", true);
                    } else {
                        $("#select_all").prop("checked", false);
                    }
                });
                $("#enroll_form").submit(function(e) {
                    e.preventDefault();
                    const enrolled_sect = [];
                    $(".enroll").each(function() {
                        if (this.checked == true) {
                            enrolled_sect.push($(this).val());
                        }
                    });
                    grouped_sect = group_num(enrolled_sect);
                    credit = ($("#credit").length) ? $("#credit").val() : null;
                    if (enrolled_sect.length == 0) {
                        alert("กรุณาเลือกตอนเรียนอย่างน้อย 1 ตอนเรียน");
                    } else if (credit == "") {
                        alert("กรุณาระบุจำนวนหน่วยกิต");
                    } else {
                        let msg = "ยืนยันการลงทะเบียนรายวิชา\n" + $("#course_info").text() + "\nตอนเรียน\n" + grouped_sect;
                        if ($("#credit").length) {
                            msg += ("\nหน่วยกิต: " + credit);
                        }
                        if (confirm(msg)) {
                            alert("SEND DATA TO THE SERVER");
                            $("#course_result").empty();
                            $("#search").val("");
                        }
                    }
                });
                $("#gr_enroll_form").submit(function(e) {
                    e.preventDefault();
                    if (confirm("ยืนยันการลงทะเบียนรายวิชา\n" + $("#course_info").text())) {
                        $(".gr_sect").each(function() {
                            alert(degroup_num($(this).text()));
                        });
                        alert("SEND DATA TO THE SERVER");
                        $("#course_result").empty();
                        $("#search").val("");
                    }
                });
            });
        </script>
    </head>
    <body onload="gettime()">
        <?php
         echo "<div id=middle>แสดงความจำนงขอลงทะเบียนเรียน<br>ภาค$_SESSION[semester] ปีการศึกษา $_SESSION[academic_year]</div>";
        ?>
        <div class="header">
            <div id="time"></div>
            <?php
                echo "<div id=student_info>$_SESSION[student_name]<br>
                    $_SESSION[student_id]<br>
                    <input type=button id=logout value=ออกจากระบบ>
                </div>";
            ?>
        </div>
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