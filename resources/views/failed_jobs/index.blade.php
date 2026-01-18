<!-- components.enquete.resetenqans -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            Failed Jobs 一覧
            <span class="mx-4"></span>
            @if ($all)
                (全件表示)
                <span class="mx-4"></span>
                <x-element.linkbutton href="{{ route('admin.failed_jobs') }}" color="lime" size="sm">
                    未読のみ表示
                </x-element.linkbutton>
            @else
                <x-element.linkbutton href="{{ route('admin.failed_jobs', ['all' => 'true']) }}" color="gray"
                    size="sm">
                    全件表示
                </x-element.linkbutton>
            @endif
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="mx-2 my-4">
        @if (count($failedJobs) === 0)
            <div class="p-4 bg-green-100 rounded dark:bg-green-900">
                現在、未読の失敗ジョブはありません。
            </div>
        @else
            <form action="{{ route('admin.failed_jobs.mark_as_read', 0) }}" method="POST" class="text-right mb-4">
                @csrf
                <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                    すべて既読にする
                </button>
            </form>

            @foreach ($failedJobs as $failedJob)
                <div class="mb-4 p-4 border rounded bg-red-50 dark:bg-red-900">
                    <form action="{{ route('admin.failed_jobs.mark_as_read', $failedJob->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-500 text-white rounded hover:bg-purple-600">
                            既読にする
                        </button>
                    </form>
                    <div class="mb-2 font-bold">ジョブID: {{ $failedJob->id }} | 失敗日時: {{ $failedJob->failed_at }}</div>
                    <div class="mb-2"><strong>接続:</strong> {{ $failedJob->connection }}</div>
                    <div class="mb-2"><strong>キュー:</strong> {{ $failedJob->queue }}</div>
                    <div class="mb-2"><strong>Payload:</strong>
                        <pre class="whitespace-pre-wrap bg-gray-100 p-2 rounded dark:bg-gray-800">{{ $failedJob->payload }}</pre>
                    </div>
                    <div class="mb-2"><strong>エクセプション:</strong>
                        <pre class="whitespace-pre-wrap bg-gray-100 p-2 rounded dark:bg-gray-800">{{ $failedJob->exception }}</pre>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush
</x-app-layout>
