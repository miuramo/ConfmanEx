<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
            @endphp
            VoteItem {{ $voteitem->id }} : {{ $voteitem->name }} の編集
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="m-3">
        {{ $voteitem->name }}の投稿先確認
    </div>

    <div class="mx-6">
        <ul>
            @foreach (json_decode($voteitem->submits) as $booth => $pid)
                <li>
                    <input type="checkbox" name="pids[]" value="{{ $pid }}" id="chk_{{ $pid }}" checked>
                    <label for="chk_{{ $pid }}">{{ $booth }} : {{ sprintf("%03d", $pid) }}</label>
                </li>
            @endforeach
        </ul>
    </div>
    <div class="pt-4">
        <hr>
    </div>

    <div class="m-6">
        <form id="rebuild" method="POST" action="{{ route('vote.update_voteitem', ['voteitem' => $voteitem->id]) }}">
            @csrf
            <div class="mx-0">カテゴリID</div>
            <input type="number" name="category_id" value="1" size="1" min="1" max="9">
            <div class="mx-0">PaperIDのカンマ区切り</div>
            <input type="text" name="pid_str" value="7,25,33,21,39,22" placeholder="001, 002, 003" class="w-full mb-2">
            <x-element.submitbutton value="rebuild" color="pink" size="sm" confirm="指定したPaperIDで再構築しますか？">
                指定したPaperIDで投稿先を再構築
            </x-element.submitbutton>
        </form>
    </div>
    <div class="py-2"></div>

    @push('localjs')
        <script src="/js/sortable.js"></script>
        <script src="/js/chk_all.js"></script>
    @endpush
    <script>
        function checkAllByClass(cls) {
            var checks = document.getElementsByClassName(cls);
            for (var i = 0; i < checks.length; i++) {
                checks[i].checked = true;
            }
        }
    </script>

</x-app-layout>
