var isDragging = false;
var startOffset = 0;
var endOffset = 0;

var selectedText = "";
var selectedText_Orig = "";

var maydirty = false; // 直接入力モードをつかったら true になる。

function maydirty_mode(mode) {
    maydirty = mode;
    if (mode) $("#seltext").css('background-color', '#e9d5ff'); // purple
    else $("#seltext").css('background-color', '#fef9c3'); // yellow
    $("#seltext").focus();
}

document.getElementById("seltext").addEventListener("keydown", function(event) {
    if (!maydirty && event.key.length === 1 && event.keyCode != 32) {
        alert("半角スペース以外の修正はできません。元の選択テキストに戻します。");
        $("#seltext").val(selectedText_Orig);
        event.preventDefault();
        return;
    }
    selectedText_Orig = $("#seltext").val();
});
document.getElementById("seltext").addEventListener("change", function(event) {
    var stxt = document.getElementById("seltext").value;
    stxt = stxt.trim().replace(/。/g, "．").replace(/、/g, "，").replace(/ /g, "");
    var otxt = selectedText_Orig.trim().replace(/。/g, "．").replace(/、/g, "，").replace(/ /g, "");
    if (!maydirty && stxt != otxt) {
        alert("半角スペース以外の修正はできません。元の選択テキストに戻します。");
        $("#seltext").val(selectedText_Orig);
        event.preventDefault();
        return;
    }
    selectedText_Orig = $("#seltext").val();
});

document.getElementById("pdftext").addEventListener("mousedown", function(event) {
    isDragging = true;
    startOffset = endOffset = getCaretOffset(event);
});

document.getElementById("pdftext").addEventListener("mousemove", function(event) {
    if (isDragging) {
        endOffset = getCaretOffset(event);
        if (startOffset < endOffset) {
            if (typeof document.caretRangeFromPoint !== 'undefined') {
                highlightText(startOffset, endOffset); //(Firefoxではドラッグ中に更新しない。markでハイライトすると、うまくいかない)
            } else {
                // 仕方ないので、Firefoxでは直接テキストエリアに選択中のテキストをフィードバックする
                var textElement = document.getElementById("pdftext");
                var highlightedText = textElement.innerText.substring(startOffset, endOffset).replaceAll(
                    "\n", "");
                $("#seltext").val(highlightedText); // ここで選択文字列を貼り付ける
            }
        }
    }
});

document.getElementById("pdftext").addEventListener("mouseup", function() {
    isDragging = false;
    if (startOffset < endOffset) {
        if (typeof document.caretRangeFromPoint == 'undefined') highlightText(startOffset, endOffset);
        selectedText_Orig = selectedText.replaceAll("\n", "");
        $("#seltext").val(selectedText_Orig); // ここで選択文字列を貼り付ける
        maydirty_mode(false);
    } else {
        // reset
        $("#seltext").val("");
        startOffset = endOffset = 0;
        highlightText(startOffset, startOffset);
    }
});

function getCaretOffset(event) {
    if (typeof document.caretRangeFromPoint !== 'undefined') {
        var range = document.caretRangeFromPoint(event.clientX, event.clientY);
        var preCaretRange = range.cloneRange();
        preCaretRange.selectNodeContents(document.getElementById("pdftext"));
        preCaretRange.setEnd(range.endContainer, range.endOffset);
        // console.log(preCaretRange.toString().length);
        return preCaretRange.toString().length;
    } else {
        // for Firefox browser (markでハイライトすると、うまくいかない)
        if (event.type == "mousedown") {
            $("#seltext").val("");
            startOffset = endOffset = 0;
            highlightText(startOffset, startOffset);
        }
        var pos = document.caretPositionFromPoint(event.clientX, event.clientY);
        return pos.offset;
    }
}

function highlightText(start, end) {
    var textElement = document.getElementById("pdftext");
    var text = textElement.innerText;
    var highlightedText = text.substring(start, end);
    selectedText = highlightedText;
    var newText = text.slice(0, start) + "<mark>" + highlightedText + "</mark>" + text.slice(end);
    textElement.innerHTML = newText;
}

function replacekutouten() {
    var val = $("#seltext").val();
    val = val.trim();
    val = val.replace(/。/g, "．");
    val = val.replace(/、/g, "，");
    $("#seltext").val(val);
    return false;
}

