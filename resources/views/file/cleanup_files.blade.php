<!-- file.create -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('削除済みファイル管理') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-6 px-6">
        @php
            $label = [0 => '通常ファイル', 1 => '削除済みファイル'];
        @endphp
        @foreach ($label as $key => $value)
            <div class="py-12 px-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
                    {{ __($value) }}
                </h2>
                size : {{ sprintf('%.2f MB', $totalsize[$key] / 1024 / 1024) }}<br>
                count : {{ $totalcount[$key] }}<br>

                <h3 class="font-semibold">うち、ビデオファイル</h3>
                size : {{ sprintf('%.2f MB', $totalsize[$key + 2] / 1024 / 1024) }}<br>
                count : {{ $totalcount[$key + 2] }}<br>
            </div>
        @endforeach
        <form action="{{ route('file.cleanup_files') }}" method="post" id="cleanup">
            @csrf
            @method('post')
            <x-element.submitbutton value="delete" color="yellow" confirm="本当に削除済みファイルを削除しますか？">
                {{ __('削除済みファイルの全削除') }}
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton value="active_video" color="orange" confirm="本当に通常ビデオファイルを全削除しますか？">
                {{ __('通常ビデオファイルの全削除') }}
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton value="active_all" color="red" confirm="本当に通常ファイルを全削除しますか？">
                {{ __('通常ファイルの全削除') }}
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton value="notindb" color="lime" confirm="本当にDBで管理されていないファイルを全削除しますか？">
                {{ __('DBで管理されていないファイルの全削除') }}
            </x-element.submitbutton>
        </form>
    </div>

    <div class="py-6 px-6">
        @php
            $fileput_dir = App\Models\Setting::where('name', 'FILEPUT_DIR')->first()['value'];
            $apf = App\Models\File::apf();
            $pf = App\Models\File::pf();
        @endphp
        DB_Setting FILEPUT_DIR: {{ $fileput_dir }} <br>
        File::$filedir: {{ App\Models\File::$filedir }}<br>
        File::apf(): {{ $apf }} <br>
        File::pf(): {{ $pf }} <br>
    </div>

    @php
        $list = App\Models\File::getFileNamesNotInDB();
    @endphp
    <div class="py-6 px-6 bg-yellow-100">
        DBで管理されていないファイル<br>
        <ul>
            @foreach ($list['notindb'] as $n => $fn)
                <li>
                    {{ $fn }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="py-6 px-6 bg-cyan-100">
        DBで管理されているファイル<br>
        <ul>
            @foreach ($list['indb'] as $fid => $fn)
                <li>
                    {{$fid}} - {{ $fn }}
                </li>
            @endforeach
        </ul>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush
</x-app-layout>
