<!-- components.enquete.index -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            {{-- <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton> --}}
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('メールアドレスの置換修正') }}

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
        注：ここでは、Cc:を送信するためのコンタクトメールアドレスのみを修正します。PaperOwnerのメールアドレスは変更しません。<br>
        PaperOwnerのメールアドレスを変更したい場合は、ユーザ管理から行ってください。<br>
        また、置換後はコンタクト管理にもどって、コンタクトの再構築や不要なコンタクトの削除を行ってください。（念の為、コンタクト置換時に当該Paperのみの再構築を行います。）
    </div>

    <div class="py-4 px-6  dark:text-gray-400">

        <div class="mb-4">
            <x-element.linkbutton href="{{ route('contact.index') }}" color="gray" size="sm">
                &larr; コンタクト管理 に戻る
            </x-element.linkbutton>
        </div>
        <h3 class="text-lg font-semibold mt-6 mb-2">操作</h3>
        <form action="{{ route('contact.modify_email') }}" method="POST" class="mb-4">
            @csrf
            <input type="text" name="pre" size="50" placeholder="置換前のメールアドレス" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('pre', $pre ?? '') }}"><br>
            <input type="text" name="post" size="50" placeholder="置換後のメールアドレス（空にすると、確認のみ）" class="border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" value="{{ old('post', $post ?? '') }}"><br>
            <x-element.submitbutton color="orange" class="ml-2">検索または置換</x-element.submitbutton>
        </form>


    <div>
        @if ($target_papers->isEmpty())
            <p>該当するPaperはありません。</p>
        @else
            <table class="bg-white dark:bg-slate-800 w-full">
                <thead>
                    <tr>
                        <th class="py-2 px-2 border-b text-left">Paper ID</th>
                        <th class="py-2 px-2 border-b text-left">Title</th>
                        <th class="py-2 px-2 border-b text-left">Owner / Email</th>
                        <th class="py-2 px-2 border-b text-left">Contact Emails</th>
                        <th class="py-2 px-2 border-b text-left">著者</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($target_papers as $paper)
                        <tr>
                            <td class="py-2 px-2 border-b">{{ $paper->id }}</td>
                            <td class="py-2 px-2 border-b text-xs">{{ $paper->title }}</td>
                            <td class="py-2 px-2 border-b text-sm text-nowrap">{{ $paper->paperowner->name ?? 'N/A' }} (uID: {{ $paper->paperowner->id ?? 'N/A' }})<br>
                            {{ $paper->paperowner->email ?? 'N/A' }}</td>
                            <td class="py-2 px-2 border-b text-nowrap">{!! nl2br(e($paper->contactemails)) !!}</td>
                            <td class="py-2 px-2 border-b text-sm">
                                {!! nl2br(e($paper->authorlist)) !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div>
        @if (isset($target_contacts))
            <h3 class="text-lg font-semibold mt-6 mb-2">該当するContact</h3>
            @if ($target_contacts->isEmpty())
                <p>該当するContactはありません。</p>
            @else
                <table class="bg-white dark:bg-slate-800 w-full">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left">Contact ID</th>
                            <th class="py-2 px-4 border-b text-left">Email</th>
                            <th class="py-2 px-4 border-b text-left">Valid</th>
                            <th class="py-2 px-4 border-b text-left">InfoProvider</th>
                            <th class="py-2 px-4 border-b text-left">関連Paper</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($target_contacts as $contact)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $contact->id }}</td>
                                <td class="py-2 px-4 border-b">{{ $contact->email }}</td>
                                <td class="py-2 px-4 border-b">{{ $contact->valid }}</td>
                                <td class="py-2 px-4 border-b">{{ $contact->infoprovider }}</td>
                                <td class="py-2 px-4 border-b">
                                    @if ($contact->papers->isEmpty())
                                        なし
                                    @else
                                        <ul class="list-disc list-inside">
                                            @foreach ($contact->papers as $paper)
                                                <li>{{ $paper->id }}: {{ $paper->title }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
    <div>
        @if (isset($target_users))
            <h3 class="text-lg font-semibold mt-6 mb-2">該当するUser</h3>
            @if ($target_users->isEmpty())
                <p>該当するUserはありません。</p>
            @else
                <table class="bg-white dark:bg-slate-800 w-full">
                    <thead>
                        <tr>
                            <th class="py-2 px-4 border-b text-left">User ID</th>
                            <th class="py-2 px-4 border-b text-left">Name</th>
                            <th class="py-2 px-4 border-b text-left">Email</th>
                            <th class="py-2 px-4 border-b text-left">所有Paper</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($target_users as $user)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $user->id }}</td>
                                <td class="py-2 px-4 border-b">{{ $user->name }}</td>
                                <td class="py-2 px-4 border-b">{{ $user->email }}</td>
                                <td class="py-2 px-4 border-b">
                                    @if ($user->papers->isEmpty())
                                        なし
                                    @else
                                        <ul class="list-disc list-inside">
                                            @foreach ($user->papers as $paper)
                                                <li>{{ $paper->id }}: {{ $paper->title }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>

</x-app-layout>
