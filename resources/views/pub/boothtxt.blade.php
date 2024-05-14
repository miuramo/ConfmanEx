@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('テキスト形式でのセッション割り当て') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>


        </h2>
        <style>
            .hidden-content {
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="px-4 py-4">
        @php
            $samp[] = '# サンプル (ここに入力したものは反映されません)';
            $samp[] = '# 各行、各要素前後の半角空白は取り除かれます。そのうえで、#ではじまる行は無視されます。';
            $samp[] = "# 要素を分割するルール→ (1) | をTAB(\\t)に置き換え、";
            $samp[] = '#                   (2) 連続するTABおよび半角空白をひとつのTABにまとめ、(3) TABで区切ります';
            $samp[] = '# sessionid | paperid | booth';
            $samp[] =
                '#    sessionid は他のカテゴリと重複してもよいです（基本的に、カテゴリ毎に別々の扱いとなります。）';
            $samp[] = '#    (orderint は個別指定できません。booth昇順ソート後の順番が自動的に設定されます。)';

            $lastsession = 0;
            foreach ($subs as $sub) {
                $samp[] =
                    sprintf('%2d', $sub->psession_id) .
                    "\t|\t" .
                    sprintf('%3d', $sub->paper_id) .
                    "\t|\t" .
                    sprintf('%10s', $sub->booth);
                if ($lastsession > $sub->psession_id) {
                    $session_orderint_mismatch = true; // orderintで並べたとき、セッションに戻りが発生している。
                }
                $lastsession = $sub->psession_id;
            }
        @endphp
        @isset($session_orderint_mismatch)
        <x-alert.error>注意：セッション番号の割り振りと、booth (orderint)で並べたときの順番に、齟齬があります。</x-alert.error>
        @endisset

        <x-element.h1>サンプル（ピンク色のテキストエリア内を編集して送信しても、反映されません）
            <div class="mx-2 text-right text-sm">
                発表件数が少なく、ブース記番が連番でよい場合→
                <x-element.linkbutton href="{{ route('pub.booth', ['cat' => $cat]) }}" color="green" size="sm">
                    GUI形式でのセッション割り当て
                </x-element.linkbutton>
            </div>
            <div class="my-2"></div>
            <textarea name="dummy" id="" cols="100" rows="15"
                class="text-sm p-1 bg-pink-100 dark:bg-pink-800 dark:text-gray-200">{{ implode("\n", $samp) }}</textarea>

            <div class="text-sm">
                ヒント：ExcelやGoogle Spreadsheet等で、セル範囲をコピーして貼り付けると、タブ区切りのテキストを得ることができます。
            </div>
        </x-element.h1>

    </div>

    @php
        if (session('sbmap')) {
            $sbmap = session('sbmap');
        }
    @endphp
    <div class="px-4">

        <x-element.h1>サンプルを参考に、以下のテキストエリアに入力して、「割り当て処理を実行」をおしてください。</x-element.h1>
        <div class="mx-4">
            <form action="{{ route('pub.boothtxt', ['cat' => $cat]) }}" method="post" id="boothtxtpost">
                @csrf
                @method('post')
                <textarea name="sbmap" id="session_booth_map" cols="90" rows="10" class="p-1 dark:text-gray-100 dark:bg-gray-900"
                    placeholder="（ここに入力してください）">{{ $sbmap }}</textarea>
                <div>
                    <x-element.submitbutton color="blue" value="9999">
                        割り当て処理を実行
                    </x-element.submitbutton>
                </div>
            </form>
        </div>
    </div>

    <div class="py-4">
    </div>




    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
