<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                if ($typedesc == null){
                    $typedesc = "unknown";
                }
            @endphp
            {{ $typedesc . __('への一括書き込み') }}
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
        $opts = [3 => '出版と著者'];
        $colors = [3 => 'teal'];
    @endphp

    @isset($bufary)
        @php
            $titleary = App\Models\Paper::select('id', 'title')->pluck('title', 'id')->toArray();
        @endphp
        <div class="px-6">
            <x-element.h1>書き込み内容の事前確認</x-element.h1>
            @foreach ($bufary as $n => $ba)
                @if (!isset($titleary[$ba['PID']]))
                    <div class="bg-red-100 dark:bg-red-300">PaperID: {{ $ba['PID'] }} は存在しません。</div>
                    @continue
                @endif
                @php
                    $ba['title'] = $titleary[$ba['PID']];
                @endphp

                <table class="text-sm border border-blue-600">
                    @foreach ($ba as $k => $v)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-blue-100' : 'bg-blue-50 dark:bg-slate-400' }}">
                            <td class="p-1">{{ $k }}</td>
                            <td class="p-1">{!! nl2br($v) !!}</td>
                        </tr>
                    @endforeach
                </table>
            @endforeach
        </div>
    @endisset

    <div class="py-2 px-6">
        <div class="p-2 bg-lime-100 dark:bg-lime-600">
            該当PaperIDの掲示板が作成されていない場合は、自動的に作成します。
        </div>
    </div>
    <div class="py-2 px-6">

        <form action="{{ route('bb.multisubmit',['type'=>$type]) }}" method="post" id="bb_mulsub">
            @csrf
            @method('post')
            <input type="hidden" name="type" value="{{ $type }}">
            <table>
                <tr class="bg-pink-100 dark:bg-pink-300">
                    <td class="px-2 py-1">
                        <label for="comsub">共通Subject: </label>
                    </td>
                    <td class="px-2 py-1">
                        <input type="text" name="subject" id="comsub" size="85" value="{{ $subject }}">
                    </td>
                </tr>
                <tr class="bg-pink-50 dark:bg-pink-200">
                    <td class="px-2 py-1"><label for="preface">共通Preface: </label></td>
                    <td class="px-2 py-1">
                        <textarea name="preface" cols="85" rows="3" id="preface">{{ $preface }}</textarea>
                    </td>
                </tr>
                <tr class="bg-pink-100 dark:bg-pink-300">
                    <td class="px-2 py-1"><label
                            for="csv">"======<br>PaperID<br>本文1行目<br>本文2行目<br>本文3行目<br>...<br>======"</label><br><br><br>
                        <span class="bg-yellow-200">
                            PaperIDは<br>0埋めでなくても<br>大丈夫です。<br><br>
                            一行に = が<br>6個以上<br>30個以下<br>連続しているとき<br>区切り線<br>として認識<br>します。
                        </span>
                    </td>
                    <td class="px-2 py-1">
                        <textarea name="csv" cols="85" rows="30" id="csv">{{ $csv }}</textarea>
                    </td>
                </tr>
                <tr>
                    <td> </td>
                    <td>
                        <x-element.submitbutton value="confirm" color="lime"
                            form="bb_mulsub">確認</x-element.submitbutton>
                        <x-element.submitbutton value="submit" color="yellow" form="bb_mulsub"
                            confirm="本当に書き込みますか？">一括書き込み</x-element.submitbutton>
                    </td>
                </tr>
            </table>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
