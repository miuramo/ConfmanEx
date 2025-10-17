@props([
    'papers' => [],
    'heads' => ['uid', 'name', 'affil', 'email', 'submitted_at', 'isearly', 'canceled_at'],
    'enqans' => [],
    'enq' => [],
])
@php
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();

    // アンケート回答したユーザ
    $uids = App\Models\EnqueteAnswer::where('enquete_id', $enq->id)->distinct()->pluck('user_id')->toArray();
    $users = App\Models\User::whereIn('id', $uids)->get();
    $eansary = [];
    foreach ($enqans as $n => $eee) {
        $eansary[$eee['paper_id']][$eee['enquete_item_id']] = $eee['valuestr'];
    }
    foreach ($enq->items as $itm) {
        $heads[] = $itm->name;
    }
    $OFFSET = 0;

    $regists = \App\Models\Regist::where('valid', 1)->get()->keyBy('user_id');

@endphp
<!-- components.admin.enqtable_nopaper -->

<table class="min-w-full divide-y divide-gray-200 text-sm">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200 dark:text-white">
        @foreach ($users as $paper)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-white dark:bg-slate-600' }}">
                <td class="p-1">{{ $paper->id }}
                </td>
                <td class="p-1">{{ $paper->name }}
                </td>
                <td class="p-1">{{ $paper->affil }}
                </td>
                <td class="p-1">{{ $paper->email }}
                </td>
                <td class="p-1">
                    @isset($regists[$paper->id])
                        {{ $regists[$paper->id]->submitted_at }}
                    @endisset
                </td>
                <td class="p-1">
                    @isset($regists[$paper->id])
                        {{ $regists[$paper->id]->isearly }}
                    @endisset
                </td>
                <td class="p-1">
                    @isset($regists[$paper->id])
                        {{ $regists[$paper->id]->canceled_at }}
                    @endisset
                </td>
                {{-- アンケート --}}
                @foreach ($enq->items as $itm)
                    <td class="p-1">
                        @isset($eansary[$OFFSET + $paper->id][$itm->id])
                            {{ $eansary[$OFFSET + $paper->id][$itm->id] }}
                        @else
                        @endisset
                    </td>
                @endforeach

            </tr>
        @endforeach
    </tbody>
</table>
