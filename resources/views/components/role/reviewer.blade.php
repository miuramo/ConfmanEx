@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    // 査読プロセスをまわす（査読者を割り当てる）カテゴリ
    $cat_arrange_review = App\Models\Category::where('status__arrange_review', true)
        ->get()
        ->pluck('name', 'id')
        ->toArray();

    $bidon = App\Models\Category::select('id', 'status__bidding_on')
        ->get()
        ->pluck('status__bidding_on', 'id')
        ->toArray();
    $bidoff = App\Models\Category::select('id', 'status__bidding_off')
        ->get()
        ->pluck('status__bidding_off', 'id')
        ->toArray();
    $revon = App\Models\Category::select('id', 'status__revedit_on')
        ->get()
        ->pluck('status__revedit_on', 'id')
        ->toArray();
    $revoff = App\Models\Category::select('id', 'status__revedit_off')
        ->get()
        ->pluck('status__revedit_off', 'id')
        ->toArray();
    // 査読結果スコア一覧
    $revlist = App\Models\Category::select('id', 'status__revlist_on')
        ->get()
        ->pluck('status__revlist_on', 'id')
        ->toArray();

    $count_revassigned = App\Models\Review::select(DB::raw('count(id) as count, category_id'))
        ->where('user_id', auth()->user()->id)
        ->groupBy('category_id')
        ->get()
        ->pluck('count', 'category_id');

@endphp

<!-- components.role.reviewer -->
<div class="px-6 py-4">
    <x-element.h1>
        {{ $role->desc }}のかたは、最初に、利害表明 / Bidding を行ってください。
    </x-element.h1>

    <div class="px-6 py-2 pb-6">
        @foreach ($cats as $n => $cat)
            @isset($cat_arrange_review[$n])
                @if ($bidon[$n])
                    @if ($bidoff[$n])
                        <div class="p-1 pt-3 text-blue-400  dark:bg-slate-400">{{ $cat }}の利害表明 / Bidding 期間は、終了しました。
                        </div>
                    @else
                        <x-element.linkbutton href="{{ route('review.conflict', ['cat' => $n]) }}" color="cyan">
                            利害表明 ({{ $cat }})
                        </x-element.linkbutton> <span class="mx-2"></span>
                    @endif
                    {{-- 件数表示 --}}
                    @php
                        $numpapers_in_cat = App\Models\Paper::where('category_id', $n)
                            ->whereNotNull('pdf_file_id')
                            ->orderBy('id')
                            ->count();
                        $count_conflict = App\Models\RevConflict::countByCatAndUser($n, auth()->id());
                    @endphp
                    @if ($count_conflict == $numpapers_in_cat)
                        <span
                            class="bg-cyan-100 dark:bg-cyan-300 border-2 border-blue-600 p-1 text-blue-600 font-bold">全{{$numpapers_in_cat}}件 入力完了👍</span>
                    @else
                        <a href="{{ route('review.conflict', ['cat'=>$n, 'noans_only'=>1])}}"><span class="text-red-600 font-bold border-2 border-red-600 p-1 bg-pink-100 dark:bg-pink-300">
                            {{ $numpapers_in_cat }} 件中 {{ $numpapers_in_cat - $count_conflict }} 件が未入力です😰
                        </span></a>
                    @endif
                @else
                    <div class="p-1 pt-3 text-gray-400">{{ $cat }}の利害表明 / Bidding は、まだ開始していません。</div>
                @endif
            @endisset
        @endforeach
    </div>

    <x-element.h1>
        査読期間になりましたら、以下のボタンから、査読をお願いします。
    </x-element.h1>
    @php
        $total_revon = false;
        foreach ($cats as $n => $cat) {
            if ($revon[$n]) {
                $total_revon = true;
            }
        }
    @endphp
    @if ($total_revon)
        <div class="mx-6 my-4">
            <x-element.linkbutton href="{{ route('review.index') }}" color="lime">
                査読を担当していただく投稿の一覧
            </x-element.linkbutton>
        </div>
        <div class="px-6 py-2 pb-6">
            @foreach ($cats as $n => $cat)
                @isset($cat_arrange_review[$n])
                    @if ($revon[$n])
                        @if (isset($count_revassigned[$n]) && $count_revassigned[$n] > 0)
                            <x-element.linkbutton href="{{ route('review.indexcat', ['cat' => $n]) }}" color="lime">
                                {{ $cat }}のみの一覧
                            </x-element.linkbutton>
                        @else
                            <span class="p-1 pt-3 text-gray-400">{{ $cat }}の査読担当はありません</span>
                        @endisset
                    @else
                        <span class="p-1 pt-3 text-gray-400">{{ $cat }}の査読開始前です</span>
                    @endif
                @endisset
                <span class="mx-2"></span>
            @endforeach
    </div>
@else
    <div class="m-2 p-2 text-gray-400">まだ査読割り当て作業中、または査読開始前です</div>
@endif

@php
    $total_revlist = false;
    foreach ($cats as $n => $cat) {
        if ($revlist[$n] && App\Models\Category::isShowReview($n)) {
            // ここはあえて$revlist[$n]を併用しないと、PC長の権限があるとき問題が起きる
            $total_revlist = true;
        }
    }
@endphp
@if ($total_revlist)
    <x-element.h1>
        査読協力ありがとうございました。査読結果・スコアの一覧は以下から参照できます。
    </x-element.h1>
    <div class="mx-6 my-4">
        @foreach ($cats as $n => $cat)
            @if ($revlist[$n] && App\Models\Category::isShowReview($n))
                <x-element.linkbutton href="{{ route('review.comment_scoreonly', ['cat' => $n]) }}" color="purple"
                    target="_blank">
                    査読結果・スコアの一覧 ({{ $cat }})
                </x-element.linkbutton> <span class="mx-2"></span>
            @endif
        @endforeach
    </div>
@endif

@php
    $sbbs = App\Models\Bb::getShepherdingBbs(auth()->id());
    $nameofmeta = App\Models\Setting::getval('NAME_OF_META');
@endphp
@if ($sbbs->count() > 0)
    <x-element.h1>
        {{ $nameofmeta }}と著者の掲示板 （シェファーディング掲示板）
    </x-element.h1>
    <div class="mx-6 my-4">
        @foreach ($sbbs as $sbb)
            <x-element.linkbutton href="{{ route('bb.show', ['bb' => $sbb->id, 'key' => $sbb->key]) }}"
                color="pink" target="_blank">
                {{ $sbb->paper->id_03d() }} : {{ $sbb->paper->title }}
            </x-element.linkbutton>
            <div class="my-3"></div>
        @endforeach
    </div>
@endif

</div>


@php
    // 担当の査読状況
    // TODO: 査読結果一覧を開示したら、リンクを表示する revlist_on
    // もし担当したときの査読フォーム review.
@endphp