function removespaces() {
    if (maydirty) {
        alert("直接入力モードでは使用できません");
        return;
    }
    var val = $("#seltext").val();
    val = val.trim();
    val = val.replace(/([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])\s+([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])/g, '$1$2');
    var conf = confirm("全角文字に挟まれた《半角スペース》に加え、全角と半角（英文字・数字）に挟まれた《半角スペース》も一括削除しますか？\n\n（例）「怪盗 Kids」→「怪盗Kids」");  
    if (conf){
        val = val.replace(/([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])\s+([a-zA-Z0-9])/g, '$1$2');
        val = val.replace(/([a-zA-Z0-9])\s+([一-龥ぁ-ゔァ-ヴー々〆〤．，。、])/g, '$1$2');
    }
    // val = val.replace(/ /g, "");
    $("#seltext").val(val);
    return false;
}

// post する (paper.dragontextpost)
function valset(field) {
    var stext = document.getElementById("seltext").value;

    // 著者名・所属バリデーション
    if (field == 'authorlist' || field == 'eauthorlist') {
        // stext を行ごとに分割して、各行をバリデーションする
        var lines = stext.split("\n");
        for (var i = 0; i < lines.length; i++) {
            if (!author_affil_validation(lines[i])) {
                return;
            }
        }
    }
    if (field == 'eauthorlist') {
        // 英文著者リストの場合、全角文字を含んでいないかチェックする
        var ascii_only_pattern = /^[\x20-\x7E\r\n\t]*$/;
        if (!ascii_only_pattern.test(stext)) {
            alert("英文著者リストに全角文字が含まれています。全角文字を半角に修正してください。");
            return;
        }
    }

    var fd = new FormData();
    fd.append('_token', $('meta[name="csrf-token"]').attr("content"));
    fd.append('target_field', field);
    fd.append('target_value', stext);
    fd.append('maydirty', maydirty);
    var formDataObject = {};
    for (var pair of fd.entries()) {
        formDataObject[pair[0]] = pair[1];
    }
    var form = $("#dragontextpost");
    $.ajax({
        url: form.attr("action"),
        type: form.attr("method"),
        data: formDataObject,
        timeout: 10000,
        // processData: false,  // データを処理しないように設定
        // contentType: false,  // コンテンツの種類を指定しないように設定
        beforeSend: function(xhr, settings) {},
        complete: function(xhr, textStatus) {},
        success: function(result, textStatus, xhr) {
            // console.log(result);
            var ary = JSON.parse(result);
            var elem = $('#confirm_' + ary['field']);
            // 改行は<br>に変換する
            ary['value'] = ary['value'].replaceAll("\n","<br>");
            console.log(ary['value']);
            elem.html(ary['value']);
            elem.addClass('flash');
            setTimeout(function() {
                elem.removeClass('flash');
            }, 1000);
            maydirty_mode(false);
            $("#seltext").val("");
        },
        error: function(xhr, textStatus, error) {
            alert("error dragontext post");
        }
    });
}

function author_affil_validation(txt){
    // 著者名・所属のバリデーションを行う
    txt = txt.trim();
    orig = txt;
    if (txt.length == 0) return true; // 空行はOK
    txt = txt.replace(/　/g, " "); // 全角スペースを半角に変換
    txt = txt.replace(/（/g, "(").replace(/）/g, ")");
    txt = txt.replace(/\(/g, "\t").replace(/\)/g, "\t");
    ary = txt.split("\t");
    if(ary.length < 3){ // 閉じ括弧もチェックするため、タブで3つに分割されていることを確認する
        alert("所属は半角または全角の括弧の中に記入してください。\n【エラー行】"+orig);
        return false;
    }
    // 氏名部分のバリデーション
    namepart = ary[0].trim();
    nameary = namepart.split(" ");
    if(nameary.length < 2){
        alert("氏名は姓と名の間を半角スペースで区切ってください。\n【エラー行】"+orig+"\n\n（例1）投稿 太郎 (投稿大学)\n（例2）Toukou, Taro (Toukou University)");
        return false;
    }
    if(namepart.indexOf(" ,") != -1){
        alert("英文の場合は、カンマのあとに半角スペースを入れてください。カンマのまえには入れないでください。\n【エラー行】"+orig+"\n\n（例1）投稿 太郎 (投稿大学)\n（例2）Toukou, Taro (Toukou University)");
        return false;
    }
    return true;
}
