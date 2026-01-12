@props([
    'papers' => [],
    'heads' => [
        'cat',
        'id',
        'id03d',
        'title',
        'owner',
        'owneraffil',
        'owneremail',
        'contactemails',
        'acceptid',
        'accept',
        'pdf',
    ],
    'enq_ids' => [],
    // 'enqans' => [],
    // 'enqs' => [],
])
@php
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();

    $enqs = [];
    $enqans = [];
    foreach ($enq_ids as $enq_id) {
        $enqs[$enq_id] = App\Models\Enquete::find($enq_id);
        $enqans[$enq_id] = App\Models\EnqueteAnswer::where('enquete_id', $enq_id)->orderBy('paper_id')->get();
    }
    // eans にふくまれる paper_id について、Paperをもってくる
    $papers = App\Models\Paper::with('paperowner')->with('submits')->orderBy('category_id')->orderBy('id')->get();
    $eansary = [];
    foreach ($enq_ids as $enqid) {
        foreach ($enqans[$enqid] as $n => $eee) {
            $eansary[$enqid][$eee['paper_id']][$eee['enquete_item_id']] = $eee['valuestr'];
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
        @foreach ($papers as $paper)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-white dark:bg-slate-600' }}">
                <td class="p-1">{{ $paper->category_id }}
                </td>
                <td class="p-1">{{ $paper->id }}
                </td>
                <td class="p-1">{{ $paper->id_03d() }}
                </td>
                <td class="p-1">{{ $paper->title }}
                </td>
                <td class="p-1">{{ $paper->paperowner->name }}
                </td>
                <td class="p-1">{{ $paper->paperowner->affil }}
                </td>
                <td class="p-1">{{ $paper->paperowner->email }}
                </td>
                <td class="p-1">{!! nl2br($paper->contactemails) !!}
                </td>
                <td class="p-1">{{ $paper->submits[0]->accept_id }}
                </td>
                <td class="p-1">{{ $accepts[$paper->submits[0]->accept_id] }}
                </td>
                <td class="p-1">
                    @if ($paper->pdf_file_id != 0)
                        <a href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        No PDF
                    @endif
                </td>
                {{-- アンケート --}}
                @foreach ($enq_ids as $enqid)
                    @foreach ($enqs[$enqid]->items as $itm)
                        <td class="p-1">
                            @isset($eansary[$enqid][$paper->id][$itm->id])
                                {{ $eansary[$enqid][$paper->id][$itm->id] }}
                            @else
                            @endisset
                        </td>
                    @endforeach
                @endforeach

            </tr>
        @endforeach
    </tbody>
</table>
