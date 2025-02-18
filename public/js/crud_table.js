var toggleNoticeShown = false;

// crud でのテキスト編集
function other_textchange(event) {
    // console.log(event.target.value); // selected option value
    // console.log(event.target.id); // select
    var tdid = event.target.id.replace("select__", "edit__");
    var text = $("#" + tdid).val();
    if (event.target.value == "remove_spaces_between_japanese") {
        var newtext = text.replace(/([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])\s+([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])/g, '$1$2');
        $("#" + tdid).val(newtext);
    } else if (event.target.value == "replace_kuten") {
        var newtext = text.replace(/、/g, "，").replace(/。/g, "．");
        $("#" + tdid).val(newtext);
    }
}

$(".clicktoedit").click(function (e) {
    var tdid = e.currentTarget.id;
    if (origData[tdid] != null) return;
    // tdidを分解する field__id__TYPE
    var m = tdid.match(/^(\w+)__(\d+)__([\w()]+)/);
    // グローバルにsizecols が定義されていれば、それを用いる。
    var localsizecols = (typeof sizecols !=='undefined') ? sizecols : 50;
    var txt = $('#' + tdid).text().trim();
    txt = txt.replaceAll('"', '&quot;');
    console.log(txt);
    var inputType = "";
    if (/text$/.test(m[3].toLowerCase())) { // text, longtext, mediumtext...
        var attr = $('#' + tdid).data('orig');
        if (typeof attr !== 'undefined') txt = attr;
        $('#' + tdid).html("<textarea style=\"resize:both;\" cols=\""+localsizecols+"\" rows=\"6\" id=\"edit__" + tdid + "\">" + txt + "</textarea><div><span class=\"text-sm text-green-500\">hint: cmd(ctrl)+enter to save, esc to cancel. </span>"+
            "<select class=\"font-sans text-xs\" id='select__" + tdid + "' onchange=\"other_textchange(event);\"><option>【テキスト一括処理】</option>"+
            "<option value=\"remove_spaces_between_japanese\">日本語文字間の半角スペース・改行を削除</option>"+
            "<option value=\"replace_kuten\">、。を，．に変換</option>"+
            "</select>"+
            // "<button class=\"text-xs bg-gray-200 p-1\" onclick=\"saveText('edit__"+tdid + "');\">保存</input>"+
            "</div>");
        inputType = "textarea";
    } else if (m[3].toLowerCase() == "varchar" || m[3].toLowerCase() == "datetime" || m[3].toLowerCase() == "timestamp") {
        $('#' + tdid).html("<input type=\"text\" size=\""+localsizecols+"\" id=\"edit__" + tdid + "\"  value=\"" + txt + "\">");
        inputType = "text";
    } else if (m[3].toLowerCase() == "tinyint") {
        if (!toggleNoticeShown) {
            var ok = confirm("Press OK to change the value. \n値を変更する場合はOKを押してください。（一度OKを押すと、次回以降は確認しません。）");
            if (!ok) return;
            toggleNoticeShown = true;
        }
        crudpost(m[1], m[2], "x", m[3].toLowerCase());
        return;
    } else {
        $('#' + tdid).html("<input type=\"text\" size=\"5\" id=\"edit__" + tdid + "\"  value=\"" + txt + "\">");
        inputType = "text";
    }
    origData[tdid] = txt;
    // $('#'+tdid).removeClass('clicktoedit');
    $('#' + tdid).removeClass('hover:text-blue-600');
    var editelem = $('#edit__' + tdid);
    editelem.focus();
    // テキスト入力フィールドの最後の文字の位置を取得します
    var endPosition = editelem.val().length;
    // カーソルの位置を設定します
    editelem[0].selectionStart = endPosition;
    editelem[0].selectionEnd = endPosition;
});

// IME変換中はEnterは確定なので、送信しない。
var isIMEActive = false;
$(".clicktoedit").on('compositionstart', function (e) {
    isIMEActive = true;
});
$(".clicktoedit").on('compositionend', function (e) {
    isIMEActive = false;
});

function saveText(tdid) {
    var m = tdid.match(/^(\w+)__(\d+)__([\w()]+)/); // tdidを分解する field__id__TYPE
    if ($('#' + tdid).find("textarea").length > 0) {
        var text = $('#' + tdid).find("textarea").val();
        $('#' + tdid).data('orig', text);
        if (typeof mode_br !== 'undefined' && mode_br === true){
            var showtext = text.replaceAll("\n", "<br>");
            $('#' + tdid).html(showtext);
        } else {
            $('#' + tdid).text(text.replaceAll("\n", "\r\n"));
        }
    } else if ($('#' + tdid).find("input").length > 0) {
        var text = $('#' + tdid).find("input").val();
        $('#' + tdid).text(text);
    } else {
        alert("error saveText");
    }
    crudpost(m[1], m[2], text, m[3]);

    $('#' + tdid).addClass('hover:text-blue-600');
    origData[tdid] = null;
    delete origData[tdid];
}

$(".clicktoedit").keydown(function (e) {
    var tdid = e.currentTarget.id;
    if (e.key === "Enter") {
        if (isIMEActive) return; // IME変換中はEnterは確定なので、送信しない。
        if ($('#' + tdid).find("textarea").length > 0 && !e.ctrlKey && !e.metaKey) return; // textareaはCTRLorMetaが必要
        else {
            saveText(tdid);
        }
    } else if (e.key === "Escape" || e.key === "Esc") {
        if (typeof mode_br !== 'undefined' && mode_br === true){
            $('#' + tdid).html(origData[tdid].replaceAll("\n", "<br>"));
        } else {
            $('#' + tdid).text(origData[tdid].replaceAll("&quot;", '"'));
        }
        $('#' + tdid).addClass('hover:text-blue-600');
        origData[tdid] = null;
        delete origData[tdid];
    }
})

function crudpost(field, data_id, val, dtype) {
    var fd = new FormData();
    fd.append('table', table);
    fd.append('_token', $('meta[name="csrf-token"]').attr("content"));
    fd.append('field', field);
    fd.append('data_id', data_id);
    fd.append('dtype', dtype);
    fd.append('val', val);
    fd.append('tdid', field + "__" + data_id + "__" + dtype);
    var formDataObject = {};
    for (var pair of fd.entries()) {
        formDataObject[pair[0]] = pair[1];
    }
    var form = $("#admincrudpost");
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formDataObject,
        timeout: 10000,
        beforeSend: function (xhr, settings) { },
        complete: function (xhr, textStatus) { },
        success: function (result, textStatus, xhr) {
            if (/^TOGGLE/.test(result)) {
                var m = result.trim().match(/(TOGGLE) (\d+) (.+)$/); // resultを分解する TOGGLE val tdid
                var elem = $('#' + m[3]);
                elem.text(m[2]);
            } else {
                var m = result.trim().match(/(OK) (.+)$/);
                var elem = $('#' + m[2]);
            }
            elem.addClass('flash');
            setTimeout(function () {
                elem.removeClass('flash');
            }, 1000);
        },
        error: function (xhr, textStatus, error) {
            alert("error crudpost");
        }
    });
}
