<x-app-layout>
    <!-- regist.index -->
    @php
    @endphp
    @section('title', '参加登録')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('参加登録') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-4">
        <div class="py-2 px-6">
            <x-element.h1>注意事項</x-element.h1>
            @php
                $sankakakunin = App\Models\Confirm::where('grp', 2)
                    ->where('valid', 1)
                    ->select('name', 'mes')
                    ->get()
                    ->pluck('mes', 'name')
                    ->toArray();
            @endphp
            <ul class="m-4">
                @foreach ($sankakakunin as $name => $mes)
                    <li class="hover:bg-lime-100 dark:text-slate-400 dark:hover:bg-lime-950">
                        <input type="checkbox" checked="checked" class="checked:bg-lime-500">
                        {!! $mes !!}
                    </li>
                @endforeach
            </ul>

        </div>
        <div class="py-2 px-6">
            @php
                $reg = App\Models\Regist::where('user_id', Auth::user()->id)->first();
            @endphp
            @isset($reg)
                <x-element.h1>
                    <x-element.linkbutton href="{{ route('regist.edit', ['regist' => $reg->id]) }}" color="lime">
                        参加登録を確認・編集する
                    </x-element.linkbutton>
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('regist.edit', ['regist' => $reg->id]) }}" color="cyan"
                        confirm="参加登録内容を確認したうえで、参加登録確認メールを送信します。よろしいですか？">
                        参加登録確認メールを送信する
                    </x-element.linkbutton>

                    <x-element.deletebutton action="{{ route('regist.destroy', ['regist' => $reg->id]) }}"
                        confirm="参加登録を削除します。よろしいですか？" color="red" align="right">
                        参加登録を削除する
                    </x-element.deletebutton>
                </x-element.h1>
                現在の参加登録内容は以下の通りです。
                <table class="table-auto">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">項目</th>
                            <th class="px-4 py-2">内容</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border px-4 py-2">申込日時</td>
                            <td class="border px-4 py-2">{{$reg->submitted_at}}</td>
                        </tr>
                        <tr>
                            <td class="border px-4 py-2">早期申込</td>
                            <td class="border px-4 py-2">{{$reg->isearly}}</td>
                        </tr>
                    </tbody>
                @else
                    <x-element.h1>
                        上記について、すべて確認・了承したうえで、参加登録を開始してください。
                        <br>
                        <br>
                        <x-element.linkbutton href="{{ route('regist.create') }}" color="cyan">
                            参加登録を開始する
                        </x-element.linkbutton>
                    </x-element.h1>
                @endisset

        </div>

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
        <script src="/js/form_changed.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
