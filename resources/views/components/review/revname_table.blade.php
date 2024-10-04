@props([
    'cat_id' => 3,
])

@php
    $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
    $uid2name = App\Models\User::select('name', 'affil', 'id')->get()->pluck('name', 'id')->toArray();
    $pid2title = App\Models\Paper::select('title', 'id')
        ->where('category_id', $cat_id)
        ->get()
        ->pluck('title', 'id')
        ->toArray();
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $revnames = App\Models\Review::arr_pu_revname();
@endphp

<!-- components.review.revname_table  -->
<table class="divide-y divide-gray-200 mb-2">
    <thead>
        <tr>
            <th class="p-1">pid</th>
            <th class="p-1">title</th>
            <th class="p-1">{{ $nameofmeta }}</th>
            <th class="p-1">査読者1</th>
            <th class="p-1">査読者2</th>
            <th class="p-1">査読者3</th>
            <th class="p-1"></th>
            <th colspan=4 class="p-1 bg-red-200">利害表明者</th>
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($pid2title as $pid => $title)            
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500 ' }}">
                <td class="p-1 text-center ">
                    {{ sprintf("%03d",$pid) }}
                </td>
                <td class="p-1 text-sm">
                    {{ $title }}
                </td>
                @if ($rigais[$pid][auth()->id()] < 3) 
                <td colspan=5 class="p-1 bg-yellow-100 text-center text-gray-400">
                    利害関係があるため非表示
                @else
            
                @isset($revnames[$pid][1])
                    @foreach ($revnames[$pid][1] as $revuid => $revname)
                        <td class="p-1">
                            {{ $revname }} ({{$revuid}})
                        </td>
                    @endforeach
                @endisset
                @isset($revnames[$pid][0])
                    @foreach ($revnames[$pid][0] as $revuid => $revname)
                        <td class="p-1">
                            {{ $revname }} ({{$revuid}})
                        </td>
                    @endforeach
                @endisset

                <td class="p-1 text-center text-red-500">
                     利 害 →
                </td>
                @isset($rigais[$pid])
                        @foreach ($rigais[$pid] as $rigaiuid=>$rigaival)
                            @if($rigaival < 3)
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
