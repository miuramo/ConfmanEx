@props([
    'all' => [],
    'imgsize' => 200,
    'size' => 'xs',
])

<!-- components.file.on_paper -->
@foreach ($all as $file)
    <div @if ($file->deleted) class="bg-red-300 dark:bg-red-500
    @elseif ($file->locked)
     class="bg-green-200 dark:bg-green-800
     @elseif ($file->pending)
     class="bg-yellow-200 dark:bg-yellow-800
    @else
    class="bg-slate-200 dark:bg-slate-600 @endif
        overflow-hidden shadow-sm sm:rounded-lg dark:text-slate-400 motion-safe:hover:scale-[1.01] transition-all
        duration-250">
        <div class="p-2 text-gray-900 text-{{$size}}">
            <x-file.adoption :file="$file" size="{{$size}}" />
            <div class="my-2"></div>

            @if ($file->mime == 'image/png' || $file->mime == 'image/jpeg')
                <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                    target="_blank">
                    <img src="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width={{$imgsize}}>
                </a>
                {{ $file->origname }}
            @else
                @if ($file->mime == 'application/pdf')
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        <img src="{{ route('file.altimgshow', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width={{$imgsize}}>
                    </a>
                    {{ $file->origname }}
                    <div>
                        <span class="sm:rounded-lg bg-red-100 p-1 whitespace-nowrap">{{ $file->pagenum }}
                            @if ($file->pagenum > 1)
                                pages
                            @else
                                page
                            @endif
                        </span>

                    </div>
                @elseif (strpos($file->mime, 'video') === 0)
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        <img src="{{ route('file.altimgshow', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width={{$imgsize}}>
                    </a>
                    <div class="my-2 "></div>
                    {{ $file->origname }}
                @else
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">{{ $file->origname }}</a>
                @endif
            @endif
            <div class="my-2 text-gray-600 text-sm text-right">{{$file->created_at}}</div>

            @if ($file->locked)
                <span
                    class="mx-4 sm:rounded-lg border-2 border-green-600 bg-lime-200 px-2 py-1 font-bold text-green-600 text-{{$size}}">Locked</span>
            @endif
            <div class="my-2"></div>

            @if ($file->deleted)
                <span class="mx-4 sm:rounded-lg bg-yellow-200 px-2 py-1 font-bold text-red-600 text-{{$size}}">Deleted</span>
            @endif
            @if ($file->pending)
                <span class="mx-4 sm:rounded-lg bg-yellow-500 px-2 py-1 font-bold text-yellow-50 text-{{$size}}">Pending</span>
            @endif
            @if (!$file->valid)
                <span class="mx-4 sm:rounded-lg bg-red-500 px-2 py-0 font-bold text-black text-{{$size}}">Invalid
                    ({{ $file->created_at }})</span>
            @endif

        </div>
    </div>
@endforeach
