<!-- file.index -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('概要説明スライド (altpdf) 提出状況') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    @php
        $count = ['1' => 0, '2' => 0, '3' => 0];
    @endphp
    <div class="py-4 px-6">
        <table class="table-auto sortable" id="enq_file_status">
            <thead>
                <tr>
                    <th class="px-4 py-2 bg-slate-400">Booth</th>
                    <th class="px-4 py-2 bg-slate-400">FileID</th>
                    <th class="px-4 py-2 bg-slate-400">アンケ回答</th>
                    <th class="px-4 py-2 bg-slate-400">PaperID</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($accAcceptedSubs as $booth => $pid)
                    <tr>
                        <td class="border px-4 text-center">{{ $booth }}</td>
                        <td class="border px-4 py-1 text-center">
                            @isset($accPIDs[$booth])
                                <x-file.link_anyfile :fileid="$altpdf_fileids[$pid]" :label="$altpdf_fileids[$pid]" linktype="button" />
                            @endisset
                        </td>
                        @if (isset($enqanswers_pid[$pid]) && $enqanswers_pid[$pid] == $enqans_yes)
                            <td class="border px-4 text-center">
                            @else
                            <td class="border px-4 text-gray-300 text-center">
                        @endif
                        {{ @$enqanswers_pid[$pid] }}</td>
                        <td class="border px-4 text-center">{{ sprintf('%03d', $pid) }}</td>
                    </tr>
                    @php
                        if (isset($accPIDs[$booth])) {
                            $count[substr($booth, 0, 1)]++;
                        }
                    @endphp
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-4 px-6">
        <table class="table-auto">
            <thead>
                <tr>
                    <th class="px-4 py-2 bg-slate-400">Day</th>
                    <th class="px-4 py-2 bg-slate-400">ファイル件数</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($count as $booth => $cnt)
                    <tr>
                        <td class="border px-4 text-center">{{ $booth }}</td>
                        <td class="border px-4 text-center">{{ $cnt }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush


</x-app-layout>
