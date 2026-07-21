@props([
    'all' => [],
    'heads' => ['booth', 'count', 'title', 'authors'],
    'enqans' => [],
])

<!-- components.vote.votesumtable -->
@php
    $res = App\Models\VoteAnswer::vote_result();
    $votes = App\Models\Vote::where('valid', true)->select('name', 'id')->pluck('name', 'id')->toArray();

    $papers = App\Models\Paper::select('title', 'id')->pluck('title', 'id')->toArray();
    $subbooth2paperid = App\Models\Submit::select('paper_id', 'booth')->get()->pluck('paper_id', 'booth')->toArray();
    $authors = App\Models\Paper::select('authorlist', 'id')->pluck('authorlist', 'id')->toArray();

    $colors = [
        1 => 'purple',
        2 => 'lime',
        3 => 'orange',
        4 => 'red',
        5 => 'blue',
    ];
@endphp

@foreach ($votes as $vid => $vname)
    {{-- @foreach ([1 => '一般', 2 => '学生'] as $sid => $stu) --}}
    <div class="rounded-lg bg-{{ $colors[$vid] }}-200 py-2 px-3 my-2 text-lg">【{{ $vname }}】の集計結果
        <span class="mx-4"></span>

        @isset($res[$vid])
            <x-element.linkbutton size="sm" color="teal" href="{{ route('vote.download_answers', ['vote' => $vid]) }}"
                target="_blank">
                投票結果 Excel
            </x-element.linkbutton>

            <span class="mx-4"></span>
            <x-element.button class="" id="toggle_votesumtable{{ $vid }}" color="{{ $colors[$vid] }}"
                size="sm" onclick="openclose('votesumtable{{ $vid }}')" value="投票結果を表示／非表示">

            </x-element.button>
            <div class="hidden-content" id="votesumtable{{ $vid }}" style="display:none;">
                <table class="min-w divide-y divide-gray-200 sortable text-sm" id="votesumtable{{ $vid }}">
                    <thead>
                        <tr>
                            @foreach ($heads as $h)
                                <th class="p-1 bg-slate-300">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">

                        @foreach ($res[$vid] as $booth => $count)
                            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                                <td class="text-center">
                                    {{ $booth }}
                                </td>
                                <td class="text-center">
                                    {{ $count }}
                                </td>
                                <td>
                                    {{ $papers[$subbooth2paperid[$booth]] }}
                                </td>
                                <td>
                                    {{ str_replace("\n", '，', trim($authors[$subbooth2paperid[$booth]])) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <span class="mx-6 text-blue-400">まだ該当する投票がありません。</span>
        @endisset
        {{-- @endforeach --}}
    </div>
@endforeach
