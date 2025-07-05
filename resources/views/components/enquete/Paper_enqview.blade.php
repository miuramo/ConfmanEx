@props([
    'paper_id' => 0,
    'enq_id' => 1,
    'size' => 'md',
])
@php
    $paper = App\Models\Paper::find($paper_id);
    $enq = App\Models\Enquete::find($enq_id);
    // 既存回答
    $eans = App\Models\EnqueteAnswer::where('paper_id', $paper_id)->get();
    $enqans = [];
    foreach ($eans as $ea) {
        $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
    }
@endphp
<!-- components.enquete.Paper_enqview (呼び出し元は components.enquete.Rev_enqview) -->
<table class="table-auto text-{{$size}}">
    <tbody>
        {{-- TODO:もし、査読者には見せないが、インライン回答のアンケートを設定したい場合どうするか？ > showonreviewerindex をみる。
            ただし、ここでやるのではなく、ここを呼び出す場所 review.index でEnqueteを集めるときにやる。 --}}
        @forelse ($enq->items as $itm)
            @php
                $current = isset($enqans[$enq->id][$itm->id]) ? $enqans[$enq->id][$itm->id]->valuestr : null;
            @endphp
            <div class="mx-10">
                <x-enquete.itmview :itm="$itm" :current="$current" :loop="$loop" size="{{ $size }}">
                </x-enquete.itmview>
            </div>
        @empty
        @endforelse
    </tbody>
</table>
