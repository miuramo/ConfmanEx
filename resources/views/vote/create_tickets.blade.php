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
            <div class="my-2">
                <x-element.button onclick="checkAllByClass('sentnum0')" color="teal" size="sm"
                    value="送信回数0にチェック">
                </x-element.button>
                <x-element.button onclick="checkAllByClass('notactivated')" color="purple" size="sm"
                    value="未開封にチェック">
                </x-element.button>
                <x-element.button value="全チェック" onclick="CheckAll('sendordestroy')" color="gray" size="sm" />
                <x-element.button value="全チェックを外す" onclick="UnCheckAll('sendordestroy')" color="slate" size="sm" />

            </div>

            <table class="table-auto w-full sortable" id="tickets_table">
                <thead>
                    <tr>
                        <th class="border px-2 py-1 bg-slate-300">c</th>
                        <th class="border px-2 py-1 bg-slate-300">メールアドレス</th>
                        <th class="border px-2 py-1 bg-slate-300">作成日時</th>
                        <th class="border px-2 py-1 bg-slate-300">送信回数</th>
                        <th class="border px-2 py-1 bg-slate-300">開封した？</th>
                        <th class="border px-2 py-1 bg-slate-300">ユーザID</th>
                        <th class="border px-2 py-1 bg-slate-300">トークン</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tickets as $ticket)
                        <tr>
                            <td class="border px-2 py-1"><input form="sendordestroy" type="checkbox" name="ticket_ids[]"
                                    value="{{ $ticket->id }}" class="sentnum{{ $ticket->sentnum }} 
                                    @if(!$ticket->activated) notactivated @endif"></td>
                            <td class="border px-2 py-1">{{ $ticket->email }}</td>
                            <td class="border px-2 py-1">{{ $ticket->created_at }}</td>
                            <td class="border px-2 py-1">{{ $ticket->sentnum }}</td>
                            <td class="border px-2 py-1">{{ $ticket->activated }}</td>
                            <td class="border px-2 py-1">
                                @if ($ticket->user_id > 0)
                                    連携済み
                                @else
                                    未連携
                                @endif
                            </td>
                            <td class="border px-2 py-1 text-sm">{{ $ticket->token }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
    <div class="px-6 py-4">
        <form id="sendordestroy" method="POST" action="{{ route('vote.send_tickets_checked') }}">
            @csrf
            <x-element.submitbutton color="pink" size="sm" confirm="選択した投票チケットをメール送信しますか？">
                選択した投票チケットをメール送信
            </x-element.submitbutton>
            <span class="mx-4"></span>
            <!-- メソッドを切り替える hidden input -->
            <input type="hidden" name="_method" id="methodField" value="POST">
            <x-element.submitbutton color="red" size="sm" confirm="選択した投票チケットを削除しますか？"
                onclick="document.getElementById('methodField').value = 'DELETE';">
                選択した投票チケットを削除
            </x-element.submitbutton>
        </form>
    </div>
    <div class="py-2"></div>

    @push('localjs')
        <script src="/js/sortable.js"></script>
        <script src="/js/chk_all.js"></script>
    @endpush
    <script>
        function checkAllByClass(cls) {
            var checks = document.getElementsByClassName(cls);
            for (var i = 0; i < checks.length; i++) {
                checks[i].checked = true;
            }
        }
    </script>

</x-app-layout>
