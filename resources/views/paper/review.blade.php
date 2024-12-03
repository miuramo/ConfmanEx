<x-app-layout>
    <!-- paper.review -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('査読結果') }}
            <span class="mx-2"></span>
            <x-element.paperid size=2 :paper_id="$paper->id">
            </x-element.paperid>
        </h2>
    </x-slot>
    @section('title', '査読結果')
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="pt-2 px-6">
        <div class="bg-slate-200 p-3 dark:bg-slate-600 dark:text-gray-300">
            @foreach ($subs as $sub)
                @if (!$loop->first)
                    <span class="mx-8 text-2xl font-bold text-gray-400"></span>
                @endif
                <x-element.category :cat="$sub->category_id" size="2xl">
                </x-element.category>
                <span class="mx-1"></span>
                <span class="text-2xl font-bold">{{ $accepts[$sub->accept_id] }}</span>
            @endforeach
        </div>
    </div>

    @foreach ($subs as $sub)
        <div class="mx-6 my-2">
            @php
                $count = 0;
                $accept = App\Models\Accept::find($sub->accept_id);
                $isaccepted = $accept->judge > 0; // 不採択の場合、返さない項目があるので、ここで調べておく
                $vpsubdescs = App\Models\Viewpoint::where('category_id', $sub->category_id)
                    ->select('subdesc', 'desc')
                    ->get()
                    ->pluck('subdesc', 'desc')
                    ->toArray();
                $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
                if ($nameofmeta == null) {
                    $nameofmeta = 'メタ';
                }
            @endphp
            @foreach ($sub->reviews as $rev)
                <table class="table-auto">
                    @php
                        $count++;
                    @endphp
                    <thead>
                        <tr>
                            <th class="bg-slate-300 border-4 border-slate-300 text-left pl-10" colspan="2">
                                査読者 {{ $count }} &nbsp; 【査{{ $rev->id }}】

                                @if ($rev->ismeta)
                                    <span class="mx-2 text-blue-500">（{{$nameofmeta}}査読者） </span>
                                @endif
                            </th>
                            {{-- <th class="bg-slate-300 border-4 border-slate-300">
                            </th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rev->scores_and_comments(1, 0, $isaccepted) as $vpdesc => $valstr)
                            <tr
                                class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200 dark:bg-neutral-200' : 'bg-white-50 dark:bg-neutral-300' }}">
                                <td nowrap class="p-2 bg-slate-100 border-2 border-slate-300">
                                    {{ $vpdesc }}
                                </td>
                                <td class="p-2 hover:bg-lime-50 transition-colors text-left">
                                    @if ($valstr == '(未入力)')
                                        （とくにお伝えする事項は、ありません）
                                    @else
                                        {!! nl2br(htmlspecialchars($valstr)) !!}
                                    @endif
                                    {{-- vpsubdesc スコアの意味などを表示する --}}
                                    @isset($vpsubdescs[$vpdesc])
                                        <span class="mx-6"></span>
                                        <span class="text-gray-400 text-sm">{{ $vpsubdescs[$vpdesc] }}</span>
                                    @endisset
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        </div>
    @endforeach


    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
            &larr; 投稿一覧に戻る
        </x-element.linkbutton>
    </div>

</x-app-layout>
