@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role'=>'admin']) }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('タイトル部分切り取り画像の確認') }}
            <span class="mx-6"></span>
            <x-element.linkbutton href="{{ route('admin.paperlist_headimg_recrop') }}" color="red" size="sm">
                画像の再構成をリクエスト（時間がかかります）
            </x-element.linkbutton>

        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @php
        $catspans = App\Models\Category::spans();
    @endphp

    <div class="py-2 px-6">

        <div id="revlist" class="grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-4">

            @foreach ($all as $paper)
                <div classs="bg-slate-400 p-5">
                    <x-element.paperid size=2 :paper_id="$paper->id">
                    </x-element.paperid>


                    {!! $catspans[$paper->category_id] !!}

                    @if ($paper->pdf_file_id != null)
                        <a href="{{ route('file.altimgshow', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                    @endif
                    <x-file.paperheadimg :paper="$paper">
                    </x-file.paperheadimg>
                    @if ($paper->pdf_file_id != null)
                        </a>
                    @endif

                </div>
            @endforeach
        </div>
        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('role.top', ['role'=>'admin']) }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
    </div>

</x-app-layout>
