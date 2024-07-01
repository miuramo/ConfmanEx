<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('書誌情報の設定（PDFにふくまれるテキストを利用）') }}
        </h2>
    </x-slot>
    <!-- paper.dragontext -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="m-4">
        <x-element.h1>
            はじめての方は、
            <x-element.button id="toggleButton" value="操作説明を表示" color='gray' size='md' onclick="openclose('usage')">
            </x-element.button>
            を押してください。（もう一度押すと、説明を閉じます。）
            <span class="mx-2"></span>
            <x-element.button id="toggleButton" value="和文著者名の設定方法を表示" color='gray' size='md'
                onclick="openclose('ex_authorlist')">
            </x-element.button>

            <div class="hidden-content bg-slate-100 p-2 mt-2 dark:text-gray-600" id="usage" style="display:none;">
                <div class="py-0 px-3 text-sm leading-relaxed   dark:text-gray-400">
                    PDF記載との不一致を防ぐため、PDFにふくまれるテキストを最大限利用して、書誌情報（
                    {{ implode('、', array_values($koumoku)) }}
                    ）を設定していただきます。
                    <div class="my-2"></div>
                    ここでは、「和文タイトル」を例にして、設定方法を説明します。<br>最初に、画面下部の<span
                        class="border border-gray-600 bg-cyan-50 p-0.5">PDFから抽出したテキスト</span>
                    の和文タイトル部分を、マウスドラッグによって選択してください。<br>
                    選択したテキストが下の <span class="border border-gray-600 bg-yellow-100 p-0.5">エディタ</span>
                    にコピーされます。必要があれば修正してください。（例：和文中の不要な空白を除去）
                    <div class="my-2 mx-4 bg-pink-50 px-4 py-1">
                        <b>半角スペース以外の文字の追加や修正はできません。ただし、PDFテキストのコピーのみで正しく設定できない場合は</b>
                        <x-element.button onclick="maydirty_mode(true);" value="直接入力モードに切替" size="sm"
                            color="purple" >
                            {{-- color="purple" confirm="本当に直接入力モードにしますか？必要がなければキャンセルを押してください。"> --}}
                        </x-element.button>
                        {{-- <span class="border border-gray-600 bg-purple-200 p-0.5">直接入力モードに切替</span> --}}
                        <b>を押して編集してください。直接入力モードの使用は必要最小限でお願いします。和文著者名・所属を入力する際は改行が必要となるため、</b>
                        <span class="bg-purple-200 p-0.5">直接入力モード</span> <b>を使用してください。</b><br>
                        <span class="bg-purple-200 p-0.5">直接入力モード</span> から
                        <span class="bg-yellow-100 p-0.5">通常モード</span> に戻すには、<span
                            class="border border-gray-600 bg-cyan-50 p-0.5">PDFから抽出したテキスト</span> を再度ドラッグで選択してください。
                    </div>

                    修正がおわったら、エディタ下の「和文タイトルに設定」ボタンを押すと、エディタ内のテキストを和文タイトルとして設定します。

                    この手順を繰り返し、和文アブストラクト、和文著者名についても、設定してください。
                    <div class="my-2"></div>

                    <a href="#confirm_shoshi"
                        class="border border-cyan-500 bg-cyan-500 text-white p-0.5">設定確認画面</a>（本ページの下部）に表示されていれば、設定ができています。
                </div>
                <div class="px-10 py-2 text-red-800 text-sm">
                    出版にあたり、シンポジウムの予稿集・出版担当が書誌情報の体裁統一（不要な空白の削除や句読点の修正）を行う場合があります。</div>


                <div class="px-10 text-gray-500 text-sm">全角記号に囲みをつけて強調するため、アシアル情報教育研究所が開発した<a
                        class=" hover:text-blue-500" target="_blank"
                        href="https://anko.education/monacakomi">「もなかこみフォント」</a>を使用しています。</div>
            </div>

            <div class="hidden-content bg-slate-100 p-2 mt-2 dark:text-gray-600" id="ex_authorlist"
                style="display:none;">
                <div class="text-sm px-2  dark:text-gray-400">和文著者名の設定方法：一名につき、一行ずつ記入してください。氏名のあいだには半角スペースをいれてください。
                    氏名のあとに、所属を半角 ( ) または全角（ ）で囲って記載してください。複数の所属がある場合は半角スラッシュ / で区切ってください。<br>
                    とくに外国人の氏名については、論文PDFでの表記（カナ/英文）とおなじであることを確認してください。共著者のかたも、投稿一覧からご確認いただけます。</div>
                <div class="mt-2 text-sm px-2  dark:text-gray-400">和文著者名の設定例：</div>
                <textarea id="jpex" name="jpexample" rows="3"
                    class="inline-flex mb-1 block p-2.5 w-full text-md text-gray-900 bg-gray-200 rounded-lg border border-gray-300
                     focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                      dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                    placeholder="投稿 太郎 (投稿大学)&#10;和布蕪 二郎 (和布蕪大学)&#10;昆布 巻子 (ダシ大学/昆布研究所)" readonly></textarea>

                <div class="px-10 text-red-800 text-sm">
                    シンポジウムの予稿集・出版担当が所属表記の短縮や修正を行う場合があります。ご了承ください。</div>

            </div>

        </x-element.h1>



        <textarea class="p-2 w-full text-xl bg-yellow-100 font-monaca" id="seltext" rows=6
            placeholder="（ここは直接入力できません。下のテキストをマウスでドラッグして、選択してください。）"></textarea>
        <div class="mb-2">
            <x-element.button onclick="return removespaces();" value="半角スペースを除去" size="sm"
                color="orange"></x-element.button>
            <x-element.button onclick="return replacekutouten();" value="句読点を『 ． ， 』に置換" size="sm"
                color="yellow"></x-element.button>
            <span class="mx-10"></span>
            <x-element.button onclick="maydirty_mode(true);" value="直接入力モードに切替" size="sm" color="purple"
                {{-- confirm="本当に直接入力モードにしますか？必要がなければキャンセルを押してください。" --}}
                >
            </x-element.button>
        </div>

        <div class="mb-2  dark:text-gray-400">
            エディタのテキストを
            @foreach ($koumoku as $key => $val)
                <x-element.button onclick="valset('{{ $key }}')" value="{{ $val }}に設定" size="sm"
                    color="{{ $koumokucolor[$key] }}"></x-element.button>
            @endforeach
        </div>
        {{-- <div id="pdftextdiv"> --}}
        <div class="text-sm  dark:text-gray-400">以下はPDFの1ページ目から抽出したテキストです。</div>
        <div class="py-2 px-6 bg-cyan-50 font-monaca" id="pdftext">{{ $pdftext }}</div>

        {{-- </div> --}}
    </div>
    <div class="mx-4">
        <div class="bg-cyan-500 text-white px-3 pb-1 pt-2" id="confirm_shoshi">設定確認画面
        </div>
        <table class="border-cyan-500 border-2">
            @foreach ($koumoku as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
                    <td class="px-2 py-1">{{ $v }}</td>
                    <td class="px-2 py-1" id="confirm_{{ $k }}">{!! nl2br($paper->{$k}) !!}</td>
                </tr>
            @endforeach
        </table>

        {{-- <div class="mx-10 mt-2">
            <div class="text-sm px-2  dark:text-gray-400">和文著者名の設定方法：一名につき、一行ずつ記入してください。氏名のあいだには半角スペースをいれてください。
                氏名のあとに、所属を半角 ( ) または全角（ ）で囲って記載してください。複数の所属がある場合は半角スラッシュ / で区切ってください。<br>
                とくに外国人の氏名については、論文PDFでの表記（カナ/英文）とおなじであることを確認してください。共著者のかたも、投稿一覧からご確認いただけます。</div>
            <div class="mt-2 text-sm px-2  dark:text-gray-400">和文著者名の設定例：</div>
            <textarea id="jpex" name="jpexample" rows="3"
                class="inline-flex mb-1 block p-2.5 w-full text-md text-gray-900 bg-gray-200 rounded-lg border border-gray-300
                 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                  dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="投稿 太郎 (投稿大学)&#10;和布蕪 二郎 (和布蕪大学)&#10;昆布 巻子 (ダシ大学/昆布研究所)" readonly></textarea>
        </div>
        <div class="px-10 text-red-800 text-sm">
            シンポジウムの予稿集・出版担当が所属表記の短縮や修正を行う場合があります。ご了承ください。</div> --}}
    </div>

    <form action="{{ route('paper.dragontextpost', ['paper' => $paper->id]) }}" method="post" id="dragontextpost">
        @csrf
        @method('post')
    </form>

    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray"
            size="lg">
            &larr; 投稿{{ $paper->id_03d() }} に戻る
        </x-element.linkbutton>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/dragontext.js"></script>
        <script src="/js/openclose.js"></script>
        <script src="/js/replace_kuten_in_page.js"></script>
    @endpush

</x-app-layout>
