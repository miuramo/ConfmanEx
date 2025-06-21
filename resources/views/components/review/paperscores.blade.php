@props([
    'cat_id' => 1,
    'paper_id' => 999,
    'bb_id' => null,
    'size' => 'sm',
])
@php
    $ret = App\Models\Review::get_scores($paper_id, $cat_id);
    $scores = $ret['scores'];
    $ismeta = $ret['ismeta'];
    $names = $ret['names'];
    $descs = $ret['descs'];

    if ($bb_id !== null) {
        $bb = App\Models\Bb::find($bb_id);
        $ismeta_myself = $bb->ismeta_myself();
    }

    $nameofmeta = App\Models\Setting::getval('NAME_OF_META');

    $count_formeta = 0;
@endphp

<!-- components.review.myscores 自分が入力したスコア一覧 -->
<div class="p-1 bg-slate-300 rounded-lg inline-block">
    <table class="divide-y divide-gray-200 mb-1 text-{{ $size }}">
        <thead>
            <tr>
                <th class="p-1 bg-slate-300"> Reviewer</th>
                <th class="p-1 bg-slate-300" title="Note: PAPERSCORES__REVIEW_LINK_ENABLE"> RevID</th>
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
                        @if (isset($ismeta_myself) && $ismeta_myself)
                            {{ $name }}
                        @else
                            @if ($ismeta[$revid])
                                @if (isset($ismeta_myself))
                                    {{ $name }}
                                @else
                                    {{ $nameofmeta }}
                                @endif
                            @else
                                (hidden)
                            @endif
                        @endif
                    </td>
                    <td class="p-1 text-center">
                        {{-- 全体設定：RevIDから査読報告にリンクするならtrue --}}
                        @php
                            $review_link_enable = App\Models\Setting::getval('PAPERSCORES__REVIEW_LINK_ENABLE');
                        @endphp
                        @if(@$review_link_enable)
                        <x-review.pubshow_link :rev_id="$revid"></x-review.pubshow_link>
                        @else
                        {{ $revid }}
                        @endif
                    </td>
                    @isset($scores[$revid])
                    {{-- 表のヘッダの項目と対応させる --}}
                        @foreach ($descs as $vpid => $desc)
                            @isset($scores[$revid][$vpid])
                            <td class="p-1 text-center">
                                {{ $scores[$revid][$vpid] }}
                            </td>
                            @else
                            <td class="p-1 text-center bg-gray-300">
                            </td>
                            @endisset
                        @endforeach
                    @endisset
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
