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
    const cache = {};
    $("#search_term").autocomplete({
        select : function(e, ui) {
            $(this).val(ui.item.value);
            $("#search_form").submit();
            submitted = true;
            prev_term = $(this).val();
        },
        source: function(request, response) {
            const raw_term = request.term;
            let term;
            if (/^\d{7} .+$/.test(raw_term)) {
                term = raw_term.substr(0, 7);
            } else if (/^\d{10} .+$/.test(raw_term)) {
                term = raw_term.substr(0, 10);
            } else {
                term = raw_term.toLowerCase().trim();
            }
            if (term in cache) {
                response(cache[term]);
                return;
            }
            request.mode = $("#mode").val();
            $.getJSON("db_action/admin_query.php", request, function(data) {
                cache[term] = data;
                response(data);
            });
        }
    });
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
