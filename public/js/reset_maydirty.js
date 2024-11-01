// paper id と field を指定して、Dirty フラグをリセットする
function reset_maydirty(pid, field) {
    reset_maydirty_post(pid, field, "false");
    return false;
}

function reset_maydirty_post(pid, field, val) {
    var fd = new FormData();
    fd.append('_token', $('meta[name="csrf-token"]').attr("content"));
    fd.append('pid', pid);
    fd.append('field', field);
    fd.append('val', val);
    var formDataObject = {};
    for (var pair of fd.entries()) {
        formDataObject[pair[0]] = pair[1];
    }
    var form = $("#update_maydirty");
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formDataObject,
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            console.log("reset_maydirty_post result: " + result);
            var ans = confirm("確認済みに設定しました。\n\nページをリロードして、設定が反映されていることを確認しますか？");
            if (ans) {
                // 元のページを、再読み込みする
                location.reload();
            }
        },
        error: function (xhr, textStatus, error) {
            alert("error crudpost");
        }
    });
}
