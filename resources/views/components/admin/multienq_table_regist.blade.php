@props([
    'regists' => [],
    'heads' => [
        'regid',
        'uid',
        'name',
        'affil',
        'email',
        'submitted_at',
        'updated_at',
        'isearly',
        'canceled_at'
    ],
    'enq_ids' => [],
    // 'enqans' => [],
    // 'enqs' => [],
])
@php
    $enqs = [];
    $enqans = [];
    foreach ($enq_ids as $enq_id) {
        $enqs[$enq_id] = App\Models\Enquete::find($enq_id);
        $enqans[$enq_id] = App\Models\EnqueteAnswer::where('enquete_id', $enq_id)->orderBy('user_id')->get();
    }
    // eans にふくまれる paper_id について、Paperをもってくる
    $regists = App\Models\Regist::with('user')->orderBy('id')->get();
    $eansary = [];
    foreach ($enq_ids as $enqid) {
        foreach ($enqans[$enqid] as $n => $eee) {
            $eansary[$enqid][$eee['user_id']][$eee['enquete_item_id']] = $eee['valuestr'];
        }
        foreach ($enqs[$enqid]->items as $itm) {
            $heads[] = $itm->name;
        }
    }
@endphp
<!-- components.admin.enqtable -->

<table class="min-w-full divide-y divide-gray-200 text-sm sortable" id="enqtable">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200 dark:text-white">
        @foreach ($regists as $regist)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-white dark:bg-slate-600' }}">
                <td class="p-1">{{ $regist->id }}
                </td>
                <td class="p-1">{{ $regist->user->id }}
                </td>
                <td class="p-1">{{ $regist->user->name }}
                </td>
                <td class="p-1">{{ $regist->user->affil }}
                </td>
                <td class="p-1">{{ $regist->user->email }}
                </td>
                <td class="p-1">{{ $regist->submitted_at }}
                </td>
                <td class="p-1">{{ $regist->updated_at }}
                </td>
                <td class="p-1">{{ $regist->isearly }}
                </td>
                <td class="p-1">{{ $regist->canceled_at }}
                </td>
                {{-- アンケート --}}
                @foreach ($enq_ids as $enqid)
                    @foreach ($enqs[$enqid]->items as $itm)
                        <td class="p-1">
                            @isset($eansary[$enqid][$regist->user->id][$itm->id])
                                {{ $eansary[$enqid][$regist->user->id][$itm->id] }}
                            @else
                            @endisset
                        </td>
                    @endforeach
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>
