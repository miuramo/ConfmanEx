<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('書誌情報の設定（PDFにふくまれるテキストを利用）') }}
        </h2>
    </x-slot>
    <!-- paper.dragontext -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="m-4">
        <div class="py-0 px-6 text-sm leading-relaxed   dark:text-gray-400">はじめに、<span
                class="border border-gray-600 bg-cyan-50 p-0.5">PDFから抽出したテキスト</span>
            の「和文タイトル部分」を、マウスドラッグによって選択してください。<br>
            選択したテキストが下の <span class="border border-gray-600 bg-yellow-100 p-0.5">エディタ</span>
            にコピーされます。必要があれば修正してください。（例：和文中の不要な空白を除去）<br>
            なお、<b>半角スペース以外の文字の追加や修正はできません。（注＊）</b><br>
            修正がおわったら、エディタ下の「和文タイトルに設定」ボタンを押すと、エディタ内のテキストを和文タイトルとして設定します。<br>
            この手順を繰り返し、アブストラクト、キーワード等についても、設定してください。<a href="#confirm_shoshi"
                class="border border-cyan-500 bg-cyan-500 text-white p-0.5">設定確認画面</a>（本ページの下部）に表示されていれば設定できています。</div>
        <div class="px-6 py-2 text-red-800 text-sm">任意項目については、空のテキストのままで結構です。（任意項目の詳細はCFPをご確認ください。）<br>
            出版にあたり、シンポジウムの予稿集・出版担当が書誌情報の体裁統一（不要な空白の削除や句読点の修正）を行う場合があります。</div>
        <div class="py-2 px-6 text-sm leading-relaxed  dark:text-gray-400"><b>（注＊）</b>PDFテキストのコピーのみで正しく設定できない場合は
            <span class="border border-gray-600 bg-purple-200 p-0.5">直接入力モード</span> にして入力してください。直接入力モードの使用は必要最小限でお願いします。
        </div>
        <div class="px-6 text-gray-500 text-sm">全角記号に囲みをつけて強調するため、アシアル情報教育研究所が開発した<a class=" hover:text-blue-500"
                target="_blank" href="https://anko.education/monacakomi">「もなかこみフォント」</a>を使用しています。</div>

        <textarea class="p-2 w-full text-xl bg-yellow-100 font-monaca" id="seltext" rows=6
            placeholder="（ここは直接入力できません。下のテキストをマウスでドラッグして、選択してください。）"></textarea>
        <div class="mb-2">
            <x-element.button onclick="return removespaces();" value="半角スペースを除去" size="sm"
                color="orange"></x-element.button>
            <x-element.button onclick="return replacekutouten();" value="句読点『 。 、 』を『 ． ， 』に置換" size="sm"
                color="yellow"></x-element.button>
            <span class="mx-10"></span>
            <x-element.button onclick="maydirty_mode(true);" value="直接入力モードにする" size="sm" color="purple"
                confirm="本当に直接入力モードにしますか？必要がなければキャンセルを押してください。"></x-element.button>
        </div>

        <div class="mb-2  dark:text-gray-400">
            エディタのテキストを
            @foreach ($koumoku as $key => $val)
                <x-element.button onclick="valset('{{ $key }}')" value="{{ $val }}に設定" size="sm"
                    color="{{ $koumokucolor[$key] }}"></x-element.button>
            @endforeach
        </div>
        {{-- <div id="pdftextdiv"> --}}
        <div class="text-sm  dark:text-gray-400">以下はPDFの1ページ目から抽出したテキストです。</div>
        <div class="py-2 px-6 bg-cyan-50 font-monaca" id="pdftext">{{ $pdftext }}</div>

        {{-- </div> --}}
    </div>
    <div class="mx-4">
        <div class="bg-cyan-500 text-white px-3 pb-1 pt-2" id="confirm_shoshi">設定確認画面
        </div>
        <table class="border-cyan-500 border-2">
            @foreach ($koumoku as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
                    <td class="px-2 py-1">{{ $v }}</td>
                    <td class="px-2 py-1" id="confirm_{{ $k }}">{{ $paper->{$k} }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <form action="{{ route('paper.dragontextpost', ['paper' => $paper->id]) }}" method="post" id="dragontextpost">
        @csrf
        @method('post')
    </form>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush

    <script>
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
            var val = $("#seltext").val();
            val = val.trim();
            val = val.replace(/ /g, "");
            $("#seltext").val(val);
            return false;
        }

        // post する (paper.dragontextpost)
        function valset(field) {
            var stext = document.getElementById("seltext").value;
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
                    elem.text(ary['value']);
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
    </script>

    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray" size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>

</x-app-layout>
