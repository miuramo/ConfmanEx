<x-app-layout>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <x-slot name="header">
        {{-- <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>

        </div> --}}
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <x-element.paperid :paper_id="$paper->id"></x-element.paperid>
            <span class="mx-4"></span>
            {{ __('論文ごとの採択ファイルの確認') }}
            <span class="mx-4"></span>
            @isset($bb)
            <x-element.bblink :bb_id="$bb->id" label="出版掲示板 for {{ $paper->id_03d() }}"></x-element.bblink>
            @endisset
            <div
                class="text-lg mt-4 font-bold bg-slate-200 py-2 px-4 inline-block rounded-md dark:text-slate-200 dark:bg-slate-500">
                {{ $paper->title }}</div>
        </h2>
        <style>
            .hidden-content {
                /* display: none; */
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="px-4 py-4">
        <table class="border-lime-400 border-2">
            <tr class="bg-lime-300">
                @php
                    $hs = ['chk', 'ファイルの採用状況', 'created_at', 'origname', 'pages', '掲示板からのアップロード','file_id','属性'];
                @endphp
                @foreach ($hs as $h)
                    <th class="px-2 py-1">{{ $h }}</th>
                @endforeach
            </tr>
            @foreach ($files as $file)
            <tr
            class="
        @php
if($file->valid && $file->deleted == 0) { 
            echo ($loop->iteration % 2 === 0 ? 'bg-lime-100' : 'bg-lime-50');
        }
         else {
            if (!$file->valid){
                echo 'bg-gray-300';
            } else if($file->deleted) echo 'bg-gray-200';
       
         } @endphp
        ">
                    <td class="px-2 py-1 text-center">
                        <input type="radio" name="file_id" form="fileadopt" value="{{ $file->id }}">
                    </td>
                    <td class="px-2 py-1 text-center font-bold">
                        @if ($paper->pdf_file_id == $file->id)
                            PDFとして採用中
                        @endif
                        @if ($paper->img_file_id == $file->id)
                            IMGとして採用中
                        @endif
                        @if ($paper->video_file_id == $file->id)
                            Videoとして採用中
                        @endif
                    </td>
                    <td class="px-2 py-1">
                        {{ $file->created_at }}
                    </td>
                    <td class="text-sm">
                        <x-file.link_anyfile :fileid="$file->id" label="origname" linktype="link"></x-file.link_anyfile>
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if ($file->mime == 'application/pdf')
                            {{ $file->pagenum }}
                        @else
                            ---
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if ($file->bb_mes_id)
                            <x-element.bblink :bbmes_id="$file->bb_mes_id"
                                label="出版掲示板 for {{ sprintf('%03d',$file->paper_id) }}"></x-element.bblink>
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $file->id }} 
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if($file->deleted)
                            <span class="bg-red-300 text-red-800 px-2 py-1 rounded-lg">Deleted</span>
                        @endif
                        @if($file->pending)
                            <span class="bg-purple-500 text-white px-2 py-1 rounded-lg">Pending</span>
                        @endif
                        @if(!$file->valid)
                            <span class="bg-yellow-300 text-yellow-800 px-2 py-1 rounded-lg">Invalid</span>
                        @endif
                        @if($file->locked)
                            <span class="bg-green-300 text-green-800 px-2 py-1 rounded-lg">Locked</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

        @php
            $file_desc = App\Models\Setting::getval('FILE_DESCRIPTIONS');
            $file_desc = json_decode($file_desc);
        @endphp
        <form action="{{ route('paper.fileadopt', ['paper' => $paper->id]) }}" method="post" class="inline"
            id="fileadopt">
            @csrf
            @method('post')
            <input type="hidden" name="paper_id" value="{{ $paper->id }}">
            <span class="font-bold bg-yellow-300">↑↑でチェックしたファイルを、</span>
            <select name="ftype" id="filetype" class="bg-yellow-100 px-2 py-1 rounded-lg dark:text-gray-500">
                @foreach ($file_desc as $name => $desc)
                    <option value="{{ $name }}">{{ $desc }} ({{$name}})</option>
                @endforeach
            </select>
            <button type="submit" class="bg-yellow-300 hover:bg-orange-300 px-1 py-1 rounded-lg dark:text-gray-500"
                onclick="return confirm('本当に採用しますか？（ファイルおよび種別がただしいか、再度確認してください）')">←として採用する</button>
            または <x-element.submitbutton2 value="reject" size="sm" color="red" id="idreject"
                confirm="Invalid & Deletedにすることで、よろしければ、OKを押してください。">Invalid & Deletedにする</x-element.submitbutton2>
        </form>
        <div class="p-2 m-1 bg-slate-50">
        ここで採用すると、Pending属性、削除済み属性は解除され、Lock属性、Valid属性がつきます。<br>
        Invalid & Deletedにすると、Pending属性、Lock属性は解除されます。<br>
        掲示板には通知しません。必要があれば、個別に通知してください。<br>
        論文（Paper）から、参照・採用できるファイルは、種別(pdf,img,video,altpdf,etc...)ごとに一つのみです。
        </div>

        {{-- <div class="mt-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div> --}}

    </div>



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
