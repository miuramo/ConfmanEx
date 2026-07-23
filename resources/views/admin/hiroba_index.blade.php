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
                <x-element.linkbutton href="{{ route('admin.hiroba_template_download') }}" color="cyan">
                    テンプレートファイルのダウンロード
                </x-element.linkbutton>

                <form action="{{ route('admin.hiroba_template_delete') }}" method="POST"
                    onsubmit="return confirm('本当に削除しますか？');" class="inline-block ml-4">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white py-2 px-4 rounded-lg text-sm">
                        テンプレートファイルの削除
                    </button>
                </form>

                <div class="my-2 mx-2">
                    <x-element.linkbutton href="{{ route('admin.hiroba_tsv') }}" color="lime">
                        情報学広場登録用データ(TSV)の閲覧
                    </x-element.linkbutton>
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('admin.hiroba_excel', ['is_tsv' => 1]) }}" color="teal">
                        情報学広場登録用データ(TSV)のダウンロード
                    </x-element.linkbutton>
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('admin.hiroba_excel', ['is_tsv' => 0]) }}" color="teal">
                        情報学広場登録用データ(Excel)のダウンロード
                    </x-element.linkbutton>

                    <div class="mt-4 text-sm text-gray-400">最大著者人数は {{ $num_max_authors }} 人でした。</div>
                </div>

            </div>
        @endif
        <div class="my-4 bg-slate-300 dark:bg-slate-600 p-4 rounded-lg">
            テンプレートファイル(xlsx)のアップロード／差し替え
            <form action="{{ route('admin.hiroba_template_upload') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="file" name="template_file" accept=".xlsx" required>
                <x-element.submitbutton color="cyan">アップロード</x-element.submitbutton>
            </form>
        </div>

    </div>
</x-app-layout>
