@props([
    'all' => [],
])

<!-- components.file.elem -->
@foreach ($all as $file)
    <div @if ($file->deleted) class="bg-red-300 dark:bg-red-500
    @elseif ($file->locked)
     class="bg-green-200 dark:bg-green-800
     @elseif ($file->pending)
     class="bg-yellow-200 dark:bg-yellow-800
    @else
    class="bg-slate-200 dark:bg-slate-600 @endif
        overflow-hidden shadow-lg sm:rounded-lg dark:text-slate-400 motion-safe:hover:scale-[1.05] transition-all
        duration-250">
        {{-- <div class="p-4 text-gray-900 xs:text-sm sm:text-sm md:text-md lg:text-lg xl:text-2xl"> --}}
        <div class="p-2 text-gray-900 text-sm leading-relaxed  dark:text-gray-400">
            @if (!$file->locked)
                <x-dropdown2>
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-slate-200 dark:bg-gray-800 hover:bg-slate-50 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-div>
                            <x-element.deletebutton action="{{ route('file.destroy', ['file' => $file->id]) }}"
                                color="red" confirm="削除してよいですか？"> Delete File
                            </x-element.deletebutton>
                        </x-dropdown-div>
                        @if ($file->mime == 'application/pdf')
                        <x-dropdown-link target="_blank" :href="route('file.pdftext', ['file' => $file->id])">
                            (参考) PDF抽出テキスト
                        </x-dropdown-link>
                        @endif
                    </x-slot>
                </x-dropdown2>
                <br>
            @endif

            @if ($file->locked)
                <span
                    class="mx-1 sm:rounded-lg border-2 border-green-600 bg-lime-200 px-2 py-1 font-bold text-green-600 text-lg dark:bg-lime-400">Locked</span>
            @endif
            <x-file.adoption :file="$file" />
            <div class="my-2"></div>

            @if ($file->mime == 'image/png' || $file->mime == 'image/jpeg')
                <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                    target="_blank">
                    <img src="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width=300>
                </a>
                <div class="my-2 "></div>
                {{ $file->origname }}
            @else
                @if ($file->mime == 'application/pdf')
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        <img src="{{ route('file.altimgshow', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width=300>
                    </a>
                    <div class="my-2 "></div>
                    {{ $file->origname }}
                    &nbsp;
                    <span
                        class="sm:rounded-lg  bg-cyan-100 p-1 dark:bg-cyan-300 dark:text-gray-500 whitespace-nowrap">{{ $file->pagenum }}
                        @if ($file->pagenum > 1)
                            pages
                        @else
                            page
                        @endif
                    </span>
                @elseif (strpos($file->mime, 'video') === 0)
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">
                        <img src="{{ route('file.altimgshow', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                            title="{{ $file->origname }}" loading="lazy" class="flex-shrink-0" width=300>
                    </a>
                    <div class="my-2 "></div>
                    {{ $file->origname }}
                @else
                    <a href="{{ route('file.showhash', ['file' => $file->id, 'hash' => substr($file->key, 0, 8)]) }}"
                        target="_blank">{{ $file->origname }}</a>
                @endif
            @endif
            <span class="m-2 text-gray-400 text-xs text-right">{{$file->created_at}}</span>

            @if ($file->deleted)
                <span class="mx-4 sm:rounded-lg  bg-yellow-200 px-2 py-1 font-bold text-red-600 text-lg">Deleted</span>
            @endif
            @if ($file->pending)
                <span
                    class="mx-4 sm:rounded-lg  bg-yellow-500 px-2 py-1 font-bold text-yellow-50 text-lg">Pending</span>
            @endif
            @if (!$file->valid)
                <span class="mx-4 sm:rounded-lg  bg-red-500 px-2 py-0 font-bold text-black text-lg">Invalid
                    ({{ $file->created_at }})</span>
            @endif


        </div>
    </div>
@endforeach
