@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Paper List') }}
        </h2>
    </x-slot>

    <div class="py-4">
        {{-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($roles as $role)
                <span>
                    <x-element.linkbutton href="{{ route('role.top', ['role' => $role->name]) }}" color="cyan">
                        {{ $role->desc }}
                    </x-element.linkbutton>
                </span>
            @endforeach
        </div> --}}
    </div>
    <div class="px-6 py-2">
        <form action="{{ route('admin.paperlist') }}" method="post" id="admin_paperlist">
            @csrf
            @method('post')
            @foreach ($cats as $catid => $catname)
                <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                    id="label{{ $catid }}" class="text-{{ $catcolors[$catid] }}-200"
                    @isset($targets[$catid])
            checked="checked"
            @endisset> <label
                    for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
            @endforeach
            <x-element.submitbutton value="view" color="green">←選択カテゴリの投稿論文リスト
            </x-element.submitbutton>
            <x-element.submitbutton value="excel" color="teal">←選択カテゴリのExcel Download
            </x-element.submitbutton>
        </form>
    </div>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    {{-- <div class="mx-10 py-4">
        <x-element.linkbutton href="{{ route('admin.paperlist') }}" color="green">
            投稿論文リスト
        </x-element.linkbutton>
        <x-element.linkbutton href="{{ route('admin.paperlist_excel') }}" color="teal">
            Excel Download
        </x-element.linkbutton>
    </div> --}}

    <div class="mx-10 py-4">
        <x-admin.papertable :all="$all" :enqans="$enqans">
        </x-admin.papertable>

    </div>

</x-app-layout>
