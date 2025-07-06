<x-app-layout>
    <!-- paper.show -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿情報の確認') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @foreach ($fileerrors as $er)
        <x-alert.error>{{ $er }}</x-alert.error>
    @endforeach

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="mypaperlist" class="grid grid-cols-1 gap-4">

            @if ($paper->accepted)
                <div class="bg-cyan-100 p-3  dark:bg-cyan-300"> <span
                        class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
                @else
                    <div class="bg-slate-200 p-3">
            @endif
            <x-element.paperid size=2 :paper_id="$paper->id">
            </x-element.paperid>
            &nbsp;
            &nbsp;
            <x-element.category :cat="$paper->category_id">
            </x-element.category>
            {{-- @if ($authorType == 1) --}}
            @can('edit_paper', ['paper' => $paper])
                &nbsp;
                &nbsp;
                <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="blue">
                    Edit </x-element.linkbutton>
            @endcan

            @isset($paper->pdf_file)
                <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => substr($paper->pdf_file->key, 0, 8)]) }}"
                    title="{{ $paper->title }}" loading="lazy"
                    class="w-full mt-2 rounded-lg dark:bg-slate-800 dark:text-slate-400 shadow-lg">
            @else
                <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => 'nofile']) }}"
                    title="{{ $paper->title }}" loading="lazy"
                    class="w-full mt-2 rounded-lg dark:bg-slate-800 dark:text-slate-400 shadow-lg">
            @endisset
        </div>
    </div>
    </div>

    <div class="m-6">
        @foreach ($enqs['canedit'] as $enq)
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                {{ $enq->name }}
                @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageview', ['paper' => $paper, 'enq' => $enq]) }}" color="lime">
                        ここをクリックして回答参照
                    </x-element.linkbutton>
                @endif
            </div>
            @if ($enq->showonpaperindex)
                <div class="mx-10">
                    <x-enquete.view :enq="$enq" :enqans="$enqans">
                    </x-enquete.view>
                </div>
            @endif
        @endforeach
        @foreach ($enqs['readonly'] as $enq)
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                {{ $enq->name }}
                @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageview', ['paper' => $paper, 'enq' => $enq]) }}" color="lime">
                        ここをクリックして回答参照
                    </x-element.linkbutton>
                @endif
            </div>
            @if ($enq->showonpaperindex)
                <div class="mx-10">
                    <x-enquete.view :enq="$enq" :enqans="$enqans">
                    </x-enquete.view>
                </div>
            @endif
        @endforeach
    </div>

    <div class="m-6">
        <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
            投稿連絡用メールアドレス
        </div>
        <div class="text-md mx-6 mt-3  dark:text-gray-400">
            {{ str_replace("\n", ' / ', trim($paper->contactemails)) }}
        </div>
    </div>

    <div class="m-6">
        <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
            ファイル
        </div>
        <div id="filelist"
            class="grid xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <x-file.on_paper :all="$paper->files_with_deleted" imgsize=300 size="sm"/>
        </div>
    </div>

    <div class="m-6">
        @php
            $koumoku = \App\Models\Paper::mandatory_bibs($paper->category_id); //必須書誌情報
        @endphp
        <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
            書誌情報
            {{-- <x-element.gendospan>採択後に入力</x-element.gendospan> --}}
        </div>
        <div class="text-md mx-6 mt-3">
            <table class="border-cyan-500 border-2">
                @foreach ($koumoku as $k => $v)
                    <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
                        <td class="px-2 py-1">{{ $v }}</td>
                        <td class="px-2 py-1" id="confirm_{{ $k }}">{!! nl2br($paper->{$k}) !!}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    {{-- <div class="m-6">
        <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
            著者・所属
            {{-- <x-element.gendospan>採択後に入力</x-element.gendospan> 
        </div>
        <div class="text-md mx-6 mt-3 grid grid-cols-2 dark:text-gray-400">
            @foreach (['authorlist' => '和文', 'eauthorlist' => '英文'] as $f => $desc)
                <div>
                    @isset($paper->{$f})
                        {!! nl2br($paper->{$f}) !!}
                    @else
                        ({{ $desc }}著者名 未入力)
                    @endisset
                </div>
            @endforeach
        </div>
    </div> --}}

    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
            &larr; 投稿一覧に戻る
        </x-element.linkbutton>
    </div>

</x-app-layout>
