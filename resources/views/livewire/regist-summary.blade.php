<div>
    現在の参加登録完了者: {{ $finishedCount }}名 （未完了者：{{ $notfinishedCount }}名）<br>

    @foreach ($items as $item)
        <table class="text-sm border-collapse border border-slate-400 mb-4">
            <tr class="bg-slate-300">
                <th class="px-2">Early</th>
                <th class="px-2">Late</th>
                <th class="px-2">{{ $item }}</th>
            </tr>
            @foreach ($summary[$item] as $name => $ary)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }} hover:bg-lime-50">
                    @foreach ([1, 0] as $isearly)
                        @isset($ary[$isearly])
                            <td class="px-1 text-right">{{ $ary[$isearly] }}</td>
                        @else
                            <td class="px-1 text-right">0</td>
                        @endisset
                    @endforeach
                    <td class="px-1">{{ $name }}</td>
                </tr>
            @endforeach
        </table>
    @endforeach
</div>
