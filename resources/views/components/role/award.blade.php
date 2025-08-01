@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

    $cat_paper_count = App\Models\Category::withCount('papers')->get();
@endphp
<!-- components.role.award -->
<div class="px-6 py-4">
    <x-element.h1>
        <x-element.linkbutton href="{{ route('vote.index') }}" color="cyan" target="_blank">
            投票ページ
        </x-element.linkbutton>
        <span class="mx-2"></span>
        <x-element.linkbutton href="{{ route('vote.create_tickets') }}" color="lime">
            投票チケットを作成・送信・管理
        </x-element.linkbutton>
        @php
            $tickets = App\Models\VoteTicket::orderBy('created_at', 'desc')->get();
            $count = $tickets->count();
        @endphp
        <span class="mx-2"></span>
        <span class="text-sm">発行済み投票チケット数：{{ $count }}件</span>

    </x-element.h1>


    <x-element.h1>
        表彰用JSON →
        @php
            $dkey = App\Models\Setting::getval('AWARDJSON_DLKEY');
        @endphp
        <x-element.linkbutton href="{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" color="cyan"
            target="_blank">
            JSON
        </x-element.linkbutton>

        <span class="mx-4"></span>
        <x-element.linkbutton href="https://git.istlab.info/miura250/SSSAward" color="green" target="_blank">
            表彰状生成スクリプト
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>
        表彰用JSONのダウンロードURLは {{ route('pub.json_booth_title_author', ['key' => $dkey]) }} <br>
        （毎年変わるダウンロードキーは {{ $dkey }}）
    </x-element.h1>

    <x-element.h1>
        投票を締め切るには Vote → isclose=1 にする。
        @php
            $shortcuts = [
                'Vote' => 'votes',
                'VoteItem' => 'vote_items',
                'VoteAnswer' => 'vote_answers',
            ];
        @endphp
        @foreach ($shortcuts as $key => $tbl)
            <x-element.linkbutton color="cyan" href="{{ route('admin.crud', ['table' => $tbl]) }}" target="_blank">
                {{ $key }}
            </x-element.linkbutton>
            <span class="mx-2"></span>
        @endforeach
        <x-element.linkbutton color="cyan" href="{{ route('admin.crud') }}" target="_blank">
            CRUD
        </x-element.linkbutton>

        <span class="mx-2"></span>
        <x-element.linkbutton color="teal" href="{{ route('vote.download_answers') }}" target="_blank">
            投票結果 Excel Download
        </x-element.linkbutton>

    </x-element.h1>


    <x-vote.votesumtable>
    </x-vote.votesumtable>

    <x-element.h1>
        Danger Zone
        <span class="mx-2"></span>
        <x-element.linkbutton href="{{ route('vote.resetall', ['isclose' => 0]) }}" color="orange"
            confirm="本当に投票関係データをすべてリセットして、本番投票を開始する？">
            投票関係データをすべてリセット（本番投票を開始）
        </x-element.linkbutton>
        <span class="mx-2"></span>
        <x-element.linkbutton href="{{ route('vote.resetall', ['isclose' => 1]) }}" color="purple"
            confirm="本当に投票関係データをすべてリセットする？（投票開始にはしません）">
            投票関係データをすべてリセット（本番投票締め切り後）
        </x-element.linkbutton>
        <br>
        どちらも、投票関係データをすべてリセットしますが、本番投票締め切り後では新規投票受付はしません(Vote→isclose=1)。
    </x-element.h1>

    (VoteItem) 投稿先発表・論文の編集 ：
    @foreach(App\Models\VoteItem::all() as $voteitem)
        <x-element.linkbutton href="{{ route('vote.edit_voteitem', ['voteitem' => $voteitem->id]) }}" color="cyan" size="sm"
            target="_blank">
            {{ str_replace(['【', '】'], '', $voteitem->name) }}
        </x-element.linkbutton>
    @endforeach

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush

</div>
