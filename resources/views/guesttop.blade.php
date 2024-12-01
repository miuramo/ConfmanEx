<x-app-layout>
    <!-- guesttop -->
    @php
        $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
        $confurl = App\Models\Setting::where('name', 'CONF_URL')->where('valid', true)->first();
        $cfpurl = App\Models\Setting::where('name', 'CFP_URL')->where('valid', true)->first();
    @endphp
    <div
        class="w-4/5 mt-14 ml-6 px-7 py-5  bg-slate-50 text-4xl font-extrabold tracking-wide text-gray-600 drop-shadow-xl dark:bg-slate-700 dark:text-slate-400">
        {{ env('APP_NAME') }}
        @isset($conf->value)
            @isset($confurl)
                for
                <a href="{{ $confurl->value }}" class="hover:underline hover:text-blue-500" target="_blank">{{ $conf->value }}</a>
            @else
                for {{ $conf->value }}
            @endisset
        @endisset
    </div>

    @php
        $cfplinktext = App\Models\Setting::findByIdOrName('CFP_LINKTEXT', 'value');
    @endphp
    @isset($cfpurl)
        <div class="my-4 mx-8 px-6">
            <!-- CFPリンクの設定は、Setting:CFP_LINKTEXT CFP_URL -->
            <a href="{{ $cfpurl->value }}"
                class="hover:underline hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-500 text-2xl p-2">{{ $cfplinktext }}</a>
        </div>
    @endisset

    <div class="my-6 mx-6 p-6 bg-slate-200 dark:bg-slate-700 dark:text-slate-400">
        <span class="mx-2 bg-gray-500 px-3 py-1 text-lg text-white">免責事項</span>
        以下の事項について、すべて了解いただける場合のみ、本投稿システムを使用してください。
        @php
            $itms = App\Models\Confirm::select('name', 'mes')
                ->where('grp', 0)
                ->where('valid', true)
                ->orderBy('name')
                ->get();
        @endphp
        <!-- 免責事項の設定は、Confirms grp=0 にあります。 -->
        <ol class="list-decimal px-8 pt-4">
            @foreach ($itms as $itm)
                <li>{!! $itm->mes !!}
                </li>
            @endforeach
        </ol>
    </div>

    @php
        $introvideourl = App\Models\Setting::getValue('INTRO_VIDEO_URL');
    @endphp
    <!-- 動画でみる投稿の流れの設定は、Setting:INTRO_VIDEO_URL にあります。 -->
    @isset($introvideourl)
        <div class="my-6 mx-6 p-6 bg-slate-200 dark:bg-slate-700 dark:text-slate-400">
            <div class="mx-2">
                <span class=" bg-gray-500 px-3 py-1 text-lg text-white">動画でみる投稿の流れ</span>
                <video class="mt-2" width="640" height="360" controls>
                    <source src="{{ $introvideourl }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
        </div>
    @endisset

    <div class="my-10 text-center text-gray-300 dark:text-gray-500">
        Powered by <a href="https://github.com/miuramo/ConfmanEx/" target="_blank" class="hover:underline">ConfmanEx</a>
        <br>
        Copyright &copy; 2024 <a href="https://istlab.info/" target="_blank" class="hover:underline">Motoki Miura</a>
    </div>

</x-app-layout>
