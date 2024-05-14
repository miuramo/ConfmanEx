function changed(formName, name) {
    var form = $("#" + formName);
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: form.serialize(),
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            console.log(result);
            var ary = JSON.parse(result);
            var elem = $("#" + name + "_answer");
            if (ary[name] == null){
                elem.html('<span class="text-red-600 font-extrabold">(未入力)</span>');
            } else if (typeof ary[name].replaceAll === 'function') {
                elem.html(ary[name].replaceAll("&", "&amp;").replaceAll("<", "&lt;")
                    .replaceAll(">", "&gt;").replaceAll("\r\n", "<br>"));
            }
            elem.addClass('flash');
            setTimeout(function () {
                elem.removeClass('flash');
            }, 1000); // フラッシュの時間
        },
        error: function (xhr, textStatus, error) {
            alert("error enq submit");
        }
    });
}
