@props([
    'rev' => null,
    'size' => 'md',
])
<!-- components.enquete.Rev_enqview (呼び出し元は review.index) -->
@php
    $enqs = App\Models\Enquete::needForSubmit($rev->paper);
    $showonrevindexenq = [];
    foreach ($enqs['canedit'] as $enq) {
        if ($enq->showonreviewerindex) {
            $showonrevindexenq[] = $enq;
        }
    }
    foreach ($enqs['readonly'] as $enq) {
        if ($enq->showonreviewerindex) {
            $showonrevindexenq[] = $enq;
        }
    }
@endphp
@foreach ($showonrevindexenq as $enq)
    <x-enquete.Paper_enqview :paper_id="$rev->paper->id" :enq_id="$enq->id" size="{{$size}}">
    </x-enquete.Paper_enqview>
@endforeach
