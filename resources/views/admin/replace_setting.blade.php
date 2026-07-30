<x-app-layout>
<div class="px-4 py-6">
    <x-element.h1>設定の一括置換</x-element.h1>

    <div class="max-w-3xl mx-auto mt-4 p-4 bg-white rounded shadow">
        <p class="mb-4">settings テーブルの value に含まれる文字列を、すべて一括で置換します。</p>

        @if (session('feedback.success'))
            <div class="mb-4 text-green-700">{{ session('feedback.success') }}</div>
        @endif
        @if (session('feedback.error'))
            <div class="mb-4 text-red-700">{{ session('feedback.error') }}</div>
        @endif

        <form method="post" action="{{ route('admin.replace_setting') }}">
            @csrf
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="pre">置換前</label>
                <input id="pre" name="pre" type="text" value="{{ old('pre', $pre ?? '') }}" class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-semibold" for="post">置換後</label>
                <input id="post" name="post" type="text" value="{{ old('post', $post ?? '') }}" class="w-full border rounded px-3 py-2">
            </div>
            <x-element.submitbutton color="cyan" value="bulk_replace">一括置換</x-element.submitbutton>
        </form>
    </div>
</div>
</x-app-layout>
