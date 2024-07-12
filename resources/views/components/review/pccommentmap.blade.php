@props([
    'cat_id' => 1,
    'subs' => [],
])
@php
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
@endphp
<!-- components.review.pccommentmap -->
<table class="min-w-full divide-y divide-gray-200 mb-2">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300"> id</th>
            <th class="p-1 bg-slate-300"> title</th>
            <th class="p-1 bg-slate-300"> accept</th>
            <th class="p-1 bg-slate-300"> score</th>
            <th class="p-1 bg-slate-300"> stddev</th>
            <th class="p-1 bg-slate-300"> num finish</th>
            <th class="p-1 bg-slate-300"> num assign</th>
            <th class="p-1 bg-slate-300"> Rev1</th>
            @php
                $vps = App\Models\Viewpoint::where('category_id', $cat_id)
                    ->orderBy('orderint')
                    ->pluck('desc', 'id')
                    ->toArray();
            @endphp
            @foreach ($vps as $id => $desc)
                <th class="p-1 bg-slate-300">{{ $desc }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @php
            $count = 1;
        @endphp

        @foreach ($subs as $sub)
            @isset($sub->paper)
                @isset($sub->paper->pdf_file)
                    <tr class="{{ $count % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                        <td class="p-1 text-center">
                            {{ $sub->paper->id_03d() }}
                        </td>
                        <td class="p-1">
                            {{ $sub->paper->title }}
                        </td>
                        <td class="p-1 text-center">
                            {{ $accepts[$sub->accept_id] }}
                        </td>
                        <td class="p-1 text-center">
                            @if ($sub->score)
                                {{ sprintf('%4.2f', $sub->score) }}
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            @if ($sub->stddevscore)
                                {{ sprintf('%4.2f', $sub->stddevscore) }}
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->reviews->where('status', 2)->count() }}
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->reviews->count() }}
                        </td>

                        {{--  ここから、各査読者のコメント --}}
                        @foreach ($sub->reviews as $rev)
                            <td class="bg-orange-200">
                                査{{ $rev->id }}
                            </td>
                            @foreach ($rev->scores_and_comments(0) as $vpdesc => $valstr)
                                <td class="hover:bg-lime-50 transition-colors">
                                    {!! nl2br(htmlspecialchars($valstr)) !!}
                                </td>
                            @endforeach
                        @endforeach
                    </tr>
                @endisset
            @endisset
        @endforeach
    </tbody>

</table>
