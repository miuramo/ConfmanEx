@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>
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
                @foreach ($cats as $catid => $catname)
                    <a href="{{ route('pub.fileinfochk', ['cat' => $catid]) }}">
                        <x-element.category :cat="$catid" size="sm">
                        </x-element.category>
                    </a>
                @endforeach
            </span>

        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('採択分ファイルの確認') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>
        </h2>
        <style>
            .hidden-content {
                /* display: none; */
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>

    <div class="px-6 pt-4 leading-relaxed">ファイルのタイムスタンプ (created_at) と、ページ数をご確認ください。
    </div>

    <div class="px-4 py-4">
        <table class="border-lime-400 border-2">
            <tr class="bg-lime-300">
                @php
                    $hs = ['PID', 'Booth', 'created_at', '拡張子', 'pages', 'fid', 'origfn', 'valid', 'deleted','PDF未採用','出版掲示板'];
                @endphp
                @foreach ($hs as $h)
                    <th class="px-2 py-1">{{ $h }}</th>
                @endforeach
            </tr>
            @foreach ($files as $file)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-lime-100' : 'bg-lime-50' }}">
                    <td class="px-2 py-1 text-center">
                        {{ sprintf('%03d', $file->paper_id) }}
                    </td>
                    <td class="px-2 py-1 text-center font-bold">
                        {{ $pid2sub[$file->paper_id]->booth }}
                    </td>
                    <td class="px-2 py-1">
                        {{ $file->created_at }}
                    </td>
                    <td class="px-2 py-1">
                        {{ $file->extension() }}
                        {{-- {{ substr($file->mime,0,15) }} --}}
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $file->pagenum }}
                    </td>
                    {{-- <td>
                        @if ($file->pagenum > 0)
                            <x-file.link_pdffile :fileid="$file->id"></x-file.link_pdffile>
                        @else
                        @endif
                    </td> --}}
                    <td class="px-2 py-1 text-center">
                        {{ $file->id }}
                    </td>
                    <td class="text-sm">
                        <x-file.link_anyfile :fileid="$file->id" label="origname" linktype="link"></x-file.link_anyfile>
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $file->valid }}
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $file->deleted }}
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if($file->mime == 'application/pdf' && $file->paper->pdf_file_id != $file->id)
                        <div class="bg-yellow-300 text-sm">PDF未採用</div>
                        @endif
                        <a class="text-sm bg-cyan-100 p-1 hover:bg-cyan-300" href="{{route('pub.paperfile', ['paper'=>$file->paper_id])}}" target="_blank">状況確認</a>
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if($file->bb_mes_id)
                            <x-element.bblink :bbmes_id="$file->bb_mes_id" label="for{{sprintf('%03d',$file->paper_id)}}"></x-element.bblink>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="mt-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>

    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
        <script src="/js/crud_table.js"></script>
        <script src="/js/crud_where.js"></script>
    @endpush
    <form action="{{ route('admin.crudpost') }}" method="post" id="admincrudpost">
        @csrf
        @method('post')
    </form>
    <script>
        var table = "papers";
        var origData = {};
        var mode_br = true; // 改行反映する
        var sizecols = 90; // 横幅
    </script>

    {{-- @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="https://d3js.org/d3.v5.min.js"></script>
        <script src="/js/d3groupedit.js"></script>
        <script src="/js/d3contextmenu.js"></script>
        <script src="/js/d3booth.js"></script>
    @endpush --}}
    {{-- <script>
        var subpapers = {!! json_encode($subs) !!};
    </script> --}}

</x-app-layout>
