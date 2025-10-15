@props([
    'label' => 'スポンサー向け参加登録URLをクリップボードにコピーする',
    'size' => 'sm',
    'color' => 'purple',
])
@php
    $token = App\Models\Regist::sponsortoken();
    $regurl = route('regist.sponsor', ['token' => $token]);
@endphp
<!-- components.element.regist_sponsorlink -->
    <button onclick="copyToClipboard('{{ $regurl }}')" class="bg-{{ $color }}-500 text-white text-{{ $size }} px-3 py-1.5 rounded-md hover:bg-{{ $color }}-600 focus:outline-none focus:ring-2 focus:ring-{{ $color }}-400">
        {{$label}}
    </button>
