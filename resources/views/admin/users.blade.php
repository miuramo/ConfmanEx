<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('ユーザ管理') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6">

        <x-element.h1>
            last_login_at の更新
        </x-element.h1>
        <livewire:man-user-last-login/>

        <x-element.h1>
            役職がないユーザを確認→ソフトデリート
        </x-element.h1>
        <livewire:man-user-norole/>
    </div>
    @push('localjs')
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
