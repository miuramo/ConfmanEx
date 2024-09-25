@props([
    'cat_id' => 1,
])
@php
    $ret = \App\Models\Review::my_scores(auth()->id(), $cat_id);
    $scores = $ret['scores'];
    $titles = $ret['titles'];
    $descs = $ret['descs'];
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
