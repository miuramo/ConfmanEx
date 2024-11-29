<!-- mailtempre.index -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            メール雛形 {{ $mt->id }} の編集
        </h2>
    </x-slot>
    @section('title', 'EditM ' . $mt->id)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <style>
        @keyframes flash {
            0% {
                background-color: #0af0f8;
            }

            100% {
                background-color: transparent;
            }
        }

        .flash-success {
            animation: flash 1.3s ease-in-out;
        }
    </style>
    <div class="py-2 px-6" id="flashable">
        <div class="my-5">
            <x-element.linkbutton href="{{ route('mt.index') }}" color="gray" size="sm">
                &larr; 雛形一覧に戻る
            </x-element.linkbutton>
        </div>

        <form action="{{ route('mt.store') }}" method="post" id="mt_store">
            @csrf
            @method('post')

            <table>
                <thead>
                </thead>
                <tbody>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1">
                            <label for="to">To</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="to" id="to" size="50"
                                value="{{ $mt->to }}"><br>
                            <span class="text-sm bg-white font-monaca font-bold p-1">複数指定する場合は && で区切ってください（セミコロン( ; ) や
                                || でも可）。</span>
                            <span class="text-sm bg-yellow-100 text-red-600 font-bold">注：メールアドレスは指定できません。</span>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1">
                            <label for="subject">Subject</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="subject" id="subject" size="100"
                                value="{{ $mt->subject }}">
                        </td>
                    </tr>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1">
                            <label for="body">Body</label>
                        </td>
                        <td class="px-2 py-1 flex items-end">
                            <textarea name="body" cols="100" rows="20" id="mt_body">{{ $mt->body }}</textarea>
                            <select class="font-sans text-xs" onchange="other_textchange(event);">
                                <option>【テキスト一括処理】</option>
                                <option value="replace_kuten">、。を，．に変換</option>
                                <option value="replace_kuten2">，．を、。に変換</option>
                            </select>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1">
                            <label for="subject">name</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="name" id="name" size="60"
                                value="{{ $mt->name }}">
                            <span class="text-sm bg-yellow-100">（オプション）雛形一覧での識別用。送信内容には影響しません。</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2">
                <input type="hidden" name="id" value="{{ $mt->id }}">
                先に
                <x-element.submitbutton color="pink">
                    保存 (CTRL+S)
                </x-element.submitbutton>
                してから、
                <x-element.linkbutton2 href="{{ route('mt.show', ['mt' => $mt]) }}" color="lime"
                    target="previewmt_{{ $mt->id }}">
                    送信前の確認画面
                </x-element.linkbutton2>
                を押してください。
                <span class="mx-10"></span>
                <x-element.linkbutton href="{{ route('admin.crud', ['table' => 'mail_templates', 'row' => $mt->id]) }}"
                    target="_blank" color="gray">
                    （管理者編集）
                </x-element.linkbutton>
            </div>
        </form>

        <div class="py-5"></div>
        <x-element.h1>Toと雛形の説明
        </x-element.h1>

        <x-mailtempre.manual>
        </x-mailtempre.manual>

    </div>

    <script>
        document.addEventListener('keydown', function(event) {
            if ((event.ctrlKey || event.altKey || event.metaKey) && event.key === 's') {
                event.preventDefault();
                saveForm();
            }
        });

        function saveForm() {
            const form = document.querySelector('#mt_store');
            const formData = new FormData(form);
            fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json' // JSONレスポンスを期待するヘッダーを追加
                    }
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.result === '保存成功') {
                        flashSuccess();
                    } else {
                        alert('保存に失敗しました。');
                    }
                })
                .catch(error => {
                    console.error('エラー:', error);
                    alert('保存中にエラーが発生しました。');
                });
        }

        function flashSuccess() {
            document.querySelector('#flashable').classList.add('flash-success');
            setTimeout(() => {
                document.querySelector('#flashable').classList.remove('flash-success');
            }, 1300); // フラッシュの持続時間を1.3秒に設定
        }

        // crud でのテキスト編集
        function other_textchange(event) {
            // console.log(event.target.value); // selected option value
            // console.log(event.target.id); // select
            var tdid = "mt_body";
            var text = $("#" + tdid).val();
            if (event.target.value == "replace_kuten") {
                var newtext = text.replace(/、/g, "，").replace(/。/g, "．");
                $("#" + tdid).val(newtext);
            } else if (event.target.value == "replace_kuten2") {
                var newtext = text.replace(/，/g, "、").replace(/．/g, "。");
                $("#" + tdid).val(newtext);
            }
        }
    </script>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush

</x-app-layout>
