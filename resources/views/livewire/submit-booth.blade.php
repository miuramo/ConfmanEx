<td class="text-center bg-{{ self::$catcolors[$cid] }}-100" wire:dblclick="editBooth()" wire:keydown.escape="cancelEdit()">
    {{ $booth }}
    @if ($isEditing === true)
        <div class="mt-1">
            <input type="text" wire:model="booth" wire:keydown.enter="save" wire:keydown.escape="cancelEdit()" x-init="$el.focus()"
                class="border border-gray-300 rounded px-1 py-0.5">
        </div>
    @endif
</td>
