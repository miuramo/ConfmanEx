@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>
    <!-- pub.booth -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('セッション割り当て') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>

        </h2>
        <style>
            .line {
                fill: none;
                stroke: #000;
                stroke-width: 3px;
                stroke-linejoin: round;
                stroke-linecap: round;
            }

            .parent {
                fill: #eff;
                stroke: #77f;
                stroke-width: 2px;
            }
        </style>
    </x-slot>

    <div class="px-4 py-4">
        <div class="px-4 py-2 leading-relaxed dark:text-gray-100">
            発表の凡例： <span class="border-4 border-green-600 px-2 py-1 rounded-lg bg-white dark:text-black">PaperID orderint(通し番号) title [ブース記番]</span><br>
            発表を、sessionにドラッグしてください。ドラッグ終了（自動レイアウト）時に自動保存されます。ページの再読み込みをすると、orderint と ブース記番 の表示が更新され、最後に保存された設定を確認できます。
            <form action="{{ route('pub.booth', ['cat' => $cat]) }}" method="post" id="boothpost">
                @csrf
                @method('post')
                <input type="hidden" name="json">
                <div class="ml-8">
                    <input type="checkbox" name="copy_orderint_to_booth" id="copy_ordint_to_booth" value="on">
                    <label for="copy_ordint_to_booth">自動保存時、ブース記番をorderintに揃える</label> <span class="mx-2 text-sm text-blue-400">ブース記番はZIPダウンロード時のファイル名の一部になります。</span>
                </div>
                <div class="ml-8">
                    orderintをブース記番に変換するときのsprintfフォーマット <input type="text" name="print_format" id="print_format" size="6" value="%03d" class="text-sm p-1 dark:text-black">
                </div>
                <div class="ml-8 mt-2 text-right">
                    発表件数が多い場合やPrefixをつけたい場合 →
                    <x-element.linkbutton href="{{ route('pub.boothtxt', ['cat' => $cat]) }}" color="blue" size="sm">
                        テキスト形式でのセッション割り当て
                    </x-element.linkbutton>
                </div>
                <!-- SubmitController.booth()  -->
            </form>
        </div>
        <svg id="chart" width="100%" height="2200" style="background:#ffe;"></svg>
    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif



    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="https://d3js.org/d3.v5.min.js"></script>
        <script src="/js/d3groupedit.js"></script>
        <script src="/js/d3contextmenu.js"></script>
        <script src="/js/d3booth.js"></script>
    @endpush
    <script>
        var subpapers = {!! json_encode($subs) !!};
    </script>

</x-app-layout>
