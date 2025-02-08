@props([
    'form' => '',
    'color' => 'orange',
    'future' => 'cyan',
    'bbs' => [],
])
@php
    $nisuru['orange'] = '対応未完了';
    $nisuru['cyan'] = '対応済み';
@endphp
<!-- components.bb.needreply  -->
<div class="mx-8 mb-2">
    <x-element.submitbutton value="submit" color="{{ $future }}" form="{{$form}}">
        チェックをつけた掲示板を「{{ $nisuru[$future] }}」にする
    </x-element.submitbutton>
    <span class="mx-4"></span>
    <x-element.button onclick="CheckAll('{{$form}}')" color="yellow" size="sm" value="すべてチェック">
    </x-element.button>
    &nbsp;
    <x-element.button onclick="UnCheckAll('{{$form}}')" color="gray" size="sm" value="すべてチェック解除">
    </x-element.button>
</div>

<table class="min-w-full m-2 border-collapse border border-gray-300 sortable" id="need_reply_{{ $color }}">
    <thead>
        <tr class="bg-{{$color}}-200">
            <th class="p-1 unsortable">chk</th>
            <th class="p-1">PID</th>
            <th class="p-1">タイトル</th>
            <th class="p-1">記事数</th>
            <th class="p-1">最終記事投稿日時</th>
        </tr>
    </thead>
    @foreach ($bbs as $bb)
        @if ($bb->needreply == ($color=='orange'))
            @isset($bb->paper)
                <tr class="border border-gray-300 hover:bg-white">
                    <td class="text-center">
                        <input type="checkbox" name="bbids[]" value="{{ $bb->id }}" id="bbids_{{ $bb->id }}"
                            class="bg-{{$color}}-100">
                    </td>
                    <td class="text-center">
                        <label for="bbids_{{ $bb->id }}" class="hover:bg-{{$color}}-200">
                            {{ $bb->paper->id_03d() }}
                        </label>
                    </td>
                    <td class="text-sm">
                        <label for="bbids_{{ $bb->id }}" class="hover:bg-{{$color}}-200">
                            {{ $bb->paper->title }}
                        </label>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('bb.show', ['bb' => $bb->id, 'key' => $bb->key]) }}"
                            class="hover:bg-pink-200 border border-pink-200 px-1 py-0.5 text-sm" target="_blank">
                            {{ $bb->nummessages() }} mes
                        </a>

                    </td>
                    <td class="text-xs text-center">
                        {{ $bb->last_message->created_at }}
                    </td>
                </tr>
            @endisset
        @endif
    @endforeach
</table>
<div class="mx-8 mb-2">
    <x-element.submitbutton value="submit" color="{{ $future }}" form="bb_needreply">
        チェックをつけた掲示板を「{{ $nisuru[$future] }}」にする
    </x-element.submitbutton>
    <span class="mx-4"></span>
    <x-element.button onclick="CheckAll('{{$form}}')" color="yellow" size="sm" value="すべてチェック">
    </x-element.button>
    &nbsp;
    <x-element.button onclick="UnCheckAll('{{$form}}')" color="gray" size="sm" value="すべてチェック解除">
    </x-element.button>
</div>
