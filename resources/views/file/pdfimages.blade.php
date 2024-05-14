<!-- file.pdfimages -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('PDF Thumb') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        <div id="filelist" class="grid grid-cols-3 gap-4">
            @for ($p = 1; $p <= $file->pagenum; $p++)
                <div
                    class="bg-slate-200 overflow-hidden shadow-sm sm:rounded-lg dark:bg-slate-800 dark:text-slate-400 motion-safe:hover:scale-[1.05] transition-all duration-250">
                    <div class="p-2 text-gray-900">
                        <a href="{{ route('file.pdfimages', ['file' => $file->id, 'page' => $p]) }}" target="_blank"><img
                                src="{{ route('file.pdfimages', ['file' => $file->id, 'page' => $p]) }}"
                                title="page {{ $p }}" loading="lazy" width=600>
                        </a>
                    </div>
                </div>
            @endfor
        </div>
    </div>

</x-app-layout>
