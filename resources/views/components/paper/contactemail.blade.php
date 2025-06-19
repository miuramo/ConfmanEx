@props([
    'id' => null,
    'paper' => [],
])
<!-- components.paper.contactemail -->

<div class="mx-6 my-0">
    {{-- <div class="text-lg mt-2 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
        投稿連絡用メールアドレス
        <x-element.gendospan>いつでも修正可</x-element.gendospan>
    </div> --}}
    <form action="{{ route('paper.update', ['paper' => $paper->id]) }}" method="post" id="editform">
        @csrf
        @method('put')
        <div class="mx-10 mb-1">
            <label for="contact"
                class="block text-sm font-medium text-gray-900 dark:text-white">投稿連絡用メールアドレス（必要があれば修正・更新してください。1件は必須、最大{{ env('CONTACTEMAILS_MAX', 5) }}件まで、1行に1件ずつ）</label>
            <x-input-error class="mx-2 mt-2 px-1" :messages="$errors->get('ema.0')" />
            <x-input-error-md class="mx-2 mt-2 px-1" :messages="$errors->get('contactemails')" />

            <textarea id="contact" name="contactemails" rows="5"
                class="mb-1 block p-2.5 w-3/4 text-md text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                placeholder="your-secondary@email.com&#10;coauthor1@email.com&#10;coauthor2@email.com">{{ $paper->contactemails }}</textarea>
            <x-element.submitbutton color="yellow" :value="9999">
                投稿連絡用メールアドレスを更新
            </x-element.submitbutton>
            @php
                $kakunins = App\Models\Confirm::all()->pluck('name', 'id')->toArray();
            @endphp
            @foreach ($kakunins as $n => $v)
                <input type="hidden" name="{{ $v }}" value="on">
            @endforeach
        </div>
    </form>
</div>
