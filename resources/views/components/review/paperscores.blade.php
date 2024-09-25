@props([
    'cat_id' => 1,
    'paper_id' => 999,
    'bb_id' => 1,
])
@php
    $ret = App\Models\Review::get_scores($paper_id, $cat_id);
    $scores = $ret['scores'];
    $ismeta = $ret['ismeta'];
    $names = $ret['names'];
    $descs = $ret['descs'];

    $bb = App\Models\Bb::find($bb_id);
    $ismeta_myself = $bb->ismeta_myself();

    $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
@endphp

<!-- components.review.myscores 自分が入力したスコア一覧 -->
<div class="p-1 bg-slate-300 rounded-lg inline-block">
    <table class="divide-y divide-gray-200 mb-1 text-sm">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300"> Reviewer</th>
                <th class="p-1 bg-slate-300"> RevID</th>
                @foreach ($descs as $vp => $desc)
                    <th class="p-1 bg-slate-300">{{ $desc }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($names as $revid => $name)
                <tr
                    class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500' }}">
                    <td class="p-1 text-center">
                        @if ($ismeta_myself)
                            {{ $name }}
                        @else
                            @if ($ismeta[$revid])
                                {{ $name }}
                            @else
                                (hidden)
                            @endif
                        @endif
                    </td>
                    <td class="p-1 text-center">
                        <x-review.pubshow_link :rev_id="$revid"></x-review.pubshow_link>
                    </td>
                    @isset($scores[$revid])
                        @foreach ($scores[$revid] as $vp => $score)
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
