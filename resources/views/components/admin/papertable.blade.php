@props([
    'all' => [],
    'heads' => ['cat', 'id', 'id03d', 'title', 'owner', 'owneraffil', 'owneremail', 'contactemails', 'pdf', 'enqans'],
    'enqans' => [],
])

<!-- components.admin.papertable -->

<table class="min-w-full divide-y divide-gray-200">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($all as $paper)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                <td class="p-1">{{ $paper->category_id }}
                </td>
                <td class="p-1">{{ $paper->id }}
                </td>
                <td class="p-1">{{ $paper->id_03d() }}
                </td>
                <td class="p-1">{{ $paper->title }}
                </td>
                <td class="p-1">{{ $paper->paperowner->name }}
                </td>
                <td class="p-1">{{ $paper->paperowner->affil }}
                </td>
                <td class="p-1">{{ $paper->paperowner->email }}
                </td>
                <td class="p-1">{!! nl2br($paper->contactemails) !!}
                </td>
                <td class="p-1">
                    @if ($paper->pdf_file_id != 0)
                        <a href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        No PDF
                    @endif
                </td>
                {{-- アンケート --}}
                @isset($enqans[$paper->id])
                    @forelse ($enqans[$paper->id] as $enqid => $eans)
                        @foreach ($eans as $name => $val)
                            <td class="p-1">
                                {{ $val }}
                            </td>
                        @endforeach
                    @empty
                    @endforelse
                @endisset

            </tr>
        @endforeach
    </tbody>
</table>
