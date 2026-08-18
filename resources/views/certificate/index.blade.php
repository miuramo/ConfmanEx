<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
            @endphp
            <span class="text-gray-700">{{ $conf->value }}</span> {{ __('表彰状ページ') }}
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6">

        <x-element.linkbutton href="{{ route('certificate.itmsettings') }}" color="cyan" size="md">
            表彰状の項目設定
        </x-element.linkbutton>

        <span class="mx-4"></span>
        <x-element.linkbutton href="{{ route('certificate.export') }}" color="lime" size="md">
            表彰状の生成とダウンロード
        </x-element.linkbutton>

        <span class="mx-4"></span>
        <x-element.linkbutton href="{{ route('certificate.export', ['forPrint' => true]) }}" color="orange" size="md">
            表彰状の生成とダウンロード（A4印刷用）
        </x-element.linkbutton>
    </div>


</x-app-layout>
