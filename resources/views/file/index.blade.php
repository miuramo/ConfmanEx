<!-- file.index -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('Files') }}
            &nbsp;
            <x-element.linkbutton href="{{ route('file.create') }}" color="cyan">
                Upload New File</x-element.linkbutton>

            <x-element.deletebutton action="{{ route('file.delall') }}" color="red" confirm="全部削除してよいですか？"> Delete All
            </x-element.deletebutton>

        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-4 px-6">
        <x-element.filedropzone color="lime"></x-element.filedropzone>
    </div>

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="filelist" class="grid xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            {{-- @if (count($all) == 0)
                <div class="text-3xl bg-yellow-200 p-4 rounded-md text-white text-center">No File</div>
            @endif --}}
            <x-file.elem :all="$all"/>
        </div>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/drop_zone_upload.js"></script>
    @endpush

</x-app-layout>
