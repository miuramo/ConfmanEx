<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('掲示板の管理') }}
        </h2>
        <div class="text-right">
            <x-element.linkbutton2 href="{{ route('pub.accstatus') }}" color="cyan" target="_blank" size="sm">
                採択状況の確認
            </x-element.linkbutton2>
            <span class="mx-4"></span>
        </div>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>
    <!-- paper.show -->

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $opts = [3 => '出版と著者'];
        $colors = [3 => 'teal'];
    @endphp

    <div class="py-2 px-6">
        下のボタンを押すと、作成済みの掲示板一覧を表示します。（かっこ内は掲示板の数）
    </div>
    <div class="py-2 px-6">
        @foreach ($opts as $i => $lbl)
            <x-element.button id="toggleButton" value="{{ $lbl }} ({{ count($bbs[$i]) }})" :color="$colors[$i]"
                onclick="openclose('div_type{{ $i }}')">
            </x-element.button>
            <span class="mx-1"></span>
        @endforeach

        @foreach ($opts as $i => $lbl)
            <div class="hidden-content bg-{{ $colors[$i] }}-200 p-2 mt-2 dark:text-gray-600"
                id="div_type{{ $i }}" style="display:none;">

                @foreach ($bbs[$i] as $bb)
                    <div>
                        @isset($bb->paper)
                            <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}"
                                :color="$colors[$i]" target="_blank" size="sm">
                                {{ $bb->paper->id_03d() }} {{ $bb->paper->title }}
                                ({{ $bb->nummessages() }} messages)
                            </x-element.linkbutton>
                        @else
                            <div>Error: No Paper associated {{ $bb->id }}</div>
                        @endisset
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="mx-6 my-2 p-3 bg-slate-300 rounded-lg dark:bg-slate-700 dark:text-gray-300">
        <form action="{{ route('bb.createnew') }}" method="post" id="bb_new">
            @csrf
            @method('post')
            <div class="mt-2">
                <label>作成する掲示板の種類：<b>出版と著者</b></label>
            </div>
            <div class="mb-1 mt-3">
                <label for="pids">掲示板をまとめて作成する Paper ID List (カンマ区切り) または all または accepted</label>
            </div>
            <textarea name="pids" id="pids" cols="80" rows="5" placeholder="012, 023, 034, ..." class="mx-2 p-1"></textarea>
            {{-- <input type="text" name="pids" id="pids" size="80" placeholder="012, 023, 034, ..."
                class="mx-2 p-1"> --}}
            <div class="mt-4 ml-4 text-pink-600">
                <label>掲示板を all または accepted で作成する際の、対象カテゴリ</label>
                {{-- </div>
            <div class="px-2 py-2"> --}}
                <br>
                @php
                    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
                @endphp
                @foreach ($cats as $val => $lbl)
                    <input type="radio" name="catid" value="{{ $val }}" id="cat{{ $val }}">
                    <label for="cat{{ $val }}" class="mr-3">{{ $lbl }}</label>
                @endforeach
                <br>
                (上で all または accepted を指定したときは必須。それ以外は入力しても無視される)
            </div>
            <div class="px-2 py-1">
                <input type="hidden" name="type" value="3" id="rad3">
            </div>
            <input type="hidden" name="for_pub" value="1">
            <div>
                <x-element.submitbutton value="submit" color="yellow">「出版と著者」掲示板を作成
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <div class="m-6">
        <x-element.linkbutton href="{{ route('bb.multisubmit', ['type'=>3]) }}" color="teal" size="md">
            出版掲示板への一括書き込み
        </x-element.linkbutton>
    </div>

    <div class="m-6">
        <x-element.h1c color="orange" dark="300">対応未完了の出版掲示板一覧</x-element.h1c>
        <form action="{{ route('bb.needreply') }}" method="post" id="bb_needreply0">
            @csrf
            @method('post')
            <input type="hidden" name="needreply" value="0">
            <x-bb.needreply color="orange" form="bb_needreply0" :bbs="$bbs[3]">
            </x-bb.needreply>
        </form>
    </div>

    <div class="m-6">
        <x-element.h1c color="cyan">対応済みの出版掲示板一覧</x-element.h1c>
        <form action="{{ route('bb.needreply') }}" method="post" id="bb_needreply1">
            @csrf
            @method('post')
            <input type="hidden" name="needreply" value="1">
            <x-bb.needreply color="cyan" form="bb_needreply1" future="orange" :bbs="$bbs[3]">
            </x-bb.needreply>
        </form>
    </div>

    <div class="mx-6 mt-32 p-3 bg-slate-300 rounded-lg dark:bg-slate-700 dark:text-gray-300">
        Danger Zone
        <span class="mx-2"></span>
        <form class="inline" action="{{ route('bb.destroy_bytype') }}" method="post" id="bb_destroy_bytype">
            @csrf
            @method('post')
            <input type="hidden" name="type" value="3">
            <input type="hidden" name="for_pub" value="1">
            <x-element.deletebutton confirm="「出版と著者」の掲示板を全削除してよいですか？" form="bb_destroy_bytype">
                「出版と著者」掲示板の全削除
            </x-element.deletebutton>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
        <script src="/js/chk_all.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
