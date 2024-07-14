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
            var elem = $("#" + name + "_answer");
            if (ary[name] == null) {
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
            // if (xhr.status == 404) {
            //     if (!reduce_404_error_for_dummyform) {
            //         if (confirm("プレビュー用フォームでは送信できません。そのため、なにか入力しても「未入力」のままになります。\nこのメッセージを短期的に表示しないようにするには、OKをおしてください")) {
            //             reduce_404_error_for_dummyform = true;
            //         }
            //     }
            //     return; // ダミーフォーム、プレビュー用フォームのとき
            // }
            alert("error form submit (form changed, but not saved.)");
            console.log(textStatus);
        }
    });
}

const numberInputs = document.querySelectorAll('input[type="number"]');
// 各 <input type="number"> 要素に対してイベントリスナーを追加
numberInputs.forEach(input => {
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();

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
    });
});
