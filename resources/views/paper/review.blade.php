<x-app-layout>
    <!-- paper.review -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('査読結果') }}
        </h2>
    </x-slot>
    @section('title', '査読結果')
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="mypaperlist" class="grid grid-cols-1 gap-4">
            <div class="bg-slate-200 p-3">
                <x-element.paperid size=2 :paper_id="$paper->id">
                </x-element.paperid>
                &nbsp;
                &nbsp;
                <x-element.category :cat="$paper->category_id">
                </x-element.category>
                {{-- @if ($authorType == 1) --}}

                {{-- <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => substr($paper->pdf_file->key,0,8)]) }}"
                    title="{{ $paper->title }}" loading="lazy" class="w-1/2 mt-2"> --}}
            </div>
        </div>
    </div>

    @foreach ($subs as $sub)
        <div class="m-6">
            @php
                $count = 0;
            @endphp
            @foreach ($sub->reviews as $rev)
                <table class="table-auto">
                    @php
                        $count++;
                    @endphp
                    <thead>
                        <tr>
                            <th class="bg-slate-300 border-4 border-slate-300">
                                査読者 {{ $count }}
                            </th>
                            <th class="bg-slate-300 border-4 border-slate-300">
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rev->scores_and_comments() as $vpdesc => $valstr)
                            <tr
                                class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200' : 'bg-white-50' }}">
                                <td nowrap class="p-2">
                                    {{ $vpdesc }}
                                </td>
                                <td class="p-2">
                                    {!! nl2br(htmlspecialchars($valstr)) !!}
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
