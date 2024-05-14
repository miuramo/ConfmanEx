@props([
    'cat' => 1,
])
@php
    $catid = intval($cat);
    $catspans = App\Models\Category::spans();
@endphp
<!-- components.element.category -->
{!! $catspans[$catid] !!}
