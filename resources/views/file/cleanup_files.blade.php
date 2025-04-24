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

    <div class="py-12 px-6">
@php
    $label = [0=>'通常ファイル',1=>'削除済みファイル'];
@endphp
    @foreach ($label as $key => $value)
    <div class="py-12 px-6">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __($value) }}
        </h2>        
        size : {{sprintf("%.2f MB", $totalsize[$key]/1024/1024) }}<br>
        count : {{$totalcount[$key]}}<br>

        <h3 class="font-semibold">うち、ビデオファイル</h3>
        size : {{sprintf("%.2f MB", $totalsize[$key+2]/1024/1024) }}<br>
        count : {{$totalcount[$key+2]}}<br>
    </div>
    @endforeach
        <form action="{{ route('file.cleanup_files') }}" method="post" id="cleanup">
            @csrf
            @method('post')
            <x-element.submitbutton value="delete" color="orange">
                {{ __('削除済みファイルの全削除') }}
            </x-element.submitbutton>
            <x-element.submitbutton value="active_video" color="yellow" confirm="本当に通常ビデオファイルを削除しますか？">
                {{ __('通常ビデオファイルの全削除') }}
            </x-element.submitbutton>
            <x-element.submitbutton value="active_all" color="red" confirm="本当に通常ファイルを削除しますか？">
                {{ __('通常ファイルの全削除') }}
            </x-element.submitbutton>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush
</x-app-layout>
