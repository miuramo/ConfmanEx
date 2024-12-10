<x-app-layout>
    {{-- <x-slot name="header"> --}}
        {{-- <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400"> --}}
            {{-- {{ $apaper->paper->id_03d() }} {{ $apaper->paper->title }} --}}
        {{-- </h2> --}}
    {{-- </x-slot> --}}
    <div class="m-2">
        @for ($i = 1; $i <= $apaper->file->pagenum; $i++)
            @php
            if ($i == $page) $color = 'yellow';
            else if ($apaper->annots->where('page', $i)->where('user_id', '!=',auth()->id())->count() > 0) $color = 'cyan';
            else $color = 'gray';
            @endphp
            <x-element.linkbutton href="{{ route('annot.showpage', ['annot' => $apaper->id, 'page' => $i]) }}"
                color="{{$color}}">page{{ $i }}
            </x-element.linkbutton>
        @endfor
    </div>

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
        @keyframes flash {
            0% {
                background-color: #0af0f8;
            }
            100% {
                background-color: #bfdbfe;
            }
        }
        .flash-success {
            animation: flash 1.3s ease-in-out;
        }
    </style>
    @section('title', 'AnnotPaper ' . $apaper->paper->id_03d() . ($apaper->is_public ? '' : ' (private)'))
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    {{-- @for ($page = 1; $page <= $apaper->file->pagenum; $page++)
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page'=>$page, 'hash' => substr($apaper->file->key, 0, 8)]) }}"
    title="page {{$page}}" loading="lazy" class="flex-shrink-0 border">
@endfor --}}

    <!-- 元の画像 -->
    <img src="{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page' => $page, 'hash' => substr($apaper->file->key, 0, 13)]) }}"
        title="page" class="flex-shrink-0 border" id="targetImage" style="display:none;">

    <!-- 描画用のキャンバス -->
    <div class="flex items-end">
    <input class="pt-1 w-8 h-12 p-0" type="color" id="ID_textColor" value="#0033ff" title="text color">
    <button id="addTextButton" class="p-2 bg-blue-200 hover:bg-blue-500">add Text</button>
    <span class="mx-2"></span>
    <input class="pt-1 w-8 h-12 p-0" type="color" id="ID_rectColor" value="#ffffaa" title="rect color">
    <button id="addRectButton" class="mr-2 p-2 bg-blue-200 hover:bg-blue-500">add Rect</button>
    <button id="saveButton" class="mx-2 p-2 bg-blue-200 hover:bg-blue-500">Save</button>
    <button id="loadButton" class="mx-2 p-2 bg-blue-200 hover:bg-blue-500">Load</button>
    <span class="mx-2"></span>
    <x-element.linkbutton href="https://scrapbox.io/confman/AnnotPaper_%E3%81%AE%E3%81%A4%E3%81%8B%E3%81%84%E3%81%8B%E3%81%9F" color="lime" target="_blank">使い方(Scrapbox/Cosense)</x-element.linkbutton>
    </div>
    {{-- <button id="inspectButton" class="p-2 bg-blue-200 hover:bg-blue-500">Inspect</button> --}}
    <div id="canvas-container"
        style="position: relative; width: 100%; height: auto; background: url('{{ route('file.pdfimages', ['file' => $apaper->file->id, 'page' => $page, 'hash' => substr($apaper->file->key, 0, 13)]) }}') no-repeat center center; background-size: cover;">
        <canvas id="canvas" width="600" height="900"></canvas>
    </div>

    <div class="m-2">
        @for ($i = 1; $i <= $apaper->file->pagenum; $i++)
        @php
        if ($i == $page) $color = 'yellow';
        else if ($apaper->annots->where('page', $i)->where('user_id', '!=',auth()->id())->count() > 0) $color = 'cyan';
        else $color = 'gray';
        @endphp
        <x-element.linkbutton href="{{ route('annot.showpage', ['annot' => $apaper->id, 'page' => $i]) }}"
            color="{{$color}}">page{{ $i }}
        </x-element.linkbutton>
    @endfor

    </div>

    <pre id="output" class="invisible"></pre>
    <div id="tooltip"
        style="position: absolute; display: none; background: rgba(0, 0, 0, 0.8); color: gray; padding: 5px; border-radius: 3px; font-size: 12px; pointer-events: none;">
    </div>
    {{-- <button id="drawModeButton" style="visibility:hidden;">フリーハンド描画モード</button> --}}

    <form action="{{ route('annot.postsubmit') }}" method="post" class="invisible" id="submit_annots">
        @csrf
        @method('post')
        <input type="text" name="page" value="{{ $page }}" id="id_page">
        <input type="text" name="content" value="1" id="id_content">
        <input type="text" name="annot_paper_id" value="{{ $apaper->id }}">
    </form>

    @php
        $final = $apaper->get_fabric_objects($page);
    @endphp
    <script>
        let notes = {!! $final !!};
        const user_id = {{ Auth::id() }};
        const annotpaper_id = {{ $apaper->id }};
        const page = {{ $page }};
        const username = "{{ Auth::user()->name }}";
        const useraffil = "{{ Auth::user()->affil }}";
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/fabric@latest/dist/index.min.js"></script>
        <script src="/js/annot.js"></script>
    @endpush

</x-app-layout>
