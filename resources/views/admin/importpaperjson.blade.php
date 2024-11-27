@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Import PaperJSON') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @if($updated)
        <x-alert.success>Updated</x-alert.success>
    @endif

    <div class="py-2 px-2">
        <div class="mx-2 py-4">
            <form action="{{ route('admin.importpaperjson') }}" method="post" id="importpaperjson">
                @csrf
                @method('post')
                <div class="mb-1">
                    <textarea name="paperjson" class="w-full text-sm mt-1 p-2" cols="30" rows="15">{{$src}}</textarea>
                    <x-element.submitbutton color="cyan" value="check">
                        check
                    </x-element.submitbutton>
                    <span class="mx-2"></span>
                    <x-element.submitbutton color="orange" value="doreplace" confirm="本当に？">
                        doreplace
                    </x-element.submitbutton>
                </div>
            </form>
        </div>

        <div class="mx-2 py-4">
            <x-element.h1>現状設定されているタイトルとの差分</x-element.h1>
            <dix class="my-2"></dix>
            <pre class="text-sm">{{$out}}</pre>
        </div>

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
    </div>

</x-app-layout>
