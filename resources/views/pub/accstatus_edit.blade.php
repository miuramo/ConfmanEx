@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('論文ごとの採否状況の確認と編集') }}
        </h2>
    </x-slot>

    <div class="m-4">
        <div class="px-4 rounded-md bg-yellow-100 border-yellow-400 border-2 p-2 text-sm">
            <p class="font-bold">編集の方法と注意点</p>
            <ul class="list-disc list-inside">
                <li>採否判定やブースをダブルクリックすると編集できます。ESCキーで編集をキャンセルできます。</li>
                <li>採否判定が無い場合も、ダブルクリックすると採否を追加できます。ただし、追加した採否に対応するブースを編集するには、一度再読み込みが必要です。</li>
                <li>採否判定を消すには、一旦判定を「---」にしたうえで、再度ダブルクリックしたときに表示される「削除」ボタンを押してください。</li>
                <li>ただし、査読が行われている（スコアがある）場合は、採否判定の削除はできません。</li>
            </ul>
    </div>
    @php
        $catspans = App\Models\Category::spans();
        $paperids = App\Models\Paper::select('id', 'title')->get()->pluck('title', 'id')->toArray();
    @endphp
    <div class="px-4 py-4">
        <table class="sortable" id="paper-accept-table">
            <tr>
                <th class="px-2 py-1 border-b-2 border-pink-400">Paper ID</th>
                <th class="px-2 py-1 border-b-2 border-pink-400">タイトル</th>
                @foreach ($cats as $cid => $cname)
                    <th class="px-2 py-1 border-b-2 border-pink-400 bg-{{$catcolors[$cid]}}-200">{{$cname}}</th>
                    <th class="px-2 py-1 border-b-2 border-pink-400 bg-{{$catcolors[$cid]}}-200">{{$cname}} booth</th>
                @endforeach
            </tr>
            @foreach ($paperids as $pid => $ptitle)
                <livewire:paper-accept :paper_id="$pid" :paper_title="$ptitle" />
                <span class="my-4"></span>
            @endforeach
        </table>

    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @push('localjs')
        <script src="/js/sortable_rev.js"></script>
    @endpush

</x-app-layout>
