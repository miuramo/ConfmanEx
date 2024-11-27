@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>
    <!-- pub.bibinfo -->
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
            <span class="mx-4"></span>
            <span class="bg-gray-100 p-4 rounded-lg">
                表示を切り替える：
                @foreach ($cats as $cid => $catname)
                    <a href="{{ route('pub.bibinfo', ['cat' => $cid, 'abbr' => ($abbr?1:0), 'filechk' => $filechk]) }}">
                        <x-element.category :cat="$cid" size="sm">
                        </x-element.category>
                    </a>
                @endforeach
            </span>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('プログラム出力') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>
        </h2>
    </x-slot>
    <div class="px-4 py-4">
        @foreach ([0 => 'しない', 1 => 'する'] as $ab => $abtxt)
            <x-element.linkbutton2 href="{{ route('pub.bibinfo', ['cat' => $catid, 'abbr' => $ab, 'filechk' => $filechk]) }}" color="lime"
                size="sm">
                連続する同一所属を省略{{ $abtxt }}
            </x-element.linkbutton2>
        @endforeach
        <span class="mx-2"></span>
        @foreach ([0 => 'しない', 1 => 'する'] as $fc => $fctxt)
            <x-element.linkbutton2 href="{{ route('pub.bibinfo', ['cat' => $catid, 'abbr' => ($abbr?1:0), 'filechk' => $fc]) }}" color="teal"
                size="sm">
                ファイルへのリンクを表示{{ $fctxt }}
            </x-element.linkbutton2>
        @endforeach
    </div>

    <div class="px-4 py-4">
        @php
            $psessionid = 0;
            $check = ['valid' => 'invalid', 'locked' => 'unlocked'];
        @endphp
        @foreach ($subs as $sub)
            @if ($psessionid != $sub->psession_id)
                <div class="mt-4 mb-2">セッション{{ $sub->psession_id }}：</div>
            @endif
            <div>({{ $sub->booth }}) {{ $sub->paper->title }}</div>
            <div class="mx-7"> {{ $sub->paper->bibauthors($abbr) }}
                @if ($filechk == 1)
                    {{-- ファイルチェック --}}
                    @if ($sub->paper->pdf_file)
                        <x-file.link_anyfile :fileid="$sub->paper->pdf_file_id" label="PDF" linktype='link' :check="$check" />
                    @endif
                    @if ($sub->paper->img_file)
                        <x-file.link_anyfile :fileid="$sub->paper->img_file_id" label="IMG" linktype='link' :check="$check" />
                    @endif
                    @if ($sub->paper->video_file)
                        <x-file.link_anyfile :fileid="$sub->paper->video_file_id" label="Video" linktype='link' :check="$check" />
                    @endif
                @endif
            </div>
            @php
                $psessionid = $sub->psession_id;
            @endphp
        @endforeach

        <div class="my-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>

    </div>

</x-app-layout>
