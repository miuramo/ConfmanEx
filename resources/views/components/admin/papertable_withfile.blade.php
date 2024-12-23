@props([
    'all' => [],
    'heads' => ['chk', 'id', '削除日時', 'title', 'owner', 'owneraffil', 'pdf', 'img', 'video', 'altpdf'],
    'enqans' => [],
])

<!-- components.admin.papertable -->
<div class="my-2">
    <x-element.button onclick="checkAllByClass('nopdffile')" color="purple" value="PDFファイル無しにチェック">
    </x-element.button>    
</div>

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
                <td class="p-1 text-center">
                    <input type="checkbox" name="pid[]" value="{{ $paper->id }}"
                    @if ($paper->deleted_at == null && ($paper->pdf_file_id == 0 || $paper->pdf_file == null))
                        class="nopdffile bg-purple-200"
                    @endif
                    >
                </td>
                <td class="p-1 text-sm">{{ $paper->id_03d() }}
                </td>
                <td class="p-1 text-sm text-red-500">{{ $paper->deleted_at }}
                </td>
                <td class="p-1 text-sm">{{ $paper->title }}
                </td>
                <td class="p-1 text-sm">{{ @$paper->paperowner->name }}
                </td>
                <td class="p-1 text-sm">{{ @$paper->paperowner->affil }}
                </td>
                <td class="p-1 text-sm">
                    @if ($paper->pdf_file_id != 0 && $paper->pdf_file != null)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $paper->pdf_file->pagenum }}page
                        </a>
                    @else
                        --
                    @endif
                </td>
                <td class="p-1 text-sm">
                    @if ($paper->img_file_id != 0 && $paper->img_file != null)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}"
                            target="_blank">
                            img
                        </a>
                    @else
                        --
                    @endif
                </td>
                <td class="p-1 text-sm">
                    @if ($paper->video_file_id != 0 && $paper->video_file != null)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->video_file_id, 'hash' => substr($paper->video_file->key, 0, 8)]) }}"
                            target="_blank">
                            video
                        </a>
                    @else
                       --
                    @endif
                </td>
                <td class="p-1 text-sm">
                    @if ($paper->altpdf_file_id != 0 && $paper->altpdf_file != null)
                        <a class="underline text-blue-600 hover:bg-lime-200"
                            href="{{ route('file.showhash', ['file' => $paper->altpdf_file_id, 'hash' => substr($paper->altpdf_file->key, 0, 8)]) }}"
                            target="_blank">
                            altpdf
                        </a>
                    @else
                        --
                    @endif
                </td>

            </tr>
        @endforeach
    </tbody>
</table>

<script>
    function checkAllByClass(cls){
        var checks = document.getElementsByClassName(cls);
        for (var i = 0; i < checks.length; i++){
            checks[i].checked = true;
        }
    }
    </script>
