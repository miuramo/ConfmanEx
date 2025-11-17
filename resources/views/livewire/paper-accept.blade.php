<tr class="hover:bg-lime-100 border-b border-gray-200">
    <td class="text-center">
        {{ sprintf('%03d', $paper_id) }}
    </td>
    <td class="text-sm"> {{ $paper_title }}
    </td>
    @foreach (self::$cats as $cid => $cname)
        @if (isset($submits[$cid]))
            <td class="text-center bg-{{ self::$catcolors[$cid] }}-50 select-none"
                wire:dblclick="editAccept({{ $cid }})">

                @if (self::$judges[$submits[$cid]->accept_id] > 0)
                    <span class="text-blue-600 font-bold">
                    @else
                        <span class="text-slate-400">_
                @endif
                {{ self::$accepts[$submits[$cid]->accept_id] }}
                </span>

                @if ($this->edit_category_id === $cid)
                    <div class="mt-1">
                        <select wire:model="submits.{{ $cid }}.accept_id" class="border border-gray-300 rounded px-1 py-0.5">
                            @foreach (self::$accepts as $aid => $aname)
                                <option value="{{ $aid }}">{{ $aname }}</option>
                            @endforeach
                        </select>
                    </div>  
                @endif
            </td>
            @if (strlen($submits[$cid]->booth ?? '') > 0)
                <td class="text-center bg-{{ self::$catcolors[$cid] }}-100">
                    {{ $submits[$cid]->booth }}

                    {{-- @if ($this->edit_category_id === $cid)
                    <div class="mt-1">
                        <input type="text" wire:model="submits.{{ $cid }}.booth" class="border border-gray-300 rounded px-1 py-0.5" />
                        <button class="bg-green-500 text-white px-2 py-1 rounded ml-2"
                            wire:click="editAccept({{ $cid }}, {{ $submits[$cid]->accept_id }})">保存</button>
                    </div>
                @endif --}}
                </td>
            @else
                <td class="text-center bg-{{ self::$catcolors[$cid] }}-100">

                </td>
            @endif
        @else
            <td></td>
            <td></td>
        @endif
    @endforeach
</tr>
