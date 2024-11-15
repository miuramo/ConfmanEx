@props([
    'cat_id' => 1,
    'subs' => [],
    'scoreonly' => 1,
])
@php
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    // ユーザが担当しているrevがあれば、ハイライト
    $tantourev = App\Models\Review::where('user_id', auth()->id())->get()->pluck('paper_id', 'id')->toArray();
    // $colors = ['white', 'red', 'yellow', 'gray', 'lime', 'cyan', 'purple', 'gray', 'gray', 'gray']; // TODO: 色の割り当ては会議によって異なるので環境設定でやる。
    $jsoncolor = App\Models\Setting::findByIdOrName('SCOREMAP_COLORS');
    if ($jsoncolor != null && $jsoncolor->valid) {
        $scoremap_colors = json_decode($jsoncolor->value, true);
    } else {
        $scoremap_colors = [];
    }
@endphp
<!-- components.review.pccommentmap -->
<table class="min-w-full divide-y divide-gray-200 mb-2 sortable" id="sortable">
    <thead>
        <tr>
            <th class="p-1 bg-slate-300"> i</th>
            <th class="p-1 bg-slate-300"> id</th>
            <th class="p-1 bg-slate-300"> title</th>
            <th class="p-1 bg-slate-300"> accept</th>
            <th class="p-1 bg-slate-300"> score</th>
            <th class="p-1 bg-slate-300"> stddev</th>
            <th class="p-1 bg-slate-300"> num finish</th>
            <th class="p-1 bg-slate-300"> num assign</th>
            @php
                if ($scoreonly) {
                    $vps = App\Models\Viewpoint::where('category_id', $cat_id)
                        ->where('content', 'like', '%number%')
                        ->orderBy('orderint')
                        ->pluck('desc', 'id')
                        ->toArray();
                } else {
                    $vps = App\Models\Viewpoint::where('category_id', $cat_id)
                        ->orderBy('orderint')
                        ->pluck('desc', 'id')
                        ->toArray();
                }
                // Primary用か一般用か（scoreonlyによらず、配列[vp_id]にいれる）
                $vpsismeta[1] = App\Models\Viewpoint::where('category_id', $cat_id)
                    ->orderBy('orderint')
                    ->pluck('formeta', 'id')
                    ->toArray();
                $vpsismeta[0] = App\Models\Viewpoint::where('category_id', $cat_id)
                    ->orderBy('orderint')
                    ->pluck('forrev', 'id')
                    ->toArray();
                if (count($subs) > 0) {
                    $sub = $subs[0];
                }
                // タイトルにリンクをつけるかどうか
                $enableTitleLink = App\Models\Category::isShowReview($cat_id);
            @endphp
            @isset($sub)
                {{--  Reviewerの数にあわせて、繰り返す。 --}}
                @foreach ($sub->reviews as $rev)
                    <th class="p-1 bg-slate-300"> Rev {{ $loop->index + 1 }}</th>
                    @foreach ($vps as $id => $desc)
                        @if ($scoreonly == 1 && strpos($desc, 'コメント') > 0)
                            {{-- // TODO: コメントではなく、scoreonlyなvpかどうかで判断すべき。 --}}
                        @else
                            @if ($vpsismeta[$rev->ismeta][$id] == 1)
                                <th class="p-1 bg-slate-300">{{ $desc }}</th>
                            @endif
                        @endif
                    @endforeach
                @endforeach
            @endisset
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
                        <td class="text-center text-gray-400 bg-slate-100">
                            {{ $loop->index + 1 }}
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->paper->id_03d() }}
                        </td>
                        <td class="p-1 text-sm">
                            @if ($enableTitleLink && isset($rigais[$sub->paper->id][auth()->id()]) && $rigais[$sub->paper->id][auth()->id()] > 2)
                                <x-review.commentpaper_link :sub="$sub"></x-element.commentpaper_link>
                                @else
                                    <span class="text-gray-400">
                                        {{ $sub->paper->title }}
                                    </span>
                            @endif
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
                                    @if ($rev->status == 2)
                                <td class="bg-cyan-50 text-gray-200">
                                @else
                                <td class="bg-yellow-50 text-gray-200">
                            @endif
                            査{{ $rev->id }}
                    @endif
                    </td>
                    @foreach ($rev->scores_and_comments(0, $scoreonly) as $vpdesc => $valstr)
                        <td class="hover:bg-lime-50 transition-colors
                            @php
                            $colors = ['white'];
                            foreach($scoremap_colors as $key => $value){
                                if (preg_match('/'.$key.'/', $vpdesc)){
                                    $colors = $value;
                                    break;
                                }
                            }
                            @endphp
                            @if (is_numeric($valstr)) text-center @else text-xs @endif
                            @isset($colors[intval($valstr)])) 
                            bg-{{ $colors[intval($valstr)] }}-200
                            @endif
                        ">
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
