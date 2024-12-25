@props([
    'paper' => null,
])
<!-- components.paper.authorlist -->
@php
$koumoku = App\Models\Paper::mandatory_bibs(); //必須書誌情報    
@endphp

<table class="border-cyan-500 border-2">
    @foreach ($koumoku as $k => $v)
        <tr
            class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
            <td class="px-2 py-1 whitespace-nowrap">{{ $v }}</td>
            @if (strlen($paper->{$k}) < 2)
                <td class="px-2 py-1 text-red-600 font-bold"
                    id="confirm_{{ $k }}">（未設定）</td>
            @else
                <td class="px-2 py-1" id="confirm_{{ $k }}">
                    {!! nl2br($paper->{$k}) !!}</td>
            @endif
        </tr>
    @endforeach
</table>