<td class="hover:bg-blue-100 text-center">
    @if ($type == 'tinyint(1)')
        <flux:switch wire:model.live="value" wire:key="{{ $field }}{{ $id }}"></flux:switch>
        {{ $value }}
    @elseif($type == 'text')
    @if ($isEditing)
        <textarea class="border border-blue-500 p-1 bg-cyan-50" wire:model.live="value"
            wire:keydown.cmd.enter="save" wire:keydown.escape="cancel"
            wire:key="editable-input-{{ $id }}-{{ $field }}" x-init="$el.focus()" cols="50" rows="3">{{ $value }}</textarea>
            @else
            @if ($value == '')
                <div wire:click="startEditing" class="w-full cursor-pointer ">　</div>
            @else
                <div wire:click="startEditing" class="w-full cursor-pointer ">
                    {{ $value }}</div>
            @endif
        @endif
    @else
        @if ($isEditing)
            <input class="w-full border border-blue-500 p-1 bg-cyan-50" type="text" wire:model.live="value"
                wire:keydown.enter="save" wire:keydown.escape="cancel"
                wire:key="editable-input-{{ $id }}-{{ $field }}" x-init="$el.focus()" />
        @else
            @if ($value == '')
                <div wire:click="startEditing" class="w-full cursor-pointer ">　</div>
            @else
                <div wire:click="startEditing" class="w-full cursor-pointer ">
                    {{ $value }}</div>
            @endif
        @endif
    @endif
</td>
