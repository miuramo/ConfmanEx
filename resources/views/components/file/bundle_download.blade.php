@props([
    'def_cat' => 1,
    'def_fts' => 'pdf',
])
@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
@endphp
<!-- components.file.adoption -->
<div class="px-6 py-0 flex">
    <div class="px-2 py-0 flex-grow">
        @php
            $fts = ['pdf', 'img', 'video', 'altpdf'];
        @endphp
        <form action="{{ route('pub.zipdownload') }}" method="post" id="pub_zipdownload">
            @csrf
            @method('post')
            <div>
                @foreach ($cats as $catid => $catname)
                    <input type="radio" name="targetcat" value="{{ $catid }}" id="label{{ $catid }}"
                        @if ($catid == $def_cat) checked="checked" @endif>
                    <label for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>&nbsp;
                @endforeach
            </div>
            <div>
                @foreach ($fts as $ft)
                    <input type="checkbox" name="filetype{{ $ft }}" value="{{ $ft }}"
                        id="label{{ $ft }}" @if ($ft == $def_fts) checked="checked" @endif>
                    <label for="label{{ $ft }}" class="dark:text-gray-300">{{ $ft }}</label>&nbsp;
                @endforeach
            </div>
            <div class="bg-orange-100 p-2">
                <input type="radio" name="fn_field" value="booth" id="labeluse_booth">
                <label for="labeluse_booth"
                    class="dark:text-gray-300 hover:bg-orange-200">ブース記番を使用する（注：未定義の場合、「pid+PaperID」を使用します）</label>&nbsp;
                <span class="mx-2"></span>
                <input type="radio" name="fn_field" value="serialnum" id="labeluse_serialnum" checked="checked">
                <label for="labeluse_serialnum" class="dark:text-gray-300 hover:bg-orange-200">ブース記番の代わりに、シリアル値を使用する</label>
            </div>
            <div class="dark:text-gray-400">
                ファイル名は、Prefix→ <input type="text" name="fn_prefix"
                    value="{{ env('PUB_DL_PREFIX', 'IPSJ-SSS2024') }}" class="p-1 dark:bg-slate-600"> +
                [ブース記番].pdf になります。ファイル名が重複するため、pdf と altpdf は同時に選択しないでください。
            </div>

            <x-element.submitbutton value="view" color="yellow">↑選択したカテゴリ・種別の採択ファイルをDownload
            </x-element.submitbutton>
        </form>

    </div>
</div>
