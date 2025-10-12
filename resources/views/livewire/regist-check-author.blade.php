<div>
    <b class="text-emerald-600">発表者がまだ参加登録していない採択発表一覧</b>

    <table class="text-sm border-collapse border border-slate-400 mb-4">
        <tr class="bg-slate-300">
            <th class="px-2">Cat</th>
            <th class="px-2">PID</th>
            <th class="px-2">Title</th>
        </tr>
        @foreach ($papers_without_presenters as $pid)
            @php
                $paper = App\Models\Paper::find($pid);
            @endphp
            <tr
                class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }} hover:bg-yellow-50">
                <td class="px-1 text-center">{{ $paper->category_id }}</td>
                <td class="px-1 text-center">{{ $paper->id }}</td>
                <td class="px-1 ">{{ $paper->title }}</td>
            </tr>
        @endforeach
    </table>

</div>
