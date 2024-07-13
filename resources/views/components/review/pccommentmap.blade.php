@props([
    'cat_id' => 1,
    'subs' => [],
    'scoreonly' => 1,
])
@php
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    $colors = ['white', 'red', 'yellow', 'gray', 'lime', 'cyan', 'purple', 'gray', 'gray', 'gray']; // TODO: 色の割り当ては会議によって異なるので環境設定でやる。
    // ユーザが担当しているrevがあれば、ハイライト
    $tantourev = App\Models\Review::where('user_id', auth()->id())->get()->pluck('paper_id', 'id')->toArray();
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
            {{-- TODO: Reviewerの数にあわせて、繰り返す。 --}}
            <th class="p-1 bg-slate-300"> Rev1</th>
            @php
                $vps = App\Models\Viewpoint::where('category_id', $cat_id)
                    ->orderBy('orderint')
                    ->pluck('desc', 'id')
                    ->toArray();
            @endphp
            @foreach ($vps as $id => $desc)
                @if ($scoreonly == 1 && strpos($desc, 'コメント') > 0)
                    {{-- // TODO: コメントではなく、scoreonlyなvpかどうかで判断すべき。 --}}
                @else
                    <th class="p-1 bg-slate-300">{{ $desc }}</th>
                @endif
            @endforeach
            {{-- TODO: Reviewerの数にあわせて、繰り返す。 --}}
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
                            @isset($tantourev[$rev->id])
                                <td class="bg-red-600 text-gray-200">
                                    <a href="{{ route('review.edit', ['review' => $rev]) }}"
                                        target="_blank">査{{ $rev->id }}</a>
                                @else
                                <td class="bg-slate-50 text-gray-200">
                                    査{{ $rev->id }}
                            @endif
                            </td>
                            @foreach ($rev->scores_and_comments(0, $scoreonly) as $vpdesc => $valstr)
                                <td
                                    class="hover:bg-lime-50 transition-colors
                                    @if (is_numeric($valstr) && isset($colors[intval($valstr)])) 
                                    bg-{{ $colors[intval($valstr)] }}-200 text-center 
                                    @endif
+                                    ">
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
