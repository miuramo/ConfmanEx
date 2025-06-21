@props([
    'mes' => [],
])
<!-- components.bb.mes  -->
@php
    $mes->mes = App\Models\Review::urllink(htmlspecialchars($mes->mes, ENT_QUOTES, 'UTF-8'));
    $mes->mes = strip_tags($mes->mes, '<a>');
    $file_desc = App\Models\Setting::getval('FILE_DESCRIPTIONS');
    $file_desc = json_decode($file_desc);

@endphp

@if ($mes->user_id == auth()->id())
    <div class="text-right">
        <div class="inline-block w-3/4 bg-green-300 p-2 rounded-lg px-2 py-1 my-1 dark:bg-green-800 dark:text-gray-50">
            <div class="flex justify-between">
                <div class="mx-2">{{ $mes->subject }}</div>
                <div class="text-right text-gray-500 text-sm mr-2 dark:text-gray-200">{{ $mes->created_at }}</div>
            </div>
            <div class="bg-green-100 px-2 py-1 mb-1 rounded-md text-left dark:bg-green-700 dark:text-gray-50">
                {!! nl2br($mes->mes) !!}</div>

            @if ($mes->files->count() > 0)
                <div class="text-left">
                    @foreach ($mes->files as $file)
                        <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                            href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            target="_blank">
                            {{ $file->origname }}
                        </a>
                        @if ($mes->bb->paper->pdf_file_id == $file->id || $mes->bb->paper->img_file_id == $file->id || $mes->bb->paper->video_file_id == $file->id || $mes->bb->paper->altpdf_file_id == $file->id)
                            <span class="mx-1 text-sm text-blue-400">(採用済み)</span>
                        @endif
                    @endforeach
                </div>
            @endif

        </div>
    </div>
@else
    <div class="bg-slate-300 rounded-lg w-3/4 px-2 py-1 my-1 dark:bg-slate-600 dark:text-gray-50">
        <div class="flex justify-between">
            <div class="mx-2">{{ $mes->subject }}</div>
            <div class="text-right text-gray-500 text-sm mr-2 dark:text-gray-200">{{ $mes->created_at }}</div>
        </div>
        <div class="bg-slate-100 px-2 py-1 mb-1 rounded-md dark:bg-slate-500">{!! nl2br($mes->mes) !!}</div>

        @if ($mes->files->count() > 0)
            <div class="text-left">
                @foreach ($mes->files as $file)
                    <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                        href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        {{ $file->origname }}
                    </a>
                    @if ($mes->bb->paper->pdf_file_id == $file->id || $mes->bb->paper->img_file_id == $file->id || $mes->bb->paper->video_file_id == $file->id || $mes->bb->paper->altpdf_file_id == $file->id)
                        <span class="mx-1 text-sm text-blue-400 dark:text-blue-200">(採用済み)</span>
                    @else
                        @if ($file->paper_id > 0)
                            @if($file->valid)
                            <form action="{{ route('bb.adopt', ['bb' => $mes->bb->id, 'key' => $mes->bb->key]) }}"
                                method="post" class="inline">
                                @csrf
                                @method('post')
                                <input type="hidden" name="mes_id" value="{{ $mes->id }}">
                                <input type="hidden" name="file_id" value="{{ $file->id }}">

                                →→措置→→
                                <select name="ftype" id="filetype"
                                    class="bg-yellow-100 px-2 py-1 rounded-lg dark:text-gray-500">
                                    @foreach ($file_desc as $name => $desc)
                                        <option value="{{ $name }}">{{ $desc }}</option>
                                    @endforeach
                                </select>
                                <button type="submit"
                                    class="bg-yellow-300 hover:bg-orange-300 px-1 py-1 rounded-lg dark:text-gray-500"
                                    onclick="return confirm('本当に採用しますか？（ファイル種別がただしいか、再度確認してください）')">←として採用し、本掲示板にその旨を通知する。</button>
                                または <x-element.submitbutton2 value="reject" size="xs" color="red" id="bb_submit" confirm="採用せず処置済みにすることで、よろしければ、OKを押してください。">採用せずに処置済みにする（ファイルをinvalid&deletedにし、本掲示板に通知する）</x-element.submitbutton2>
                            </form>

                            <div class="mx-8 text-gray-600 dark:text-gray-50">参考：
                                現在のファイル
                                @if ($mes->bb->paper->pdf_file_id != 0)
                                    <a class="underline text-blue-600 hover:bg-lime-200 p-2 dark:text-blue-300"
                                        href="{{ route('file.showhash', ['file' => $mes->bb->paper->pdf_file_id, 'hash' => substr($mes->bb->paper->pdf_file->key, 0, 8)]) }}"
                                        target="_blank">
                                        PDF ({{ $mes->bb->paper->pdf_file->pagenum }}pages)
                                    </a>
                                @endif
                                @if ($mes->bb->paper->img_file_id != 0)
                                    <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                                        href="{{ route('file.showhash', ['file' => $mes->bb->paper->img_file_id, 'hash' => substr($mes->bb->paper->img_file->key, 0, 8)]) }}"
                                        target="_blank">
                                        IMG
                                    </a>
                                @endif
                            </div>
                            @else
                                <span class="text-sm text-gray-500">→措置済み（未採用）</span>
                            @endif
                        @endif
                    @endif
                @endforeach
            </div>
        @endif

    </div>
@endif
