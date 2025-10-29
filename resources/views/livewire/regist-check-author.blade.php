<div>
    <b class="text-emerald-600">発表者がまだ参加登録完了していない採択発表一覧</b> ({{ count($papers_without_presenters) }}件)
    @php
        $mikaishi = [];
        $mikanryo = [];
        $paper_mikaishi = [];
        $paper_mikanryo = [];
    @endphp
    <table class="text-sm border-collapse border border-slate-400 mb-4">
        <tr class="bg-slate-300">
            <th class="px-2">Cat</th>
            <th class="px-2">PID</th>
            <th class="px-2">Title</th>
            <th class="px-2">Owner status</th>
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
                <td class="px-1 ">
                    @isset($pending_name[$paper->owner])
                        <span class="text-blue-400">
                            {{ $pending_name[$paper->owner] }} ({{ $paper->owner }}) 未完了
                        </span>
                        @php
                            $mikanryo[] = $paper->owner;
                            $paper_mikanryo[] = $paper->id;
                        @endphp
                    @else
                        <span class="text-pink-400">
                            @php
                                $user = \App\Models\User::find($paper->owner);
                            @endphp
                            @if ($user)
                                {{ $user->name }} ({{ $paper->owner }}) 未開始
                                @php
                                    $mikaishi[] = $paper->owner;
                                    $paper_mikaishi[] = $paper->id;
                                @endphp
                            @else
                                (未登録ユーザ)
                            @endif
                        </span>
                    @endisset
                </td>
            </tr>
        @endforeach
    </table>
    <div class="mb-2 text-sm"> User IDs: 
        <b class="text-blue-400 text-sm">{{ implode(', ', array_unique($mikanryo)) }}</b> <span class="mx-2"></span>
        <b class="text-pink-400 text-sm">{{ implode(', ', array_unique($mikaishi)) }}</b>
    </div>
    <div class="mb-2 text-sm"> Paper IDs: 
        <b class="text-blue-400 text-sm">{{ implode(', ', array_unique($paper_mikanryo)) }}</b> <span class="mx-2"></span>
        <b class="text-pink-400 text-sm">{{ implode(', ', array_unique($paper_mikaishi)) }}</b>
    </div>
</div>