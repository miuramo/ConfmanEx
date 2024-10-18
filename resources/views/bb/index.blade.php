<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('掲示板の管理') }}
        </h2>
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
        $opts = [1 => '査読者同士の事前議論', 2 => 'メタと著者', 3 => '出版と著者'];
        $colors = [1 => 'cyan', 2 => 'teal', 3 => 'pink'];
        $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
        if ($nameofmeta != null) {
            $opts[2] = $nameofmeta . 'と著者';
        }
    @endphp

    <div class="py-2 px-6">
        下のボタンを押すと、作成済みの掲示板一覧を表示します。（かっこ内は掲示板の数）
    </div>
    <div class="py-2 px-6">
        @foreach ($opts as $i => $lbl)
            <x-element.button id="toggleButton" value="{{ $lbl }} ({{ count($bbs[$i]) }})" :color="$colors[$i]"
                onclick="openclose('div_type{{ $i }}')">
            </x-element.button>
        @endforeach

        @foreach ($opts as $i => $lbl)
            <div class="hidden-content bg-{{$colors[$i]}}-200 p-2 mt-2 dark:text-gray-600" id="div_type{{ $i }}"
                style="display:none;">

                @foreach ($bbs[$i] as $bb)
                    <div>
                        @isset($bb->paper)
                        <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}"
                            :color="$colors[$i]" target="_blank" size="sm">
                            {{ $bb->paper->id_03d() }} {{ $bb->paper->title }} 
                            ({{ $bb->nummessages() }} messages)
                        </x-element.linkbutton>
                        @else
                        <div>Error: No Paper associated {{$bb->id}}</div>
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
            <div class="">
                <label>掲示板をまとめて作成する発表の対象カテゴリ</label>
            </div>
            <div class="px-2 py-2">
                @php
                    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
                @endphp
                @foreach ($cats as $val => $lbl)
                    <input type="radio" name="catid" value="{{ $val }}" id="cat{{ $val }}">
                    <label for="cat{{ $val }}" class="mr-3">{{ $lbl }}</label>
                @endforeach
            </div>
            <div class="mb-1">
                <label for="pids">掲示板をまとめて作成する Paper ID List (カンマ区切り) / all / accepted</label>
            </div>
            <input type="text" name="pids" id="pids" size="80" placeholder="pid1, pid2, ..."
                class="mx-2 p-1">
            <div class="mt-2">
                <label>作成する掲示板の種類</label>
            </div>
            <div class="px-2 py-1">
                @foreach ($opts as $val => $lbl)
                    <input type="radio" name="type" value="{{ $val }}" id="rad{{ $val }}">
                    <label for="rad{{ $val }}" class="mr-3">{{ $lbl }}</label>
                @endforeach

            </div>
            <div>
                <x-element.submitbutton value="submit" color="yellow">作成
                </x-element.submitbutton>
            </div>
        </form>

    </div>

    <div class="mx-6 mt-10 p-3 bg-slate-300 rounded-lg dark:bg-slate-700 dark:text-gray-300">
        Danger Zone
        <span class="mx-2"></span>
        <x-element.deletebutton_nodiv confirm="全削除してよいですか？" :action="route('bb.destroy')">
            掲示板の全削除
        </x-element.deletebutton_nodiv>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
