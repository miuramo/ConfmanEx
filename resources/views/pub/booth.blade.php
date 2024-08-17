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
        <div class="ml-8 mt-2 text-right">
            発表件数が多い場合やPrefixをつけたい場合 →
            <x-element.linkbutton href="{{ route('pub.boothtxt', ['cat' => $cat]) }}" color="blue" size="sm">
                テキスト形式でのセッション割り当て
            </x-element.linkbutton>
        </div>

        <div class="px-4 py-2 leading-relaxed dark:text-gray-100">
            <x-element.button id="toggleButton" value="セッション割り当ての操作方法をみる" color='green' size='md'
                onclick="openclose('usage')">
            </x-element.button>
            <span class="mx-2"></span>
            <x-element.button id="toggleButton" value="ブース記番の設定画面をとじる" color='pink' size='md'
                onclick="openclose('setbooth')">
            </x-element.button>

            <div class="hidden-content bg-green-100 p-4 mt-2 dark:text-gray-600" id="usage" style="display:none;">
                発表の凡例： <span class="border-4 border-green-600 px-2 py-1 rounded-lg bg-white dark:text-black">PaperID
                    orderint(通し番号) title [ブース記番]</span>
                <div class="my-2">
                    発表を、sessionにドラッグしてください。ドラッグ終了（自動レイアウト）時に自動保存されます。
                    ページの再読み込みをすると、orderint が更新され、最後に保存された設定を確認できます。
                </div>
            </div>

            {{-- <div class="hidden-content bg-orange-100 p-4 mt-2 dark:text-gray-600" id="setbooth" style="display:none;"> --}}
            <div class="bg-gray-200 p-2 mt-2 dark:text-gray-600" id="setbooth">
                <div class="mt-2 grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-2">
                    <div class="mx-2 p-3 rounded-lg border-2 border-orange-400 bg-orange-200">

                        <form action="{{ route('pub.booth', ['cat' => $cat]) }}" method="post" id="boothpost_byorder">
                            @csrf
                            @method('post')
                            <x-element.submitbutton value="byorder" color="orange">現在の orderint (通し番号) を使用して設定する
                            </x-element.submitbutton>
                            @php
                                $format = '%03d';
                                if ($cat == 2) {
                                    $format = 'P-%d';
                                }
                            @endphp
                            <div class="ml-8">
                                sprintfフォーマット <input type="text" name="print_format" id="print_format" size="9"
                                    value="{{ $format }}" class="text-sm p-1 dark:text-black">
                                <span class="mx-2"></span>
                                orderintに追加する値 <input type="number" name="additional" min=0 max=1000 value=0 
                                    class="text-sm p-1 dark:text-black">
                            </div>
                        </form>
                    </div>
                    <div class="mx-2 p-3 rounded-lg border-2 border-pink-400 bg-pink-200">

                        <form action="{{ route('pub.booth', ['cat' => $cat]) }}" method="post"
                            id="boothpost_bysession">
                            @csrf
                            @method('post')
                            <x-element.submitbutton value="bysession" color="pink">現在のセッション番号と、セッション内の並び順を使用して設定する
                            </x-element.submitbutton>
                            @php
                                $format = '%d-%d';
                            @endphp
                            <div class="ml-8">
                                sprintfフォーマット <input type="text" name="print_format" id="print_format" size="8"
                                    value="{{ $format }}" class="text-sm p-1 dark:text-black">
                            </div>
                        </form>

                    </div>
                </div>
                <div class="m-2 text-sm text-blue-600">注：ボタンを押すと、「現在の」セッション割り当てや orderint に基づいて、ブース記番を設定します。
                    セッション割り当てやセッション内の順番を変更したら、再度「設定する」ボタンを押してください。なお、ブース記番はZIPダウンロード時のファイル名の一部になります。
                </div>
            </div>

        </div>
    </div>
    <form action="{{ route('pub.booth', ['cat' => $cat]) }}" method="post" id="boothpost">
        @csrf
        @method('post')
        <input type="hidden" name="json">
        <div class="ml-2 bg-green-100 px-4 py-2">
            セッション割り当て操作時に、orderintは自動で再設定します。 <span class="mx-2 text-sm text-blue-600">表示は再読み込みすると反映します。</span>
        </div>

        <!-- SubmitController.booth()  -->
    </form>
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
        <script src="/js/openclose.js"></script>
    @endpush
    <script>
        var subpapers = {!! json_encode($subs) !!};
    </script>

</x-app-layout>
