@props([
    'itm' => [],
    'current' => [],
    'loop' => 0,
])
<!-- components.enquete.itmview -->
@php
    $ary = explode(App\Models\Viewpoint::$separator, $itm->content);
    $item_title = nl2br(trim($ary[0]));
    $type = trim($ary[1]);
    $sel = array_map('trim', array_slice($ary, 2));

    if ($type == 'textarea' && is_string($current)) {
        $current = nl2br($current);
        if (strlen($current) < 1) {
            $current = null;
        }
    }
    $after = nl2br($itm->contentafter);
@endphp
<tr
    class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200' : 'bg-white-50 dark:bg-slate-400' }}">
    <td nowrap class="p-2">
        {{ $itm->desc }} →</td>
    <td id="{{ $itm->name }}_answer" class="text-lg p-2">
        @if (isset($current) && !is_array($current))
            {!! $current !!}
        @else
            <span class="text-red-600 font-extrabold text-sm">(未入力)</span>
        @endif
    </td>
</tr>
