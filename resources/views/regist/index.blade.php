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
                        @if ($reg->valid)
                            参加登録内容を確認する
                        @else
                            参加登録を継続する
                        @endif
                    </x-element.linkbutton>

                    <x-element.deletebutton action="{{ route('regist.destroy', ['regist' => $reg->id]) }}"
                        confirm="参加登録を削除します。よろしいですか？" color="red" align="right">
                        参加登録を削除する
                    </x-element.deletebutton>
                </x-element.h1>
                <div class="mx-6">
                    現在の参加登録内容は以下の通りです。
                    <table class="table-auto">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2 bg-slate-200 dark:bg-slate-500">項目</th>
                                <th class="border px-4 py-2 bg-slate-200 dark:bg-slate-500">内容</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border px-4 py-2 dark:text-gray-100">状況</td>
                                <td class="border px-4 py-2 dark:text-gray-100">
                                    @if ($reg->valid)
                                        <span class="text-green-500 font-extrabold">有効</span>
                                    @else
                                        <span class="text-red-500 font-extrabold">無効（まだ申込は完了していません）</span>
                                    @endif
                                </td>
                            </tr>
                            @if ($reg->valid)
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100">参加登録ID</td>
                                    <td class="border px-4 py-2 dark:text-gray-100">{{ $reg->id }}</td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100">申込日時</td>
                                    <td class="border px-4 py-2 dark:text-gray-100">{{ $reg->submitted_at }}</td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100">申込種別</td>
                                    <td class="border px-4 py-2 dark:text-gray-100">
                                        @if ($reg->isearly)
                                            早期申込
                                        @else
                                            通常申込
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                <x-element.h1>
                    上記について、すべて確認・了承したうえで、参加登録を開始してください。
                    <br>
                    （注：現在は採録著者とプログラム委員のみ登録できます。）<br>
                    <x-element.linkbutton href="{{ route('regist.create') }}" color="cyan">
                        参加登録を開始する
                    </x-element.linkbutton>
                </x-element.h1>
            @endisset
            @if ($reg && $reg->valid)
                <div class="py-2 px-6">
                    <x-element.linkbutton href="{{ route('regist.email', ['regist' => $reg->id]) }}" color="cyan"
                        confirm="参加登録内容メールを送信します。よろしいですか？">
                        参加登録内容を自分にメール送信する
                    </x-element.linkbutton>
                </div>
            @endif

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
