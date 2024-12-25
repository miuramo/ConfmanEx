@php
    $cats = App\Models\Category::manage_cats();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            {{-- <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton> --}}
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('投稿とファイルのタイムスタンプ') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat_id">
            </x-element.category>
        </h2>
    </x-slot>

    <div class="py-1">
    </div>
    <div class="px-6 py-2">切り替えリンク：
        @foreach ($cats as $catid => $catname)
            <a href="{{ route('admin.timestamp', ['cat' => $catid]) }}">
                <x-element.category :cat="$catid" size="sm">
                </x-element.category>
            </a>
            <span class="mx-1"></span>
        @endforeach
    </div>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <form action="{{ route('admin.timestamp', ['cat' => $cat_id]) }}" method="post" id="admin_timestamp">
        @csrf
        @method('post')
        <div class="mx-10 py-4">
            <x-admin.papertable_withtimestamp :all="$all">
            </x-admin.papertable_withtimestamp>

            {{-- <div class="my-2"></div>
            チェックを入れた投稿を
            <span class="mx-1"></span>
            <x-element.submitbutton value="revoke" color="cyan">復活する
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton value="delete" color="red">論理削除する
            </x-element.submitbutton> --}}

        </div>
    </form>

    @push('localjs')
        <script src="/js/sortable.js"></script>
    @endpush

</x-app-layout>
