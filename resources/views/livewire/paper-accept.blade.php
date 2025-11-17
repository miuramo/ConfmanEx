<tr class="hover:bg-lime-100 border-b border-gray-200">
    <td class="text-center">
        {{ sprintf('%03d', $paper_id) }}
    </td>
    <td class="text-sm"> {{ $paper_title }}
    </td>
    @foreach (self::$cats as $cid => $cname)
        @if (isset($submits[$cid]))
            <livewire:submit-accept :submit_id="$submits[$cid]->id" :accept_id="$submits[$cid]->accept_id" :cid="$cid" :key="'submit-accept-' . $submits[$cid]->id"
                :accepts="self::$accepts" :catcolors="self::$catcolors" :judges="self::$judges" />
            @if (strlen($submits[$cid]->booth ?? '') > 0)
                <livewire:submit-booth :submit_id="$submits[$cid]->id" :booth="$submits[$cid]->booth" :cid="$cid" :key="'submit-booth-' . $submits[$cid]->id"
                    :accepts="self::$accepts" :catcolors="self::$catcolors" :judges="self::$judges" />
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
