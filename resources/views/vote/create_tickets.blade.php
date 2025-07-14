<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
            @endphp
            <span class="text-gray-700">{{ $conf->value }}</span> {{ __('投票チケットの作成') }}
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
        <div class="my-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'award']) }}" color="gray" size="sm">
                &larr; 投票Topに戻る
            </x-element.linkbutton>
        </div>

        <form method="POST" action="{{ route('vote.create_tickets') }}">
            @csrf
            <x-element.h1>投票チケットの作成</x-element.h1>
            <p>投票チケットを作成するには、以下のフォームにメールアドレスを1行に1つずつ入力してください。</p>
            <div class="mt-4">
                <textarea name="emails" cols="60" rows="10"></textarea>
            </div>
            <div class="mt-1">
                <x-element.submitbutton color="lime">
                    投票チケットを作成
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <div class="mx-6">
        @php
            $tickets = App\Models\VoteTicket::orderBy('created_at', 'desc')->get();
            $count = $tickets->count();
        @endphp
        <x-element.h1>作成済みの投票チケット ({{ $count }}件)
            <span class="mx-4"></span>
            <x-element.linkbutton href="{{ route('vote.send_tickets') }}" color="pink" size="sm"
                confirm="本当に全ての投票チケットをメール送信しますか？">
                投票チケットをメール送信
            </x-element.linkbutton>

            <span class="mx-4"></span>
            <x-element.deletebutton_nodiv color="red" size="sm" align="right"
                action="{{ route('vote.destroy_tickets') }}" confirm="本当に全ての投票チケットを削除しますか？">
                全ての投票チケットを削除
            </x-element.deletebutton_nodiv>
        </x-element.h1>
        @if ($tickets->isEmpty())
            <p>現在、作成済みの投票チケットはありません。</p>
        @else
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">メールアドレス</th>
                        <th class="px-4 py-2">トークン</th>
                        <th class="px-4 py-2">作成日時</th>
                        <th class="px-4 py-2">有効？</th>
                        <th class="px-4 py-2">ユーザID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td class="border px-4 py-2">{{ $ticket->email }}</td>
                            <td class="border px-4 py-2">{{ $ticket->token }}</td>
                            <td class="border px-4 py-2">{{ $ticket->created_at }}</td>
                            <td class="border px-4 py-2">{{ $ticket->activated }}</td>
                            <td class="border px-4 py-2">
                                @if ($ticket->user_id > 0)
                                    連携済み
                                @else
                                    未連携
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

</x-app-layout>
