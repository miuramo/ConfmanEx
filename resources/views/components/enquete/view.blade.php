@props([
    'enq' => [],
    'enqans' => [],
])

<!-- components.enquete.view (呼び出し元は paper.show や paper.edit など) -->
<table class="table-auto">
    <tbody>
        @forelse ($enq->items as $itm)
            @php
                $current = isset($enqans[$enq->id][$itm->id]) ? $enqans[$enq->id][$itm->id]->valuestr : null;
            @endphp
            <div class="mx-10">
                <x-enquete.itmview :itm="$itm" :current="$current" :loop="$loop">
                </x-enquete.itmview>
            </div>
        @empty
        @endforelse
    </tbody>
</table>
