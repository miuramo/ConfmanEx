{{-- @props([
    'submits' => [],
    'heads' => [],
    'pagenums' = [],
]) --}}

<!-- components.paper.excel_hiroba -->
@php
    $year = App\Models\Setting::findByIdOrName("CONFTITLE_YEAR","value");
    $startpage = 1;
    $endpage = 0;
@endphp
<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($submits as $sub)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1">{{ $sub->category_id * 100 + $sub->orderint }}
                </td>
                <td class="p-1">{{ $sub->paper_id }}
                </td>
                <td class="p-1">Symposium
                </td>
                <td class="p-1">{{ $sub->paper->title }}
                </td>
                <td class="p-1">Ja
                </td>
                <td class="p-1">{{ $sub->paper->keyword }}
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">{{ $sub->paper->etitle }}
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">{{ $sub->paper->getAllAffils(1,"") }}
                </td>
                <td class="p-1">{{ $sub->paper->getAllAffils(1,"e") }}
                </td>
                <td class="p-1">{{ str_replace(" ",", ",$sub->paper->getAllAffils(0,"")) }}
                </td>
                <td class="p-1">{{ $sub->paper->getAllAffils(0,"e") }}
                </td>
                <td class="p-1">{{ $sub->paper->abst }}
                </td>
                <td class="p-1">{{ $sub->paper->eabst }}
                </td>
                <td class="p-1">
                </td>
                @php
                    if (!$sub->booth) $sub->booth = 0;
                    if (is_numeric($sub->booth)){
                        $booth = sprintf("%03d", $sub->booth);
                    } else {
                        $booth = $sub->booth;
+                   }
                @endphp
                <td class="p-1">IPSJ-SSS{{$year}}_{{ $booth }}.pdf
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">0
                </td>
                <td class="p-1">IPSJ:学会員,0|CE会員,0|CLE会員,0|DLIB:会員,0
                </td>
                <td class="p-1">Copyright (c) {{$year}} by the Information Processing Society of Japan
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">{{$year}}
                </td>
                <td class="p-1">
                </td>
                {{-- 開始ページ --}}
                <td class="p-1">
{{$startpage}}
                </td>
                {{-- 終了ページ --}}
                @php
                    $pages = @$pagenums[$sub->paper->pdf_file_id];
                    $endpage = $startpage + $pages - 1 ;
                    $startpage = $startpage + $pages;
                @endphp
                <td class="p-1">
{{$endpage}}
                </td>
                <td class="p-1">
                </td>
                <td class="p-1">
{{$pages}}
                </td>

            </tr>
        @endforeach
    </tbody>
</table>
