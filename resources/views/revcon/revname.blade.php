<x-app-layout>
    <!-- revcon.revstat -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読者一覧と利害表明者') }}

            <span class="mx-2"></span>

            <x-element.category :cat="$cat->id" />

                @php
                    $cat_id = $cat->id;
                @endphp
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-4 px-2  dark:text-gray-400">
        <x-review.revname_table :cat_id="$cat_id">
        </x-review.revname_table>
    </div>


</x-app-layout>
