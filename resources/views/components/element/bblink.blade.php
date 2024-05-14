@props([
    'rev' => null,
])
@php
    $bburl = App\Models\Bb::url_from_rev($rev);
@endphp
<!-- components.element.bblink -->
@isset($bburl)
    <x-element.linkbutton href="{{ $bburl }}" target="_blank" color="pink">
        掲示板
    </x-element.linkbutton>
@else
    <div class="m-2 p-2 bg-pink-200">掲示板参照Error</div>
@endisset
