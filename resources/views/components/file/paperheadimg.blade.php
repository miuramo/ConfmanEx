<!-- components.file.paperheadimg -->
@if ($paper->pdf_file_id == 0)
    <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => 0]) }}"
        title="{{ $paper->title }}" loading="lazy" class="w-full mt-2">
@else
    <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => substr($paper->pdf_file->key,0,8)]) }}"
        title="{{ $paper->title }}" loading="lazy" class="w-full mt-2 rounded-lg dark:bg-slate-800 dark:text-slate-400 shadow">
@endif
