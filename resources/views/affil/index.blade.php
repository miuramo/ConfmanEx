<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">

            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>

        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('所属の修正ルールの一覧') }}
            <span class="mx-6"></span>
            <x-element.linkbutton href="{{ route('affil.create') }}" color="lime" size="md">
                ルールの再構築（preおよび削除ルール以外）
            </x-element.linkbutton>
            <span class="mx-2"></span>
            <x-element.linkbutton href="{{ route('affil.rebuild') }}" color="red" size="sm" confirm="本当に全ルールを再構築する？">
                全ルールの再構築
            </x-element.linkbutton>
        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    <div class="m-4 py-2 px-2 bg-white">
        <span class="bg-gray-500 text-white px-2 py-1">説明</span> pre は事前適用ルールです。複数所属分割文字列が / 以外になっている場合に、指定してください。修正後は「所属1/所属2」のように指定してください。<br>
        pre_score は、事前適用ルール候補になりうるものに、大きい値を与えています。<br>
        skip がチェックされているルールは、適用しません。<br>
        「修正後」が空になっているルールは、削除ルールです。学部・学科などの所属を削除する場合には、修正後を空にしてください。<br>
        元テキストは、投稿者が入力した際のテキストです。ただし、関連PaperIDが複数の場合、それらのうちの一つのみが表示されます。<br>
    </div>

    <div class="py-2 px-2">
        <form action="{{ route('affil.update') }}" method="post">
            @csrf
            <div class="mt-4">
                <x-element.submitbutton value="update" color="blue">
                    更新
                </x-element.submitbutton>
            </div>

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 sortable" id="sortable">
                <thead>
                    <tr>
                        <th class="px-2 py-2 text-left">ID</th>
                        <th class="px-2 py-2 text-left">修正前／修正後</th>
                        <th class="px-2 py-2 text-left">pre</th>
                        <th class="px-2 py-2 text-left">pre_score</th>
                        <th class="px-2 py-2 text-left">skip</th>
                        <th class="px-2 py-2 text-left unsortable">関連PaperID／元テキスト</th>
                        <th class="px-2 py-2 text-left">修正後</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($affils as $affil)
                        <tr>
                            <td class="px-2 py-2">{{ $affil->id }}</td>
                            <td class="px-2 py-2">
                                {{-- <input type="hidden" name="id[]" value="{{ $affil->id }}"> --}}
                                <span class="mx-1"></span>{{ $affil->before }} <br>
                                <input type="text" name="after[{{$affil->id}}]" value="{{ $affil->after }}"
                                    class=" border-gray-300 dark:border-gray-700 rounded-md shadow-sm py-1 px-2" size="64">
                            </td>
                            <td class="px-2 py-2">
                                <input type="hidden" name="pre[{{$affil->id}}]" value="off">
                                <input type="checkbox" name="pre[{{$affil->id}}]" @if ($affil->pre) checked @endif
                                    class=" border-green-500  text-green-600">
                            </td>
                            <td class="px-2 py-2">
                                {{ $affil->orderint }}
                            </td>
                            <td class="px-2 py-2">
                                @if($affil->skip) <span class="text-pink-600 text-xs">skip</span> @endif
                                <input type="hidden" name="skip[{{$affil->id}}]" value="off">
                                <input type="checkbox" name="skip[{{$affil->id}}]" @if ($affil->skip) checked @endif
                                    class=" border-red-500 text-pink-500">
                                @if(!$affil->skip && $affil->before == $affil->after) <br><span class="text-pink-600 text-xs">SKIP候補</span> @endif
                            </td>
                            <td class="px-2 py-2">
                                @if (is_array($affil->pids))
                                    @foreach ($affil->pids as $pid)
                                        <x-element.linkbutton
                                            href="{{ route('pub.bibinfochk_paper', ['paper' => $pid]) }}"
                                            target="bibinfochk_paper" color="blue" size="sm">
                                            {{ $pid }}
                                        </x-element.linkbutton>
                                    @endforeach
                                @endif
                                <br>
                                <span class="text-xs">{{ $affil->origtxt }}</span>
                            </td>
                            <td class="p-2 text-xs
                            @if($affil->skip) text-pink-100 @endif
                            ">
                                {{ $affil->after }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                <x-element.submitbutton value="update" color="blue">
                    更新
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush
</x-app-layout>
