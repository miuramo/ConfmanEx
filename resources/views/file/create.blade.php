<!-- file.create -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('Upload File') }}
        </h2>
        upload_max_filesize: {{ ini_get('upload_max_filesize') }}
        / post_max_size: {{ ini_get('post_max_size') }}
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-12 px-6">
        <form action="{{ route('file.store') }}" method="post" id="imgupform" enctype="multipart/form-data">
            @csrf
            @method('post')

            <label for="file">ファイル選択:</label>
            <input type="file" name="file" id="file">
            <x-element.submitbutton>
                Upload
            </x-element.submitbutton>

        </form>
    </div>
    <div class="py-12 px-6">
        <x-element.filedropzone color="lime"></x-element.filedropzone>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/drop_zone_upload.js"></script>
    @endpush
</x-app-layout>
