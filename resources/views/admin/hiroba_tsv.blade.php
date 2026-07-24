<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('情報学広場登録用TSV 閲覧用') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $year = App\Models\Setting::getval('CONFTITLE_YEAR');
        if ($year == null) {
            $year = date('Y');
        }
        $startpage = 1;
        $endpage = 0;
        $num_max_authors = \App\Models\Submit::max_author_count();

        $rows = $template;
        // 1行目は、本システムが書き出すための特殊なヘッダ
        $base_headers = array_shift($rows);

        // 2行目〜6行目は、情報学広場の仕様に従ったヘッダ
        $stock_headers = [];
        for ($i = 0; $i < 5; $i++) {
            $stock_headers[] = array_shift($rows);
        }
        // 7行目は、書き出し用のデータ
        $export_template = array_shift($rows);

        // 繰り返し構造を、配列に保存する
        $headers = [];
        $indices = []; // ヘッダに対応する、元の配列のインデックスを保存する
        $replace_person_num = [];

        $n = 0;
        $skipnum = 0;
        foreach ($base_headers as $col => $h) {
            if ($skipnum > 0) {
                // 繰り返し構造ですでに処理済みの部分をスキップする
                $skipnum--;
                $n++;
                continue;
            }
            if (preg_match('/^REP_(\d+)$/', $h, $matches)) {
                $num_repeat = $matches[1];
                for ($p = 0; $p < $num_max_authors; $p++) {
                    for ($i = 0; $i < $num_repeat; $i++) {
                        $headers[] = $h . '_' . $p;
                        $indices[] = $n + $i;
                        $replace_person_num[] = $p;
                    }
                }
                $skipnum = $num_repeat - 1;
            } else {
                $headers[] = $h;
                $indices[] = $n;
                $replace_person_num[] = null;
            }
            $n++;
        }
    @endphp
    <table class="table-auto border-collapse border border-slate-400 dark:border-slate-600 text-sm">
        {{-- <thead>
            <tr> 
                @foreach ($headers as $n => $h)
                    <th class="border border-slate-400 dark:border-slate-600 px-2 py-1 bg-slate-200 dark:bg-slate-700 whitespace-nowrap">
                        {{ $h }}</th>
                @endforeach 
                 @foreach ($indices as $n => $idx)
                    <td class="border border-slate-400 dark:border-slate-600 px-2 py-1">{{ $idx }}
                        {{ $replace_person_num[$n] ?? 'null' }}</td>
                @endforeach
            </tr> 
        </thead> --}}
        <tbody>
            {{-- <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-100 dark:bg-slate-800' : '' }}"> --}}
            @for ($i = 0; $i < count($stock_headers); $i++)
                <tr class="{{ $i % 2 === 0 ? 'bg-slate-100 dark:bg-slate-800' : '' }}">
                    @foreach ($indices as $n => $idx)
                        <td class="border border-slate-400 dark:border-slate-600 px-2 py-1">
                            @php
                                $val = $stock_headers[$i][$idx] ?? '';
                                if (isset($replace_person_num[$n]) && $replace_person_num[$n] !== null) {
                                    // $val = "[".$replace_person_num[$n]."] ".$val;
                                    $val = preg_replace('/\[0\]/', '[' . $replace_person_num[$n] . ']', $val, 1);
                                }
                            @endphp
                            {{ $val }}</td>
                    @endforeach
                </tr>
            @endfor

            @foreach ($submits as $sub)
                @php
                    $serial = intval($sub->serialnum);
                    $pdf_file = "IPSJ-SSS{$year}{$sub->serialnum}.pdf";
                    $authorlist = $sub->paper->authorlist_ary("authorlist",true);
                    $eauthorlist = $sub->paper->authorlist_ary("eauthorlist",true);
                    $keywords = $sub->paper->keyword;
                    // もし、和文が含まれていたら、カンマを全角に置換する。
                    if (preg_match('/[一-龠ぁ-ゔァ-ヴー々〆〤]/u', $keywords)) {
                        $keywords = str_replace(',', '，', $keywords);
                        $keywords = str_replace(' ', '', $keywords);
                    }

                    $affil = [];
                    $eaffil = [];
                    $name = [];
                    $ename = [];
                    foreach ($authorlist as $n => $u) {
                        $affil[$n] = str_replace('/','／', $authorlist[$n][1]);
                        $eaffil[$n] = $eauthorlist[$n][1];
                        $name[$n] = str_replace(' ', ', ', $authorlist[$n][0]);
                        $ename[$n] = ucwords(strtolower($eauthorlist[$n][0])); // ucwordsで、頭文字だけ大文字にする
                    }
                    $abst = $sub->paper->abst;
                    $eabst = $sub->paper->eabst;

                    $pages = @$pagenums[$sub->paper->pdf_file_id];
                    $endpage = $startpage + $pages - 1;
                    $end = $endpage;
                    $start = $startpage;
                    $startpage = $startpage + $pages;

                    // \Log::info("affil: " . implode(", ", $affil));
                    // \Log::info("eaffil: " . implode(", ", $eaffil));
                    // \Log::info("name: " . implode(", ", $name));
                    // \Log::info("ename: " . implode(", ", $ename));
                @endphp
                <tr>
                    @foreach ($indices as $pos => $idx)
                        @php
                            $val = $export_template[$idx];
                            if (preg_match('/{{(.*)}}/', $val, $matches)) {
                                if (isset($matches[1])) {
                                    $varname = trim($matches[1]);
                                    // \Log::info("varname: $varname");
                                    // もし、$varnameに[0]があったら、$replace_person_num[$pos]を使って置換する
                                    if (strpos($varname, '[0]') !== false && isset($replace_person_num[$pos]) && $replace_person_num[$pos] !== null) {
                                        $varname = str_replace('[0]', '[' . $replace_person_num[$pos] . ']', $varname);
                                    }
                                    $stmt = "\$val = {$varname} ?? '';"; 
                                    eval($stmt);
                                }
                            }
                        @endphp
                        <td class="border border-slate-400 dark:border-slate-600 px-2 py-1">
                            {{ $val ?? '' }}</td>
                    @endforeach
                </tr>
            @endforeach

        </tbody>
    </table>

</x-app-layout>
