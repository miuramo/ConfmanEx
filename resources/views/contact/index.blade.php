<!-- components.enquete.index -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            {{-- <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton> --}}
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('コンタクト管理') }}

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

    <div class="py-4 px-6  dark:text-gray-400">

        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'admin']) }}" color="gray" size="sm">
                &larr; 管理 に戻る
            </x-element.linkbutton>
        </div>
        <div class="py-4 px-6  dark:text-gray-400">
            注：ここでは、Cc:を送信するためのコンタクトのみを対象とします。PaperOwnerのメールアドレスは変更しません。<br>
            PaperOwnerのメールアドレスを変更したい場合は、ユーザ管理から行ってください。
        </div>
        <div class="py-4 px-6  dark:text-gray-400">
            コンタクトの数: {{ $count }} (valid: {{ $count_valid }}, invalid: {{ $count_invalid }})
        </div>

        <div class="mb-4">
            <x-element.linkbutton href="{{ route('contact.modify_email') }}" color="pink" size="md">
                コンタクトメールアドレスの置換修正画面へ移動する
            </x-element.linkbutton>
        </div>

        <h3 class="text-lg font-semibold mt-6 mb-2">操作（置換後の再構築・無効化・削除）</h3>
        <form action="{{ route('contact.call_method') }}" method="POST" class="mb-4">
            @csrf
            <select name="method"
                class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                <option value="">操作を選択</option>
                <option value="rebuild_from_papers">すべてのPaper（削除済みを含む）からコンタクトを再構築する</option>
                <option value="invalidate">未参照のコンタクトをinvalidにする</option>
                <option value="bundle_delete">未参照のコンタクトを削除する</option>
                <option value="delete_invalid">invalidなコンタクトを削除する</option>
            </select>
            <x-element.submitbutton color="orange" class="ml-2">実行</x-element.submitbutton>
        </form>

        <h3 class="text-lg font-semibold mb-2">Paperから未参照のコンタクト ({{ $count_unused }}件)</h3>
        @if ($unused_contacts->isEmpty())
            <p>未参照のコンタクトはありません。</p>
        @else
            <table class="bg-white dark:bg-slate-800">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">valid</th>
                        <th class="py-2 px-4 border-b">メールアドレス</th>
                        <th class="py-2 px-4 border-b">infoprovider</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($unused_contacts as $contact)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $contact->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->valid }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->infoprovider }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h3 class="text-lg font-semibold mt-6 mb-2">Invalid コンタクト ({{ $count_invalid }}件)</h3>
        @if ($invalid_contacts->isEmpty())
            <p>Invalidなコンタクトはありません。</p>
        @else
            <table class="bg-white dark:bg-slate-800">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">valid</th>
                        <th class="py-2 px-4 border-b">メールアドレス</th>
                        <th class="py-2 px-4 border-b">infoprovider</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invalid_contacts as $contact)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $contact->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->valid }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->infoprovider }}</td>
                        </tr>
                    @endforeach 
                </tbody>
            </table>
        @endif

        <h3 class="text-lg font-semibold mt-6 mb-2">参考：Top 30 コンタクト</h3>
        @if ($top_30->isEmpty())
            <p>トップのコンタクトはありません。</p>
        @else
            <table class="bg-white dark:bg-slate-800">
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b">ID</th>
                        <th class="py-2 px-4 border-b">valid</th>
                        <th class="py-2 px-4 border-b">メールアドレス</th>
                        <th class="py-2 px-4 border-b">関連Paper数</th>
                        <th class="py-2 px-4 border-b">Paper IDs</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($top_30 as $contact)
                        <tr>
                            <td class="py-2 px-4 border-b">{{ $contact->id }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->valid }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->email }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->paper_count }}</td>
                            <td class="py-2 px-4 border-b">{{ $contact->paper_ids ?? '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

    </div>

</x-app-layout>
