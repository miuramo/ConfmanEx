<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            PDFサムネイルキャッシュの一括削除
        </h2>
    </x-slot>

    <div class="px-4 py-4 max-w-3xl mx-auto">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        <x-element.h1>PDFサムネイルキャッシュの一括削除</x-element.h1>

        <div class="py-2 text-gray-900 dark:text-gray-100">
            現在のキャッシュ：
            {{ number_format($stats['count']) }} ファイル /
            {{ number_format($stats['bytes'] / 1024 / 1024, 1) }} MB
        </div>
        <p class="text-sm text-gray-600 dark:text-gray-400 py-2">
            各ページのサムネイル画像（t-*.png）をすべて削除します。削除しても、閲覧時にオンデマンドで再生成されます。
        </p>

        <form action="{{ route('admin.purge_pdf_thumbs.post') }}" method="post" class="py-4">
            @csrf
            <x-element.submitbutton color="red" value="purge"
                confirm="PDFサムネイルキャッシュをすべて削除します。よろしいですか？">
                キャッシュを一括削除
            </x-element.submitbutton>
        </form>
    </div>
</x-app-layout>
