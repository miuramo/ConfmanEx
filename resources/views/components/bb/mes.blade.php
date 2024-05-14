@props([
    'mes' => [],
])

<!-- components.enquete.edit (呼び出し元は主に paper.edit) -->
@php
    $mes->mes = preg_replace_callback("/(<a [^>]+?>.+?<\/a>)|(https?:\/\/[a-zA-Z0-9_\.\/\~\%\:\#\?=&\;\-]+)/i", "urllink", $mes->mes);
    $mes->mes = strip_tags($mes->mes,"<a>");
@endphp

@if ($mes->user_id == auth()->id())
    <div class="text-right">
        <div class="inline-block w-3/4 bg-green-300 p-2 rounded-lg px-2 py-1 my-1">
            <div class="flex justify-between">
                <div class="mx-2">{{ $mes->subject }}</div>
                <div class="text-right text-gray-500 text-sm mr-2">{{ $mes->created_at }}</div>
            </div>
            <div class="bg-green-100 px-2 py-1 mb-1 rounded-md text-left">{!! nl2br($mes->mes) !!}</div>
        </div>
    </div>
@else
    <div class="bg-slate-300 rounded-lg w-3/4 px-2 py-1 my-1">
        <div class="flex justify-between">
            <div class="mx-2">{{ $mes->subject }}</div>
            <div class="text-right text-gray-500 text-sm mr-2">{{ $mes->created_at }}</div>
        </div>
        <div class="bg-slate-100 px-2 py-1 mb-1 rounded-md">{!! nl2br($mes->mes) !!}</div>
    </div>
@endif
