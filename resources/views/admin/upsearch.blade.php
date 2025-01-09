<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('インクリメンタル検索') }} （ユーザ・論文）
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
        <div class="mx-2 py-4">
            <input type="text" id="search-box" placeholder="検索..." autocomplete="off">
            <ul class="m-1 p-2 bg-green-200" id="results"></ul>
            <ul class="m-1 p-2 bg-orange-200" id="presults"></ul>

        </div>

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
    </div>

    <script>
        var searchUrl = "{{ route('admin.upsearch') }}";
    </script>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/upsearch.js"></script>
    @endpush
</x-app-layout>
