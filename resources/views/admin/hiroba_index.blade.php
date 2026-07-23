<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('情報学広場登録用TSVのダウンロード') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $num_max_authors = \App\Models\Submit::max_author_count();

        $check_file = !file_exists(storage_path('app/hiroba_template.xlsx'));
    @endphp


    <div class="mx-6">
        @if ($check_file)
            <span class="text-red-600">情報学広場登録用テンプレートファイル (hiroba_template.xlsx) が存在しません。<br>
                まず、テンプレートファイルをアップロードしてください。</span>
        @else
        <div class="my-4 bg-cyan-100 dark:bg-slate-600 p-4 rounded-lg">
            テンプレートファイル(xlsx) がアップロードされています。
            <x-element.linkbutton href="{{ route('admin.hiroba_template_download') }}" color="teal">
                情報学広場登録用テンプレートファイルのダウンロード
            </x-element.linkbutton>
            <br>
            著者の最大人数は {{ $num_max_authors }} 人です。<br>

            <x-element.linkbutton href="{{ route('admin.hiroba_tsv') }}" color="lime">
                情報学広場登録用データ(TSV)の閲覧
            </x-element.linkbutton>


            <x-element.linkbutton href="{{ route('admin.hiroba_excel') }}" color="teal">
                情報学広場登録用データ(TSV)のダウンロード
            </x-element.linkbutton>

            <form action="{{ route('admin.hiroba_template_delete') }}" method="POST"
                onsubmit="return confirm('本当に削除しますか？');">
                @csrf
                @method('DELETE')
                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded-lg text-sm">
                    情報学広場登録用テンプレートファイルの削除
                </button>
            </form>
        </div>
        @endif
        <div class="my-4 bg-slate-300 dark:bg-slate-600 p-4 rounded-lg">
            テンプレートファイル(xlsx)のアップロード／差し替え
            <form action="{{ route('admin.hiroba_template_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="template_file" accept=".xlsx" required>
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">アップロード</button>
            </form>
        </div>

    </div>
</x-app-layout>
