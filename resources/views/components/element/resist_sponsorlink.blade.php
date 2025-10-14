@props([
    'label' => 'スポンサー参加登録',
    'size' => 'md',
    'color' => 'pink',
])
@php
    $token = App\Models\Regist::sponsortoken();
    $regurl = route('regist.sponsor', ['token' => $token]);
@endphp
<!-- components.element.regist_sponsorlink -->
    <x-element.linkbutton href="{{ $regurl }}" target="_blank" color="{{ $color }}" size="{{ $size }}">
        {{$label}}
    </x-element.linkbutton>
