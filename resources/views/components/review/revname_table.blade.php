@props([
    'cat_id' => 3,
])

@php
    $nameofmeta = App\Models\Setting::getval('NAME_OF_META');
    $uid2name = App\Models\User::select('name', 'affil', 'id')->get()->pluck('name', 'id')->toArray();
    $pid2title = App\Models\Paper::select('title', 'id')
        ->where('category_id', $cat_id)
        ->get()
        ->pluck('title', 'id')
        ->toArray();
    $rigais = App\Models\RevConflict::arr_pu_rigai($cat_id);
    $revnames = App\Models\Review::arr_pu_revname();
@endphp

<!-- components.review.revname_table  -->
<table class="divide-y divide-gray-200 mb-2">
    <thead>
        <tr>
            <th class="p-1">pid</th>
            <th class="p-1">title</th>
            @php
                // 配列pid2titleの最初の要素を取り出して、査読者の数を取得
                $firstpid = key($pid2title);
                $num_metarevs = count($revnames[$firstpid][1] ?? []);
                $num_revs = count($revnames[$firstpid][0] ?? []);
            @endphp
            @if ($num_metarevs > 1)
                @for ($i = 1; $i <= $num_metarevs; $i++)
                    <th class="p-1">{{ $nameofmeta }}{{ $i }}</th>
                @endfor
            @else
                @if ($num_metarevs == 1)
                    <th class="p-1">{{ $nameofmeta }}</th>
                @endif
            @endif
            @for ($i = 1; $i <= $num_revs; $i++)
                <th class="p-1">査読者{{ $i }}</th>
            @endfor
            <th class="p-1"></th>
            <th colspan=4 class="p-1 bg-red-200">利害表明者</th>
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($pid2title as $pid => $title)
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500 ' }}">
                <td class="p-1 text-center ">
                    {{ sprintf('%03d', $pid) }}
                </td>
                <td class="p-1 text-sm">
                    {{ $title }}
                </td>
                @if (isset($rigais[$pid][auth()->id()]) && $rigais[$pid][auth()->id()] < 3)
                    <td colspan=5 class="p-1 bg-yellow-100 text-center text-gray-400">
                        利害関係があるため非表示
                    @else
                        @isset($revnames[$pid][1])
                            @foreach ($revnames[$pid][1] as $revuid => $revname)
                        <td class="p-1">
                            {{ $revname }} ({{ $revuid }})
                        </td>
                    @endforeach
                @endisset
                @isset($revnames[$pid][0])
                    @foreach ($revnames[$pid][0] as $revuid => $revname)
                        <td class="p-1">
                            {{ $revname }} ({{ $revuid }})
                        </td>
                    @endforeach
                @endisset

                <td class="p-1 text-center text-red-500">
                    利 害 →
                </td>
                @isset($rigais[$pid])
                    @foreach ($rigais[$pid] as $rigaiuid => $rigaival)
                        @if ($rigaival < 3)
                            <td class="p-1 bg-red-200">
                                {{ $uid2name[$rigaiuid] }}
                            </td>
                        @endif
                    @endforeach
                @endisset
        @endif
        </tr>
        @endforeach
    </tbody>
</table>
