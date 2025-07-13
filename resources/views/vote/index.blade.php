<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
            @endphp
            <span class="text-gray-700">{{ $conf->value }}</span> {{ __('投票ページ') }}



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

        @if ($ticket)
            <x-element.h1>投票トークンの確認</x-element.h1>
            <div class="mx-4">
                <p>投票トークン： {{ $ticket->token }}</p>
                <p>投票トークンの送信先メールアドレス： {{ $ticket->email }}</p>
                @if ($ticket->user_id)
                    <p class="text-blue-500">投票トークンはユーザーアカウントに連携されています。どのブラウザでも「ログイン」→「投票」で投票できます。</p>
                @else
                    <p class="text-purple-500">投票トークンはユーザーアカウントに連携されていません。このブラウザ以外で投票を行う場合は、メールに記載されたURLを開く必要があります。</p>
                @endif
            </div>
        @endif

        <div class="py-2"></div>
        <x-element.h1>投票を行うには、以下のボタンを押してください。</x-element.h1>
        @php
            $votes = App\Models\Vote::where('isopen', true)->where('isclose', false)->get();
        @endphp
    </div>

    <x-vote.votelink>
    </x-vote.votelink>



</x-app-layout>
