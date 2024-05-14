@props([
    'enq' => [],
    'enqans' => [],
])

<!-- components.enquete.edit (呼び出し元は主に paper.edit) -->
<table class="table-auto">
    <tbody>
        @forelse ($enq->items as $itm)
            @php
                $current = isset($enqans[$enq->id][$itm->id]) // 現在のDBの値
                    ? $enqans[$enq->id][$itm->id]->valuestr
                    : null;
                $formid = "enqform{$enq->id}";
            @endphp
            <x-enquete.itmedit :itm="$itm" :formid="$formid" :current="$current" :loop="$loop">
            </x-enquete.itmedit>
        @empty
        @endforelse
    </tbody>
</table>
