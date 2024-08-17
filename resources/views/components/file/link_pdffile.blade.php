@props([
    'fileid' => null,
])
@php
    $file = App\Models\File::find($fileid);
@endphp
<!-- components.file.link_pdffile -->
@isset($file->key)
<a class="p-1 px-2 rounded-md bg-yellow-300 hover:bg-orange-300"
href="{{route("file.showhash",['file'=>$fileid, 'hash'=>substr($file->key,0,12)])}}" target="_blank">PDF</a>
@endisset

