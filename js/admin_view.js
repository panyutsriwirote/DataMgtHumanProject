$(function() {
    $("#mode").change(function() {
        const input_field = $("#search_term");
        if ($(this).val() == "std") {
            input_field.attr("placeholder", "รหัสหรือชื่อนิสิต");
        } else {
            input_field.attr("placeholder", "รหัสหรือชื่อรายวิชา");
        }
    });
    let submitted = false, prev_term = "";
    $("#search_term").autocomplete({
        select : function(e, ui) {
            $(this).val(ui.item.value);
            $("#search_form").submit();
            submitted = true;
            prev_term = $(this).val();
            $(this).blur();
        },
        source: function(request, response) {
        request.mode = $("#mode").val();
        $.getJSON("db_action/admin_query.php", request, function(data) {
            response(data);
        });
    }});
    $("#search_term").on("input", function() {
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
    $("#search_form").submit(function(e) {
        e.preventDefault();
        $("#search_term").blur();
        $.get("db_action/admin_result.php", {mode: $("#mode").val(), term: $("#search_term").val()}, function(data) {
            $("#result").html(data);
        });
    });
});