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
