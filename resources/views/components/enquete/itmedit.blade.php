@props([
    'itm' => [],
    'loop' => 0,
    'formid' => '',
    'current' => null,
])
<!-- components.enquete.itmedit -->
@php
    $ary = explode(App\Models\Viewpoint::$separator, $itm->content); //改行ではなく、セミコロン ; で区切っていることに注意
    $item_title = nl2br(trim($ary[0])); // 最初の要素は、説明
    $type = trim($ary[1]); // 次は、formの種類
    $sel = array_map('trim', array_slice($ary, 2)); // 選択肢やオプション

    if ($type == 'textarea') {
        $currentbr = nl2br($current);
        if (strlen($current) < 1) {
            $currentbr = null;
        }
    }
    $after = nl2br($itm->contentafter); // input要素のあとの説明など
@endphp
<tr
    class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200' : 'bg-white-50 dark:bg-slate-400' }}">
    <td nowrap class="p-4">
        {{ $itm->desc }} →</td>
    @if ($type == 'selection')
        <td id="{{ $itm->name }}_answer" class="text-xl p-4">
            {!! $current ?? '<span class="text-red-600 font-extrabold">(未入力)</span>' !!}</td>
        <td class="p-2 pl-10">
            {!! $item_title !!}<br>
            @foreach ($sel as $choice)
                <input type="radio" id="{{ $itm->name }}{{ $loop->iteration }}" name="{{ $itm->name }}"
                    value="{{ $choice }}" onchange="changed('{{ $formid }}','{{ $itm->name }}');"
                    @if (isset($current) && $choice == $current) checked @endif>
                <label for="{{ $itm->name }}{{ $loop->iteration }}"
                    class="hover:bg-lime-100">{{ $choice }}</label>
                &nbsp;<br>
            @endforeach
            <div class="my-3"></div>
            {!! $after !!}
        </td>
    @elseif($type == 'number')
        <td id="{{ $itm->name }}_answer" class="text-lg p-4">
            {!! $current ?? '<span class="text-red-600 font-extrabold">(未入力)</span>' !!}</td>
        <td class="p-2 pl-10">
            {!! $item_title !!}<br>
            <input type="number" id="{{ $itm->name }}{{ $loop->iteration }}" name="{{ $itm->name }}"
                onchange="changed('{{ $formid }}','{{ $itm->name }}');" value="{{ $current ?? $sel[2] }}"
                min="{{ $sel[0] }}" max="{{ $sel[1] }}">
                {{-- EnterでJSONが表示されてしまう問題に対しては、まずonkeypress ではなく、Controller.update()でリダイレクトすることによって対応
                その後、Javascript form_changed.js でkeydown処理によって対応 --}}
            <div class="my-3"></div>
            {!! $after !!}
        </td>
    @elseif($type == 'text')
        <td id="{{ $itm->name }}_answer" class="text-md p-4">
            {!! $current ?? '<span class="text-red-600 font-extrabold">(未入力)</span>' !!}</td>
        <td class="p-2 pl-10">
            {!! $item_title !!}<br>
            <input type="hidden" name="{{ $itm->name }}" value="">
            <input type="text" id="{{ $itm->name }}{{ $loop->iteration }}" name="{{ $itm->name }}"
                onblur="changed('{{ $formid }}','{{ $itm->name }}');" value="{{ $current ?? '' }}"
                size="{{ $sel[0] }}" placeholder="{{ $sel[1] }}">
            <div class="my-3"></div>
            {!! $after !!}
        </td>
    @elseif($type == 'textarea')
        <td id="{{ $itm->name }}_answer" class="text-md p-4">
            {!! $currentbr ?? '<span class="text-red-600 font-extrabold">(未入力)</span>' !!}</td>
        <td class="p-2 pl-10">
            {!! $item_title !!}<br>
            <input type="hidden" name="{{ $itm->name }}" value="">
            <textarea class="text-left" id="{{ $itm->name }}{{ $loop->iteration }}" name="{{ $itm->name }}"
                onblur="changed('{{ $formid }}','{{ $itm->name }}');" cols="{{ $sel[0] }}"
                rows="{{ $sel[1] }}" placeholder="{{ $sel[2] }}">{{ $current ?? '' }}</textarea>
            <div class="my-3"></div>
            {!! $after !!}
        </td>
    @endif
</tr>
