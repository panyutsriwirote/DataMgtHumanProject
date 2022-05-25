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
    for (let i = 0; i < list.length; i++) {
        const num = parseInt(list[i]);
        if (i > 0 && list[i-1] == num-1) {
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
let submitted = false, prev_term = "";
$(function() {
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
    $("#refresh").click(function() {
        $.get("db_action/enrolled_course.php", function(data) {
            $("#enrolled_course_view").html(data);
            $(document).trigger("enrolled_course_loaded");
        });
    }).click();
    const search_cache = {};
    $("#search").autocomplete({
        select: function(e, ui) {
            $(this).val(ui.item.value);
            $("#course_search").submit();
            submitted = true;
            prev_term = $(this).val();
        },
        source: function(request, response) {
            const raw_term = request.term;
            const term = (/^\d{7} .+$/.test(raw_term)) ? raw_term.substr(0, 7) : raw_term.toLowerCase().trim();
            if (term in search_cache) {
                response(search_cache[term]);
                return;
            }
            if (/^\d{1,7}$/.test(term)) {
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
            $.getJSON("db_action/course_query.php", request, function(data) {
                search_cache[term] = data;
                response(data);
            });
        }
    });
    $("#course_search").submit(function(e) {
        e.preventDefault();
        $("#search").blur();
        $.get("db_action/course_result.php", {search_term: $("#search").val()}, function(data) {
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
        $("#select_all").prop("checked", all_selected);
    });
    const enrolled_sect_dom = $("#already_enrolled_sect");
    const already_enrolled_sect = (enrolled_sect_dom.length) ? enrolled_sect_dom.html().split(",") : [];
    $(".enroll").each(function() {
        if (already_enrolled_sect.includes($(this).val())) {
            $(this).click();
        }
    });
    const credit_dom = $("#credit");
    const prev_credit = (credit_dom.length) ? credit_dom.val() : null;
    $("#enroll_form").submit(function(e) {
        e.preventDefault();
        const enrolled_sect = [];
        $(".enroll").each(function() {
            if (this.checked) {
                enrolled_sect.push($(this).val());
            }
        });
        const num_enrolled_sect = enrolled_sect.length;
        if (num_enrolled_sect == 0 && $("#submit_form").val() != "ยืนยันการแก้ไข") {
            alert("กรุณาเลือกตอนเรียนอย่างน้อย 1 ตอนเรียน");
            return;
        }
        const credit = (credit_dom.length) ? credit_dom.val() : null;
        if (credit == "") {
            alert("กรุณาระบุจำนวนหน่วยกิต");
            return;
        }
        const section_the_same = (JSON.stringify(already_enrolled_sect) == JSON.stringify(enrolled_sect));
        if (credit_dom.length) {
            const credit_the_same = (credit == prev_credit);
            if (section_the_same && credit_the_same) {
                alert("กรุณาแก้ไขข้อมูลให้แตกต่างจากเดิม");
                return;
            }
        } else {
            if (section_the_same) {
                alert("กรุณาแก้ไขตอนเรียนให้แตกต่างจากเดิม");
                return;
            }
        }
        const to_delete = [];
        for (const section of already_enrolled_sect) {
            if (!enrolled_sect.includes(section)) {
                to_delete.push(section);
            }
        }
        const course_info = $("#course_info").text();
        const grouped_sect = group_num(enrolled_sect);
        let msg;
        if (num_enrolled_sect == 0) {
            msg = "ยืนยันการลบรายวิชา\n" + course_info;
        } else {
            msg = "ยืนยันการลงทะเบียนรายวิชา\n" + course_info + "\nตอนเรียน\n" + grouped_sect;
            if (credit_dom.length) {
                msg += ("\nหน่วยกิต: " + credit);
            }
        }
        if (confirm(msg)) {
            const course_id = course_info.substr(0, 7);
            $.post("db_action/enroll.php",
            {course_id: course_id,
            enrolled_sect: enrolled_sect,
            credit: credit,
            to_delete: to_delete}, function() {
                $("#course_result").empty();
                $("#search").val("");
                submitted = false;
                $("#refresh").click();
            });
        }
    });
    $("#gr_enroll_form").submit(function(e) {
        e.preventDefault();
        const course_info = $("#course_info").text();
        if (confirm("ยืนยันการลงทะเบียนรายวิชา\n" + course_info)) {
            const course_id = course_info.substr(0, 7);
            $.post("db_action/enroll.php", {course_id: course_id}, function() {
                $("#course_result").empty();
                $("#search").val("");
                submitted = false;
                $("#refresh").click();
            });
        }
    });
});
$(document).on("enrolled_course_loaded", function() {
    $(".edit").click(function() {
        const course_id = $(this).parent().siblings(".course_id").html();
        const course_name = $(this).parent().siblings(".course_name").html();
        const val = course_id + " " + course_name;
        submitted = true;
        prev_term = val;
        $("#search").val(val);
        $("#course_search").submit();
    });
    $(".delete").click(function() {
        const course_id = $(this).parent().siblings(".course_id").html();
        const course_name = $(this).parent().siblings(".course_name").html();
        if (confirm("ยืนยันการลบรายวิชา\n" + course_id + "  " + course_name)) {
            $.post("db_action/delete.php", {course_id: course_id}, function() {
                $("#refresh").click();
                const course_info_dom = $("#course_info");
                if (course_info_dom.length && course_info_dom.html().substr(0, 7) == course_id) {
                    const val = course_id + " " + course_name;
                    submitted = true;
                    prev_term = val;
                    $("#search").val(val);
                    $("#course_search").submit();
                }
            });
        }
    });
});
