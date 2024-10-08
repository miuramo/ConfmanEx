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
                    $hs = ['PID','Booth','created_at','mime','pages','link','fid','origfn','valid','deleted'];
                @endphp
                @foreach ($hs as $h)
                    <th class="px-2 py-1">{{$h}}</th>
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
                        {{ $file->mime }}
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $file->pagenum }}
                    </td>
                    <td>
                        @if($file->pagenum > 0)
                        <x-file.link_pdffile :fileid="$file->id"></x-file.link_pdffile>
                        @else
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{$file->id}}
                    </td>
                    <td class="text-sm">
                        {{$file->origname}}
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{$file->valid}}
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{$file->deleted}}
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
