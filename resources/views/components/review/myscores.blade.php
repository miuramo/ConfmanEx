@props([
    'cat_id' => 1,
])
@php
    // review list
    $sql1 =
        'select reviews.id, paper_id, title from reviews left join papers on reviews.paper_id = papers.id where reviews.user_id = ' .
        auth()->id() .
        " and reviews.category_id = $cat_id order by paper_id";
    $res1 = DB::select($sql1);
    $titles = [];
    foreach ($res1 as $res) {
        $titles[$res->paper_id] = $res->title;
    }
    $sql2 =
        'select paper_id, viewpoint_id, value, orderint, `desc` from scores ' .
        ' left join reviews on scores.review_id = reviews.id' .
        ' left join viewpoints on scores.viewpoint_id = viewpoints.id' .
        ' where reviews.user_id = ' .
        auth()->id() .
        " and reviews.category_id = $cat_id " .
        ' and value is not null order by paper_id, orderint';
    $res2 = DB::select($sql2);
    $scores = [];
    $descs = [];
    foreach ($res2 as $res) {
        $scores[$res->paper_id][$res->viewpoint_id] = $res->value;
        $descs[$res->viewpoint_id] = $res->desc;
    }
@endphp

<!-- components.review.myscores 自分が入力したスコア一覧 -->
<x-element.h1>
    {{ __('自分が入力したスコア一覧（点数のみ。コメントは表示しません。）') }}
</x-element.h1>
<div class="px-2">
    <table class="min-w-full divide-y divide-gray-200 mb-2">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300"> PaperID</th>
                <th class="p-1 bg-slate-300"> Title</th>
                @foreach ($descs as $vp => $desc)
                    <th class="p-1 bg-slate-300">{{ $desc }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($titles as $pid => $title)
                <tr
                    class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500' }}">
                    <td class="p-1 text-center">
                        {{ sprintf('%03d', $pid) }}
                    </td>
                    <td class="p-1">
                        {{ $title }}
                    </td>
                    @isset($scores[$pid])
                        @foreach ($scores[$pid] as $vp => $score)
                            <td class="p-1 text-center">
                                {{ $score }}
                            </td>
                        @endforeach
                    @endisset
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
{{-- @foreach ($titles as $pid => $title)
    {{ $pid }} {{ $title }} <br>
    @foreach ($scores[$pid] as $vp => $score)
        {{ $descs[$vp] }} {{ $score }} <br>
    @endforeach
@endforeach --}}
