<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            PdfJob を再実行
        </h2>
    </x-slot>

    <div class="px-4 py-4 max-w-3xl mx-auto">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        <x-element.h1>PdfJob を再実行</x-element.h1>
        <form action="{{ route('admin.redispatch_pdf_job.post') }}" method="post" class="py-4">
            @csrf
            <label for="file_id" class="block text-sm font-medium text-gray-900 dark:text-white">file_id</label>
            <input id="file_id" name="file_id" type="number" min="1" required value="{{ old('file_id') }}"
                class="mt-1 block w-full p-1 text-sm text-gray-900 bg-gray-50 rounded-lg dark:bg-slate-800 dark:text-white border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:border-gray-600">
            @error('file_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <div class="mt-4">
                <x-element.submitbutton color="slate" value="submit">
                    再実行キューへ投入
                </x-element.submitbutton>
            </div>
        </form>
    </div>
</x-app-layout>