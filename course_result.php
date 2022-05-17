<script>
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
    for (let group of grouped_num) {
      if (group.length == 1) {
        num_range.push(String(group[0]));
      } else {
        num_range.push(group[0] + "-" + group[group.length-1]);
      }
    }
    return num_range;
  }
  $(function() {
    $("#select_all").click(function() {
      if (this.checked) {
        $("input[type=checkbox]").each(function() {
          this.checked = true;
        });
      } else {
        $("input[type=checkbox]").each(function() {
          this.checked = false;
        });
      }
    });
    $("#enroll_form").submit(function(e) {
      e.preventDefault();
      const enrolled_sect = [];
      $("input.enroll").each(function() {
        if (this.checked == true) {
          enrolled_sect.push($(this).val());
        }
      });
      grouped_sect = group_num(enrolled_sect);
      if (enrolled_sect.length == 0) {
        alert("กรุณาเลือกตอนเรียนอย่างน้อย 1 ตอนเรียน");
      } else if (confirm("ยืนยันการลงทะเบียนรายวิชา\n" + $("#course_info").text() + "\nตอนเรียน\n" + grouped_sect)) {
        alert("SEND DATA TO THE SERVER");
        $("#course_result").empty();
        $("#search").val("");
      }
    });
  });
</script>
<?php
  $link = mysqli_connect("localhost", "root", "", "regchula_courses");
  $stmt = $link->prepare("SELECT * FROM course, section, slot WHERE course.course_id = ? AND course.course_id = section.course_id AND section.course_id = slot.course_id AND section.sect_num = slot.sect_num ORDER BY section.sect_num, slot_id");
  $stmt->bind_param("s", $id);
  $id = mysqli_real_escape_string($link, $_GET["course_id"]);
  $stmt->execute();
  $result = $stmt->get_result();
  if (mysqli_num_rows($result) == 0) {
    exit("<h1>ไม่พบรายวิชา</h1>");
  }
  echo "<form id=enroll_form>";
  echo "<table style=border-collapse:collapse class=center>";
  echo "<td><label for=select_all>เลือกทั้งหมด</label><br><input type=checkbox id=select_all></td>";
  echo "<tr>";
  echo "<th>ลงทะเบียน</th>";
  echo "<th>จำนวนนิสิต</th>";
  echo "<th>ตอนเรียน</th>";
  echo "<th>วิธีสอน</th>";
  echo "<th>วัน</th>";
  echo "<th>เวลาเรียน</th>";
  echo "<th>อาคาร</th>";
  echo "<th>ห้อง</th>";
  echo "<th>ผู้สอน</th>";
  echo "<th>หมายเหตุ</th>";
  echo "</tr>";
  function switch_class($class) {
    if ($class == "color1") {
      return "color2";
    } else {
      return "color1";
    }
  }
  $cur_sect = "";
  $class = "color1";
  while ($row = mysqli_fetch_array($result)) {
    if ($cur_sect == "") {
      echo "<p id=course_info style=text-align:center;>$row[course_id]&nbsp&nbsp$row[course_short_name]</p>";
      echo "<p style=text-align:center;>$row[course_th_name]</p>";
      echo "<p style=text-align:center;>$row[course_en_name]</p>";
      echo "<p style=text-align:center;>$row[credit]&nbspหน่วยกิต</p>";
      echo "<p style=text-align:center;><input type=submit value=ลงทะเบียนรายวิชา></p>";
      if (in_array($row["course_en_name"], ["THESIS", "DISSERTATION"])) {
        echo "<p style=text-align:center;><label for=credit>เลือกหน่วยกิต</label>&nbsp&nbsp<input type=text id=credit placeholder=หน่วยกิต size=10 style=text-align:center;></p>";
      }
    }
    $sect_num = $row["sect_num"];
    if ($cur_sect != $sect_num) {
      $class = switch_class($class);
      echo "<tr class=$class>";
      if ($row["sect_status"] == "close") {
        echo "<td valign=TOP>-</td>";
        echo "<td valign=TOP><span style=color:red>ปิด</span></td>";
      } elseif ($row["registered"] >= $row["maximum"]) {
        echo "<td valign=TOP>-</td>";
        echo "<td valign=TOP><span style=color:red>$row[registered]/$row[maximum]</span></td>";
      } else {
        echo "<td valign=TOP><input class=enroll type=checkbox value=$sect_num></td>";
        echo "<td valign=TOP>$row[registered]/$row[maximum]</td>";
      }
      echo "<td valign=TOP>$sect_num</td>";
      $cur_sect = $sect_num;
    } else {
      echo "<tr class=$class>";
      echo "<td></td>";
      echo "<td></td>";
      echo "<td></td>";
    }
    echo "<td valign=TOP>&nbsp$row[teach_method]&nbsp</td>";
    echo "<td valign=TOP>&nbsp$row[day]&nbsp</td>";
    echo "<td valign=TOP>&nbsp$row[time]&nbsp</td>";
    echo "<td valign=TOP>&nbsp$row[building]&nbsp</td>";
    echo "<td valign=TOP>&nbsp$row[room]&nbsp</td>";
    echo "<td valign=TOP>&nbsp$row[teacher]&nbsp</td>";
    $note = str_replace("\n", "<br>", $row["note"]);
    echo "<td valign=TOP>$note</td>";
    echo "</tr>";
  }
  echo "</table>";
  echo "</form>";
  mysqli_close($link);
?>