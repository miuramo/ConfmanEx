@props([
    'fileid' => null,
    'label' => null,
    'linktype' => 'button',
    'check' => null,
])
@php
    $file = App\Models\File::find($fileid);
    if ($label == null) {
        $label = strtoupper($file->extension());
    } elseif ($label == 'origname') {
        $label = $file->origname;
    }
@endphp
<!-- components.file.link_pdffile -->
@if ($linktype == 'button')
    <a class="p-1 px-2 rounded-md bg-yellow-300 hover:bg-orange-300"
        href="{{ route('file.showhash', ['file' => $fileid, 'hash' => substr($file->key, 0, 12)]) }}"
        target="_blank">{{ $label }}</a>
@elseif($linktype == 'link')
    <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
        href="{{ route('file.showhash', ['file' => $fileid, 'hash' => substr($file->key, 0, 12)]) }}"
        target="_blank">{{ $label }}</a>
@endif
@if (is_array($check))
    @foreach ($check as $c=>$v)
        @if ($file->{$c} == 0)
            <span class="bg-green-200 text-sm">{{ $v }}</span>
        @endif
    @endforeach
    @if($file->deleted)
        <span class="bg-red-500">deleted</span>
    @endif
    @if($file->pending)
        <span class="bg-orange-400">pending</span>
    @endif

@endif
