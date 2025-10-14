@props([
    'rev' => null,
    'bbmes_id' => null,
    'bb_id' => null,
    'label' => '掲示板',
])
@php
    if ($rev) {
        $bburl = App\Models\Bb::url_from_rev($rev);
    } elseif ($bb_id) {
        $bburl = App\Models\Bb::url_from_bbid($bb_id);
    } elseif ($bbmes_id) {
        $bburl = App\Models\Bb::url_from_bbmesid($bbmes_id);
    }
@endphp
<!-- components.element.bblink -->
@isset($bburl)
    <x-element.linkbutton href="{{ $bburl }}" target="_blank" color="pink">
        {{$label}}
    </x-element.linkbutton>
@else
    <div class="m-2 p-2 bg-pink-200 text-sm">掲示板は準備中です。</div>
@endisset
