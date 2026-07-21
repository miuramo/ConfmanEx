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
        <x-element.linkbutton href="{{ route('pub.json_booth_title_author', ['key' => $dkey]) }}" color="cyan" size="sm"
            target="_blank">
            JSON
        </x-element.linkbutton>

        <span class="mx-4"></span>
        <x-element.linkbutton href="https://git.istlab.info/miura250/SSSAward" color="green" target="_blank" size="sm">
            表彰状生成スクリプト
        </x-element.linkbutton>

        <div class="text-sm mt-4">
            表彰用JSONのダウンロードURLは {{ route('pub.json_booth_title_author', ['key' => $dkey]) }} <br>
            （毎年変わるダウンロードキーは {{ $dkey }} ）
        </div>
    </x-element.h1>

    <x-vote.votesumtable>
    </x-vote.votesumtable>

    <div class="my-8">
    </div>

    <x-element.h1>
        <div class="text-sm">投票を締め切るには Vote → isclose=1 にする。投票件数を直接設定するにはVoteItem → upperlimit を設定する。</div>
        @php
            $shortcuts = [
                'Vote' => 'votes',
                'VoteItem' => 'vote_items',
                'VoteAnswer' => 'vote_answers',
            ];
        @endphp
        @foreach ($shortcuts as $key => $tbl)
            <x-element.linkbutton color="cyan" href="{{ route('admin.crud', ['table' => $tbl]) }}" target="_blank" size="sm">
                {{ $key }}
            </x-element.linkbutton>
            <span class="mx-2"></span>
        @endforeach

        <span class="mx-2"></span>
        <x-element.linkbutton color="teal" href="{{ route('vote.download_answers') }}" target="_blank">
            投票結果 Excel Download
        </x-element.linkbutton>

    </x-element.h1>



    <div class="my-8">
    </div>

    <x-element.h1>
        Danger Zone
        <span class="mx-2"></span>
        <x-element.linkbutton href="{{ route('vote.initializeall') }}" color="lime" size="sm"
            confirm="本当に投票の初期設定を行う？（投票データはリセットしません）">
            投票の初期設定を行う
        </x-element.linkbutton>
        <span class="text-sm">←既存の投票データはリセットしません。Vote, VoteItem が無い場合に、デフォルト値で作成します。</span>
        <x-element.linkbutton
            href="{{ route('vote.initializeall', ['truncate_vote' => 0, 'truncate_voteitem' => 1]) }}" color="orange"
            size="sm" confirm="本当に投票の初期設定を行う？（VoteItemを削除して再構成します）">
            投票の初期設定（VoteItemを削除して再構成）
        </x-element.linkbutton> <span
            class="text-sm">←Voteを残すことで、既存設定（学生を分けるかどうかや、投票件数割合など）をつかってVoteItemのみを再構成できます。</span>

        <x-element.linkbutton
            href="{{ route('vote.initializeall', ['truncate_vote' => 1, 'truncate_voteitem' => 1]) }}" color="red"
            size="sm" confirm="本当に投票の初期設定を行う？（Vote, VoteItemを削除して再構成します）">
            投票の初期設定（VoteとVoteItemを削除して再構成）
        </x-element.linkbutton>


        <div class="my-6">
            VoteItem 投稿先発表・論文の編集 ：
            @foreach (App\Models\VoteItem::all() as $voteitem)
                <x-element.linkbutton href="{{ route('vote.edit_voteitem', ['voteitem' => $voteitem->id]) }}"
                    color="cyan" size="sm" target="_blank">
                    {{ str_replace(['【', '】'], '', $voteitem->name) }}
                </x-element.linkbutton>
            @endforeach

        </div>

        <x-element.linkbutton href="{{ route('vote.resetall', ['isclose' => 0]) }}" color="purple" size="sm"
            confirm="本当に投票データをすべてリセットする？">
            投票データをすべてリセット
        </x-element.linkbutton>
        <span class="text-sm">確認ダイアログがでます</span>
    </x-element.h1>


    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</div>
