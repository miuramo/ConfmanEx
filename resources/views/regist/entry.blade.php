<x-app-layout>
    <!-- regist.entry -->
    @php
    @endphp
    @section('title', '参加登録')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('参加登録') }}
        </h2>
    </x-slot>

    <div class="py-2 px-6">
        <x-element.sankou>
            参加登録の流れは、以下のようになります。
            <ol class="list-decimal px-8 pt-4 leading-relaxed">
                <li> <x-element.linkbutton href="{{ route('entry') }}">参加者／投稿者アカウントの作成</x-element.linkbutton>
                    （参加者のかたにも、先に投稿者アカウントを作成していただきます。）<br>
                    <b>（すでに著者または委員としてアカウントがある方は不要です。<x-element.linkbutton href="{{ route('regist.index') }}"
                            color="green">ログインして参加登録</x-element.linkbutton> (手順 2.) にお進みください。）</b>
                </li>
                <ol class="list-inside list-disc px-8 leading-relaxed">
                    <li> メールで届いた認証URLをクリック</li>
                    <li> パスワードの設定</li>
                    <li> 氏名と所属の登録</li>
                </ol>
                <li> メニューの「参加登録」→「参加登録を開始する」</li>
                <li> 「入力内容のチェック」後、「参加登録を完了」をクリック</li>
                <li> 「参加登録確認メールを送信する」をクリックし、参加登録メールが届くことを確認（迷惑メールフォルダもご確認ください）</li>
            </ol>

        </x-element.sankou>
    </div>
    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
        }

        function CheckNoTag(formname, cls) {
            // JQueryで、クラスがclsである要素を取得し、その要素のチェックボックスをチェックする
            $("." + cls).prop('checked', true);
        }

        function UnCheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = false;
                }
            }
        }
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
