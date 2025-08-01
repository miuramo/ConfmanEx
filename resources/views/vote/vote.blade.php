<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('vote.index') }}" color="green" size="sm">
                &larr; 投票Topに戻る
            </x-element.linkbutton>
            @php
                $votes = App\Models\Vote::where('isopen', true)->where('isclose', false)->get();
            @endphp
            <span class="mx-4"></span>
            @foreach ($votes as $vvv)
                @if ($vote->for_pc && !auth()->user()->is_pc_member())
                    @continue
                @endif
                @php
                    $col = $vvv->for_pc ? 'orange' : 'lime';
                @endphp
                @if ($vvv->isopen && !$vvv->isclose)
                    <x-element.linkbutton href="{{ route('vote.vote', ['vote' => $vvv->id]) }}" color="{{$col}}"
                        size="md">
                        {{ $vvv->name }}に対する投票
                    </x-element.linkbutton>
                @endif
                <span class="mx-2"></span>
            @endforeach

        </div>

        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ $vote->name }}に対する投票

        </h2>
    </x-slot>
    <style>
        .hover-trigger:hover+.tooltip {
            display: block;
        }
    </style>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6">
        <x-element.h1>
            投票先を修正したときは、画面下の <span class="bg-purple-200 p-1 text-purple-700"> チェックを入れた発表に投票</span> ボタンをおしてください。
            <br>期限までは何度でも修正できます。
            <br>

            投票後に <x-element.linkbutton2 href="{{ route('vote.vote', ['vote' => $vote]) }}" color="cyan">再読み込み
            </x-element.linkbutton2>
            をすると、ただしく投票できているか、確認できます。
        </x-element.h1>
    </div>
    @php
        $voteitems = App\Models\VoteItem::where('vote_id', $vote->id)->orderBy('orderint')->get();
        $papers = App\Models\Paper::select('title', 'id')->pluck('title', 'id')->toArray();
        $authors = App\Models\Paper::select('authorlist', 'id')->pluck('authorlist', 'id')->toArray();
    @endphp

    <form action="{{ route('vote.vote', ['vote' => $vote]) }}" method="post" id="votevote">
        @csrf
        @method('post')

        @auth
            <input type="hidden" name="user_id" value="{{ $uid }}">
            <input type="hidden" name="comment" value="{{ auth()->user()->name }} {{ auth()->user()->affil }}">
        @else
            <input type="hidden" name="token" value="{{ $ticket->token }}">
            <input type="hidden" name="comment" value="{{ $ticket->email }}">
        @endauth

        <div class="py-4 px-6  dark:text-gray-400">
            @foreach ($voteitems as $vi)
                <x-element.h1>
                    {{ $vi->name }} {{ $vi->desc }} を<b>
                        @if ($vi->upperlimit > 0)
                            {{ $vi->upperlimit }}件以内で
                        @else
                            すべて
                        @endif
                    </b> 選択してください。
                </x-element.h1>
                <div class="mx-4">

                    @foreach (json_decode($vi->submits) as $booth => $pid)
                        <div class="mx-1 my-1">
                            <input type="checkbox" class="cursor-pointer mt-0 mb-1" id="{{ $booth }}"
                                name="{{ $booth }}" {{ isset($vas[$booth]) ? 'checked' : '' }} />
                            <label for="{{ $booth }}"
                                class="hover:bg-yellow-100 hover:border-2 hover:border-yellow-300 hover:p-1 hover-trigger hover:font-bold cursor-pointer p-0.5 transition-all duration-150">
                                {{ $booth }} : {{ $papers[$pid] }}</label>
                            <div
                                class="absolute hidden border-2 border-lime-300 p-1 ml-64 mt-4 text-black bg-lime-100 bg-opacity-85 tooltip text-sm transition-all duration-150">
                                {{ str_replace("\n", ' ', trim($authors[$pid])) }}
                            </div>
                            @if($vi->show_pdf_link)
                                @php
                                    $paper = App\Models\Paper::where('id', $pid)->first();
                                @endphp
                                <span class="ml-2">
                                    <x-file.link_anyfile :fileid="$paper->pdf_file_id" label="PDF" linktype='link' />
                                    {{-- <x-file.link_pdffile :fileid="$paper->pdf_file_id"></x-file.link_pdffile> --}}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if (count($voteitems) > 0)
                <div class="mx-4 mt-8">
                    <x-element.submitbutton2 color="purple" size="3xl" value="9999">
                        チェックを入れた発表に投票
                    </x-element.submitbutton2>
                </div>
            @else
                投票は準備中、または終了済みです。
            @endif
        </div>
    </form>

    <div class="mx-6 mt-4 mb-12">
        <div class="mx-4">
            <x-element.linkbutton href="{{ route('vote.index') }}" color="green" size="sm">
                &larr; 投票一覧に戻る
            </x-element.linkbutton>

            <span class="mx-4"></span>
            @foreach ($votes as $vvv)
                @if ($vvv->isopen && !$vvv->isclose)
                    <x-element.linkbutton href="{{ route('vote.vote', ['vote' => $vvv->id]) }}" color="lime"
                        size="md">
                        {{ $vvv->name }}
                    </x-element.linkbutton>
                @endif
                <span class="mx-2"></span>
            @endforeach

        </div>
    </div>

</x-app-layout>
