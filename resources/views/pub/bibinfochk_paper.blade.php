@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>
    <!-- pub.bibinfochk -->
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <x-slot name="header">
    </x-slot>

    <div class="mx-6 px-4 pt-4 leading-relaxed">
    </div>

    @php
        $koumoku = [
            'title' => '和文タイトル',
            'authorlist' => '和文著者・所属',
            'abst' => '和文アブストラクト',
            'keyword' => '和文キーワード',
            'etitle' => '英文Title',
            'eauthorlist' => '英文著者・所属',
            'eabst' => '英文Abstract',
            'ekeyword' => '英文Keyword',
        ];
        $dtype = [
            'title' => 'varchar',
            'authorlist' => 'mediumtext',
            'abst' => 'mediumtext',
            'keyword' => 'varchar',
            'etitle' => 'varchar',
            'eauthorlist' => 'mediumtext',
            'eabst' => 'mediumtext',
            'ekeyword' => 'varchar',
        ];

    @endphp

    <div class="px-4 py-4">
        @isset($sub)
            <table class="border-lime-400 border-2">
                <tr class="bg-lime-200">
                    <td class="px-2 py-1">
                        @isset($sub->booth)
                        Booth {{ $sub->booth }}
                        @else
                        {{ $sub->paper->id_03d() }}
                        @endisset
                    </td>
                    <td class="px-2 py-1">
                        <x-element.button id="toggleButton" value="HEADIMG Open/Close" color='yellow'
                            onclick="openclose('headimg{{ $sub->id }}')">
                        </x-element.button>

                        @isset($sub->paper->pdf_file_id)
                            <x-file.link_pdfthumb :fileid="$sub->paper->pdf_file_id" page=1></x-file.link_pdfthumb>
                            <x-file.link_pdffile :fileid="$sub->paper->pdf_file_id"></x-file.link_pdffile>
                        @endisset
                        @isset($sub->paper->pdf_file)
                            {{ $sub->paper->pdf_file->pagenum }} pages
                        @endisset
                    </td>
                </tr>
            </table>
            <div class="hidden-content w-3/4 border border-gray-400 p-1" id="headimg{{ $sub->id }}"
                style="display:none;">
                @isset($sub->paper)
                    <x-file.paperheadimg :paper="$sub->paper"></x-file.paperheadimg>
                @endisset
            </div>
            <table class="border-cyan-400 border-2 mb-1">
                <tr class="bg-white dark:bg-cyan-100">
                    <td class="px-2 py-1">
                        PaperID
                    </td>
                    <td class="px-2 py-1">
                        @isset($sub->paper)
                            {{ $sub->paper->id_03d() }}
                        @endisset
                    </td>
                </tr>
                @foreach ($koumoku as $k => $v)
                    <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
                        <td class="px-2 py-1">
                            @isset($sub->paper)
                                @if (isset($sub->paper->maydirty[$k]) && $sub->paper->maydirty[$k] == 'true')
                                    <span class="bg-purple-300">{{ $v }}</span>
                                    <button onclick="reset_maydirty('{{ $sub->paper->id }}', '{{ $k }}')"
                                        class="bg-red-300 hover:bg-red-500 text-white px-2 py-1">確認済みにする</button>
                                @else
                                    {{ $v }}
                                @endif
                            @endisset
                        </td>
                        @isset($sub->paper)
                            @if (isset($sub->paper->maydirty[$k]) && $sub->paper->maydirty[$k] == 'true')
                                <td class="px-2 py-1 bg-purple-300 hover:bg-lime-100
@else
<td class="px-2 py-1
                                    hover:bg-lime-100 @endif
                                    font-monaca clicktoedit"
                                    id="{{ $k }}__{{ $sub->paper->id }}__{{ $dtype[$k] }}"
                                    data-orig="{{ $sub->paper->{$k} }}">
                                    {!! nl2br($sub->paper->{$k}) !!}</td>
                            @endisset
                    </tr>
                @endforeach
            </table>

            {{-- それぞれ表示する。背景色は黄色か紫か。修正するときはインライン編集、または別画面で。なるべく小さい単位で。 --}}
            {{-- {{ $sub->paper->maydirty }} --}}

            {{-- {{ $sub->paper->pdf_file_id }} --}}
        @endisset

        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>

    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
        <script src="/js/crud_table.js"></script>
        <script src="/js/crud_where.js"></script>
        <script src="/js/reset_maydirty.js"></script>
    @endpush
    <form action="{{ route('admin.crudpost') }}" method="post" id="admincrudpost">
        @csrf
        @method('post')
    </form>

    <form action="{{ route('pub.update_maydirty') }}" method="post" id="update_maydirty">
        @csrf
        @method('post')
    </form>

    <script>
        var table = "papers";
        var origData = {};
        var mode_br = true; // 改行反映する
        var sizecols = 90; // 横幅
    </script>

</x-app-layout>
