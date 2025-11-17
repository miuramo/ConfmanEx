<tr class="hover:bg-lime-100 border-b border-gray-200">
    <td class="text-center">
        {{ sprintf('%03d', $paper_id) }}
    </td>
    <td class="text-sm"> {{ $paper_title }}
    </td>
    @foreach (self::$cats as $cid => $cname)
        @if (isset($submits[$cid]))
            <livewire:submit-accept :submit_id="$submits[$cid]->id" :accept_id="$submits[$cid]->accept_id" :cid="$cid" :key="'submit-accept-' . $submits[$cid]->id" />
            <livewire:submit-booth :submit_id="$submits[$cid]->id" :booth="$submits[$cid]->booth" :cid="$cid" :key="'submit-booth-' . $submits[$cid]->id" />
        @else
            <livewire:submit-accept :submit_id="0" :accept_id="0" :cid="$cid" :paper_id="$paper_id" />
            <td></td>
        @endif
    @endforeach
</tr>
