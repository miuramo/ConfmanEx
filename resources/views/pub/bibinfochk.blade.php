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
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('書誌情報の確認と修正') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>
        </h2>
        <style>
            .hidden-content {
                /* display: none; */
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>

    <div class="mx-6 px-4 pt-4 leading-relaxed">
        <span class="p-1 font-bold">出版担当のかたへ：</span> 論文自体のチェックに加えて、お手数ですが、投稿者が入力した書誌情報の確認と修正をお願いします。<br>
        緑色の背景のところにあるボタンを押すと、それぞれ切り取り画像、1ページ目の画像、PDFをひらきます。<br>
        その下の表は、投稿者が入力した書誌情報です。背景色が変わる要素については、クリックすると編集できます。<br>
        <b>一行テキストは Enter、複数行テキストは CTRL+Enter で保存してください。Escapeでキャンセルできます。</b><br><br>
        
        <span class="p-1 bg-purple-300">背景が紫</span>
        の要素は、書誌情報入力画面の「直接入力モード」で入力された項目ですので、要チェックです。PDFとの齟齬があるかもしれません。<br>
        「確認済みにする」をクリックすると、要チェックのフラグを消すことができます。<br>
        <br>
        背景が通常（紫でない）については、基本的にはPDFから抽出したテキストになります。<br>
        不要な空白、句読点の種類の不統一が気になる場合は、クリックして【テキスト一括処理】を実行してください（複数行テキストのみ適用可能）。その後、CTRL+Enter で保存してください。<br>
        <b>（投稿者の書誌情報入力画面には、「半角スペースをすべて削除」の機能があります。投稿者がそれを英文箇所に使った場合、本来残したほうがよい半角スペースが消えている場合があります。）</b><br>

        著者名（所属）のカッコは、全角・半角どちらでも大丈夫です。また、著者名と所属の間のスペースの有無も問いません。<br>
        複数所属は、半角スラッシュ / で区切ってください。<br>
        出力例を確認しながら、修正されることをおすすめします。
        <x-element.linkbutton href="{{ route('pub.bibinfo', ['cat' => $catid, 'abbr'=>'true']) }}" target="_blank" color="cyan" size="sm">
            出力例（所属をまとめる）
        </x-element.linkbutton>
        <x-element.linkbutton href="{{ route('pub.bibinfo', ['cat' => $catid]) }}" target="_blank" color="teal" size="sm">
            出力例（所属をまとめない）
        </x-element.linkbutton>
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

        if (count($subs) == 0) {
            $subs = $subs2;
            $memo = "採択済み論文はまだありません。かわりに、すべての投稿論文を表示します。";
        }
    @endphp
    @isset($memo)
        <div class="m-2 px-4 py-2 bg-red-500 text-white text-2xl">{{ $memo }}
        </div>
        @endisset

    <div class="px-4 py-4">
        @foreach ($subs as $sub)
            @php
                if($sub->paper == null) {
                    continue;
                }
            @endphp
            <table class="border-lime-400 border-2">
                <tr class="bg-lime-200">
                    <td class="px-2 py-1">
                        Booth {{ $sub->booth }}
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
<td class="px-2 py-1 hover:bg-lime-100
                                    @endif
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
        @endforeach

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

    {{-- @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="https://d3js.org/d3.v5.min.js"></script>
        <script src="/js/d3groupedit.js"></script>
        <script src="/js/d3contextmenu.js"></script>
        <script src="/js/d3booth.js"></script>
    @endpush --}}
    {{-- <script>
        var subpapers = {!! json_encode($subs) !!};
    </script> --}}

</x-app-layout>
