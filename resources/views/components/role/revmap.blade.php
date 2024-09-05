@props([
    'reviewers' => [],
    'cat' => null,
    'papers' => [],
])
@php
    $bids = App\Models\RevConflict::arr_pu_bname();
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $stars = App\Models\Review::arr_pu_star();
@endphp
<!-- components.role.revmap -->
<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300"> paper \ reviewer</th>
            @foreach ($reviewers as $rev)
                <th class="p-1 bg-slate-300 n text-sm" id="u{{ $rev->id }}">{{ $rev->name }} ({{ $rev->affil }})
                </th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($papers as $p)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td id="paper{{ $p->id }}"
                    class="p-1 text-sm
                    @if (isset($rigais[$p->id][auth()->id()]) && $rigais[$p->id][auth()->id()] < 3) text-gray-400 @endif
                    ">
                    {{ $p->id_03d() }}
                    {{ $p->title }}
                </td>

                @foreach ($reviewers as $rev)
                    @if (isset($rigais[$p->id][auth()->id()]) && $rigais[$p->id][auth()->id()] < 3)
                        <td class="p-1 p text-center text-sm text-gray-400">共著or利害
                        </td>
                    @else
                        <td class="p-1 p text-center" id="u{{ $rev->id }}_p{{ $p->id }}"
                            @isset($bids[$p->id][$rev->id])
                        data-bidding="{{ $bids[$p->id][$rev->id] }}"
                        @endisset>
                            @isset($bids[$p->id][$rev->id])
                                {!! $bids[$p->id][$rev->id] !!}
                            @endisset

                            @isset($stars[$p->id][$rev->id])
                                {!! $stars[$p->id][$rev->id] !!}
                            @endisset
                            {{-- {{ $rev->name }} --}}
                        </td>
                    @endif
                @endforeach

            </tr>
        @endforeach
    </tbody>
</table>
