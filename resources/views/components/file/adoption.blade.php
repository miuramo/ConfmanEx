@props([
    'file' => null,
])

<!-- components.file.adoption -->
@if ($file->paper->is_accepted_in_any_category())
    @if ($file->id == $file->paper->pdf_file_id)
        <span
            class="mx-0 sm:rounded-lg border-2 border-blue-600 bg-cyan-200 px-1 py-1 font-bold text-blue-600 text-sm dark:bg-cyan-400">
            PDFとして収録予定
        </span>
    @elseif($file->id == $file->paper->img_file_id)
        <span
            class="mx-0 sm:rounded-lg border-2 border-blue-600 bg-cyan-200 px-1 py-1 font-bold text-blue-600 text-sm dark:bg-cyan-400">
            IMGとして収録予定
        </span>
    @elseif($file->id == $file->paper->video_file_id)
        <span
            class="mx-0 sm:rounded-lg border-2 border-blue-600 bg-cyan-200 px-1 py-1 font-bold text-blue-600 text-sm dark:bg-cyan-400">
            Videoとして収録予定
        </span>
        <div class="my-2"></div>
        <x-element.deletebutton action="{{ route('file.abandon', ['file' => $file->id]) }}" color="purple"
            confirm="本当に【収録しない】に変更してよいですか？（ここで変更すると、元に戻せません）"> 【収録しない】に変更する
        </x-element.deletebutton>
    @elseif($file->id == $file->paper->altpdf_file_id)
        <span
            class="mx-0 sm:rounded-lg border-2 border-blue-600 bg-cyan-200 px-1 py-1 font-bold text-blue-600 text-sm dark:bg-cyan-400">
            ALTPDFとして収録予定
        </span>
    @endif
@endif
