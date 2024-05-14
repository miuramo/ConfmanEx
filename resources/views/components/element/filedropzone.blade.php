@props([
    'id' => 'drop_zone',
    'color' => 'lime',
    'paper_id' => 999,
])
@php
    if (!function_exists('return_bytes')) {
        function return_bytes($val)
        {
            $val = trim($val);
            $last = strtolower($val[strlen($val) - 1]);
            $num = substr($val, 0, -1);
            switch ($last) {
                // 'G' も使えます。
                case 'g':
                    $num *= 1024;
                case 'm':
                    $num *= 1024;
                case 'k':
                    $num *= 1024;
            }
            return $num;
        }
    }
    $id_03d = sprintf('%03d', $paper_id);
@endphp
<form action="{{ route('file.store') }}" method="post" id="imgupform" enctype="multipart/form-data" class="text-sm dark:text-gray-300">
    @csrf
    @method('post')
    <label for="upfile">または、ファイル選択:</label>
    <input type="file" name="upfile" id="upfile">
    {{-- <x-element.submitbutton>
        Upload
    </x-element.submitbutton> --}}
</form>
<!-- file drop area: resources/views/components/element/filedropzone.blade.php -->
<div id="drop_zone"
    class="bg-{{ $color }}-300 hover:bg-lime-200 py-2 px-10 xs:text-lg sm:text-2xl md:text-3xl lg:text-4xl text-green-700 motion-safe:hover:scale-[1.02] transition-all duration-250  dark:bg-lime-500">
    Drop Files Here for PaperID : {{ $id_03d }}
</div>
<progress id="progressbar" value="0" max="100" style="width:100%; height:20px;"></progress>
<div class="w-full h-4 mb-4 bg-gray-200 rounded-full dark:bg-gray-700">
    <div id="progressdiv" class="h-4 bg-blue-600 rounded-full dark:bg-blue-500" style="width: 0%">
    </div>
</div>
<div id="loaded_n_total" class="text-xl text-{{ $color }}-700"></div>
<div id="status" class="text-xl text-{{ $color }}-700"></div>
{{-- <div id="fileinfo" class="text-xl text-lime-700">(info)</div> --}}
<script>
    const upload_max_filesize = {{ return_bytes(ini_get('upload_max_filesize')) }};
    const post_max_size = {{ return_bytes(ini_get('post_max_size')) }};
    const paper_id = {{ $paper_id }};
</script>
