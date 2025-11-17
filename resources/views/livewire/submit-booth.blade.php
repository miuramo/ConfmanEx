<td class="text-center bg-{{ $catcolors[$cid] }}-100" wire:dblclick="editBooth()">
    {{ $booth }}
        @if ($isEditing === true)
        <div class="mt-1">
            <input wire:model.live.debounce.1000ms="booth" class="border border-gray-300 rounded px-1 py-0.5">
        </div>
    @endif
</td>
    