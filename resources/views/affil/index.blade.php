<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">

            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>

        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('所属の修正ルールの一覧') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-2 px-2">
        <form action="{{ route('affil.update') }}" method="post">
            @csrf

            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-2 py-2 text-left">ID</th>
                        <th class="px-2 py-2 text-left">修正前</th>
                        <th class="px-2 py-2 text-left">修正後</th>
                        <th class="px-2 py-2 text-left">関連PaperID</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($affils as $affil)
                        <tr>
                            <td class="px-2 py-2">{{ $affil->id }}</td>
                            <td class="px-2 py-2">{{ $affil->before }}</td>
                            <td class="px-2 py-2">
                                <input type="text" name="after[]" value="{{ $affil->after }}"
                                    class=" border-gray-300 dark:border-gray-700 rounded-md shadow-sm" size="64">
                                <input type="hidden" name="id[]" value="{{ $affil->id }}">
                            </td>
                            <td class="px-2 py-2">
                                @if (is_array($affil->pids))
                                    @foreach ($affil->pids as $pid)
                                        {{ $pid }}
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    更新
                </button>
            </div>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush
</x-app-layout>
