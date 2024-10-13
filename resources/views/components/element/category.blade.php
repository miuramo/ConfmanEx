@props([
    'cat' => 1,
    'size' => 'lg',
])
@php
    $catid = intval($cat);
    $catspans = App\Models\Category::spans();
@endphp
<!-- components.element.category -->
<span class="text-{{$size}}">
    {!! $catspans[$catid] !!}
</span>