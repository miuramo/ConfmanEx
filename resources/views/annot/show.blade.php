<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ $apaper->paper->id_03d() }} {{ $apaper->paper->title }}
        </h2>
    </x-slot>

    <style>
        canvas {
            border: 1px solid black;
        }

        pre {
            white-space: pre-wrap;
            /* 自動改行を有効にする */
            word-wrap: break-word;
            /* 長い単語を折り返す */
        }
    </style>
    @section('title', 'AnnotPaper ' . $apaper->paper->id_03d())
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $page = 2;
    @endphp
    {{-- @for ($page = 1; $page <= $apaper->file->pagenum; $page++)
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page'=>$page, 'hash' => substr($apaper->file->key, 0, 8)]) }}"
    title="page {{$page}}" loading="lazy" class="flex-shrink-0 border">
@endfor --}}

    <!-- 元の画像 -->
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page' => $page, 'hash' => substr($apaper->file->key, 0, 8)]) }}"
        title="page" class="flex-shrink-0 border" id="targetImage" style="display:none;">

    <!-- 描画用のキャンバス -->
    <button id="drawModeButton">フリーハンド描画モード</button>
    <button id="addTextButton" class="p-2 bg-blue-200 hover:bg-blue-500">add Text</button>
    <button id="exportButton">Save</button>
    <button id="importButton">Load</button>
    <canvas id="canvas" width="600" height="900"></canvas>
    <pre id="output"></pre>

    <form action="{{ route('annot.postsubmit') }}" method="post" class="invisible" id="submit_annots">
        @csrf
        @method('post')
        <input type="text" name="page" value="{{ $page }}" id="id_page">
        <input type="text" name="content" value="1" id="id_content">
        <input type="text" name="annot_paper_id" value="{{ $apaper->id }}">
        {{-- <button type="submit" class="p-2 bg-blue-200 hover:bg-blue-500">Save</button> --}}
    </form>


    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fabric@latest/dist/index.min.js"></script>
        <script src="/js/annot.js"></script>
    @endpush

</x-app-layout>
