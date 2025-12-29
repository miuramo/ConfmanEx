// var reduce_404_error_for_dummyform = false;

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
            var ary = JSON.parse(result);
            // console.log(ary);
            var elem = $("#" + name + "_answer");
            if (ary[name] == null) {
                if (ary[name+'_is_mandatory'] == 1) elem.html('<span class="text-red-600 font-extrabold">(未入力)</span>');
                else elem.html('<span class="text-blue-600 font-extrabold">(未入力)</span>');
            } else if (typeof ary[name].replaceAll === 'function') {
                elem.html(ary[name].replaceAll("&", "&amp;").replaceAll("<", "&lt;")
                    .replaceAll(">", "&gt;").replaceAll("\r\n", "<br>"));
            }
            elem.addClass('flash');
            if (ary['reload_on_change'] == 1 ||
                ary['reload_on_firstinput'] == 1 && ary['firstinput'] == true) {
                    console.log("will reload");
            }
            setTimeout(function () {
                elem.removeClass('flash');
                if (ary['reload_on_change'] == 1 ||
                    ary['reload_on_firstinput'] == 1 && ary['firstinput'] == true) {
                    location.reload();
                    // window.location.href = "/paper/" + ary['paper_id'] + "/edit";
                }
            }, 1000); // フラッシュの時間
            // もし、unsavedTextareas が定義されており、name がその中に含まれている場合は、nameを削除する
            if (typeof unsavedTextareas !== 'undefined' && unsavedTextareas.has(name)) {
                unsavedTextareas.delete(name);
            }
        },
        error: function (xhr, textStatus, error) {
            alert("error form submit (form changed, but not saved.)");
            console.log(textStatus);
        }
    });
}

function validateAndSubmit4NumberInput(input) {
    // このままでは、min〜max外の値も送信してしまうので、
    // 入力値を取得
    let value = parseInt(input.value);
    // min と max の値を取得
    const min = parseInt(input.getAttribute('min'));
    const max = parseInt(input.getAttribute('max'));

    // min と max の範囲に収める
    if (value < min) {
        value = min;
    } else if (value > max) {
        value = max;
    }
    input.value = value;

    // changeイベントを擬似的に発火させる
    const changeEvent = new Event('change', {
        bubbles: true,
        cancelable: true
    });
    input.dispatchEvent(changeEvent);
}

const numberInputs = document.querySelectorAll('input[type="number"]');
// 各 <input type="number"> 要素に対してイベントリスナーを追加
numberInputs.forEach(input => {
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            validateAndSubmit4NumberInput(input);
        }
    });
    input.addEventListener('blur', function (event) {
        event.preventDefault();
        validateAndSubmit4NumberInput(input);
    });
});
