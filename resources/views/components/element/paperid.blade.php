@props([
    'size' => 2,
    'paper_id' => 0,
])
@php
    $id_03d = sprintf('%03d', $paper_id);
    $font = "text-{$size}xl px-".($size+1)." py-2";
@endphp
<!-- components.element.paperid -->
<span class=" text-gray-600 bg-gray-100 {{$font}} rounded-xl font-extrabold dark:bg-gray-400">PaperID : {{ $id_03d }}</span>
