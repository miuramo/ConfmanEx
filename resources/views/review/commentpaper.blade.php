<x-app-layout>
    <!-- review.pccomment -->
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    @endphp
    @section('title', $paper->id_03d() . ' スコア')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読結果') }} &nbsp; {{ $paper->id_03d() }} &nbsp; {{ $paper->title }} &nbsp;

            {!! $catspans[$cat_id] !!}



        </h2>
    </x-slot>

    <div class="py-2 px-6">
            @if ($paper->pdf_file_id != 0 && $paper->pdf_file != null)
                <a class="underline text-blue-600 hover:bg-lime-200"
                    href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                    target="_blank">
                    PDF ({{ $paper->pdf_file->pagenum }}pages)
                </a>
            @endif
            <span class="mx-4"></span>
            @if ($paper->video_file_id != 0 && $paper->video_file != null)
                <a class="underline text-blue-600 hover:bg-lime-200"
                    href="{{ route('file.showhash', ['file' => $paper->video_file_id, 'hash' => substr($paper->video_file->key, 0, 8)]) }}"
                    target="_blank">
                    Video
                </a>
            @endif

    </div>

    <div class="mx-6 mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
        <div class="w-full">
            <x-file.paperheadimg :paper="$paper">
            </x-file.paperheadimg>
        </div>
        <div class="w-full">
            @if ($paper->img_file_id != 0 && $paper->img_file != null)
                <img src="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}">
            @endif
        </div>
    </div>

{{-- //    プライマリの査読結果（Primary部分のみ6項目）を表示する。 --}}


{{-- // また、その下に、各査読者のスコアとコメントをすべて表示する。 --}}

</x-app-layout>
