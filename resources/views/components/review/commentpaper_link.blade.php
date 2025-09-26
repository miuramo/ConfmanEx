@props([
    'sub' => null,
])

<!-- components.review.commentpaper_link  -->
<a class="hover:underline" href="{{ route('review.commentpaper', ['cat'=>$sub->category_id, 'paper' => $sub->paper, 'token' => $sub->token() ]) }}" target="_blank">
    @if(strlen($sub->paper->title) < 1)
        @php
            // タイトル未設定のとき、アンケートから抽出する（暫定措置）
            // enqID 8  itm 35
            $enq_title = App\Models\EnqueteAnswer::where('enquete_id', App\Models\Setting::getval('ENQUETE_ID_FOR_TITLE_FALLBACK') ?? 8)
                ->where('paper_id', $sub->paper->id)
                ->where('enquete_item_id', App\Models\Setting::getval('ENQUETE_ITEM_ID_FOR_TITLE_FALLBACK') ?? 35)
                ->first();
        @endphp
        (仮) {{ $enq_title->valuestr ?? '(タイトルなし)' }}
    @elseif(mb_strlen($sub->paper->title) > 80)
        {{ mb_substr($sub->paper->title, 0, 80) }}...
    @else
        {{ $sub->paper->title }}
    @endif
</a>
