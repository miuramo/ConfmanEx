<!-- mailtempre.index -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('メール雛形') }}
        </h2>
    </x-slot>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-6">

        <form action="{{ route('mt.bundle') }}" method="post" id="mt_bundle">
            @csrf
            @method('post')

            <div class="mt-2">
                <x-element.submitbutton value="copy" color="yellow">
                    チェックをいれた雛形をコピー
                </x-element.submitbutton>
            </div>

            <table class="table-auto w-full sortable" id="sortable">
                <thead>
                    <tr class="bg-pink-200">
                        <th class="px-2 unsortable">chk</th>
                        <th class="px-2">id</th>
                        <th class="px-2">to</th>
                        <th class="px-2">subject</th>
                        <th class="px-2">name</th>
                        <th class="px-2">lastsent</th>
                        <th class="px-2">updated_at</th>
                        <th class="px-2 unsortable">(action)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mts as $mt)
                        <tr
                            class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-300' }}">
                            <td class="px-2 py-1 text-center">
                                <input type="checkbox" name="mt_{{ $mt->id }}" value="on">
                            </td>
                            <td class="px-2 py-1 text-center">
                                {{ $mt->id }}
                            </td>
                            <td class="px-2 py-1">
                                <a class="hover:font-bold hover:text-blue-600 block break-all"
                                    href="{{ route('mt.edit', ['mt' => $mt]) }}" target="editmt_{{ $mt->id }}">
                                    {{ $mt->to }}</a>
                            </td>
                            <td class="px-2 py-1">
                                <a class="hover:font-bold hover:text-lime-600"
                                    href="{{ route('mt.show', ['mt' => $mt]) }}"
                                    target="previewmt_{{ $mt->id }}">{{ $mt->subject }}</a>
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->name }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->lastsent }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->updated_at }}
                            </td>
                            <td class="px-2 py-1">
                                <x-element.linkbutton2 href="{{ route('mt.show', ['mt' => $mt]) }}" color="lime" size="xs">
                                    送信前確認
                                </x-element.linkbutton2>
                                <x-element.linkbutton2 href="{{ route('mt.edit', ['mt' => $mt]) }}" color="blue" size="xs">
                                    雛形を編集
                                </x-element.linkbutton2>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="py-2">
                <x-element.button onclick="CheckAll('mt_bundle')" color="lime" value="すべてチェック" size="xs">
                </x-element.button>
                &nbsp;
                <x-element.button onclick="UnCheckAll('mt_bundle')" color="orange" value="すべてチェック解除" size="xs">
                </x-element.button>
            </div>

            <div class="mt-2">
                <x-element.submitbutton value="copy" color="yellow">
                    チェックをいれた雛形をコピー
                </x-element.submitbutton>
                <span class="mx-2"></span>
                <x-element.submitbutton value="delete" color="red" confirm="本当に削除する？">
                    チェックをいれた雛形を削除
                </x-element.submitbutton>
                <span class="mx-2"></span>
                <x-element.submitbutton value="export" color="lime">
                    チェックをいれた雛形をExcelエクスポート
                </x-element.submitbutton>
            </div>
        </form>

        <form action="{{ route('mt.import') }}" method="post" id="mtimport" enctype="multipart/form-data" class="inline-block">
            @csrf
            @method('post')
            Excelエクスポートファイルを選択→
            <input type="file" name="file" id="file" class="text-xs">
            <x-element.submitbutton color="cyan">←で指定したファイルから雛形をインポート
            </x-element.submitbutton>
        </form>


        <div class="py-5"></div>
        <x-element.h1>Toと雛形の説明
        </x-element.h1>

        <x-mailtempre.manual>
        </x-mailtempre.manual>

    </div>
    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
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
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
