<td class="text-center bg-{{ $catcolors[$cid] }}-50 select-none" wire:dblclick="editAccept()">
    @if ($judges[$accept_id] > 0)
        <span class="text-blue-600 font-bold">
        @else
            <span class="text-slate-400">_
    @endif
    {{ $accepts[$accept_id] }}
    </span>
    @if ($isEditing === true)
        <div class="mt-1">
            <select wire:model.live="accept_id" class="border border-gray-300 rounded px-1 py-0.5">
                @foreach ($accepts as $aid => $aname)
                    <option value="{{ $aid }}">{{ $aname }}</option>
                @endforeach
            </select>
        </div>
    @endif

</td>
