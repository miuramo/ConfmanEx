@php
    if (!isset(self::$judges[$accept_id])) {
        Cache::forget('judges');
    }
    if (!isset(self::$accepts[$accept_id])) {
        Cache::forget('accepts');
    }
    if (!isset(self::$catcolors[$cid])) {
        Cache::forget('catcolors');
    }
@endphp
<td class="text-center bg-{{ self::$catcolors[$cid] }}-50 select-none" wire:dblclick="editAccept()"
    wire:keydown.escape="cancelEdit()">
    @if ($accept_id == 0)
    @else
        @if (!isset(self::$judges[$accept_id]) || !isset(self::$accepts[$accept_id]))
            キャッシュをクリアしました。ページを再読み込みしてください。
            <button wire:click="reloadPage()" class="mt-1 p-2 rounded-md bg-purple-300 hover:bg-purple-500">再読み込み</button>
        @else
            @if (self::$judges[$accept_id] > 0)
                <span class="text-blue-600 font-bold">
                @else
                    <span class="text-slate-400">_
            @endif
            {{ self::$accepts[$accept_id] }}
        @endif
        </span>
    @endif
    @if ($isEditing === true)
        @if ($submit_id == 0)
            <div class="text-red-600 font-bold">注意👀新規作成！</div>
            [ESC]キーでキャンセル
        @endif
        <div class="mt-1">
            <select wire:model.live="accept_id" class="border border-gray-300 rounded px-1 py-0.5"
                wire:keydown.escape="cancelEdit()" x-init="$el.focus()">
                @foreach (self::$accepts as $aid => $aname)
                    <option value="{{ $aid }}">{{ $aname }}</option>
                @endforeach
            </select>
        </div>
        @if ($accept_id == 20 && $canDelete)
            <button wire:click="deleteSubmit()"
                class="mt-1 p-2 rounded-md bg-purple-300 hover:bg-purple-500">削除</button>
        @endif
    @endif

</td>
