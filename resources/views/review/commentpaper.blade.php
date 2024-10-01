<x-app-layout>
    <!-- review.commentpaper -->
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
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->pdf_file_id, 'hash' => substr($paper->pdf_file->key, 0, 8)]) }}"
                target="_blank">
                PDF ({{ $paper->pdf_file->pagenum }}pages)
            </a>
        @endif
        <span class="mx-4"></span>
        @if ($paper->video_file_id != 0 && $paper->video_file != null)
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->video_file_id, 'hash' => substr($paper->video_file->key, 0, 8)]) }}"
                target="_blank">
                Video
            </a>
        @endif
        <span class="mx-4"></span>
        @if ($paper->img_file_id != 0 && $paper->img_file != null)
            <a class="underline text-blue-600 hover:bg-lime-200 p-2"
                href="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}"
                target="_blank">
                Image
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
                <img
                    src="{{ route('file.showhash', ['file' => $paper->img_file_id, 'hash' => substr($paper->img_file->key, 0, 8)]) }}">
            @endif
        </div>
    </div>
    {{-- 査読スコアのサマリー表示 bb_id=null にすると、掲示板に表示したもののプライマリの名前も表示しない。 --}}
    <div class="mx-4 mt-4 text-center">
        <x-review.paperscores :paper_id="$paper->id" :cat_id="$paper->category_id" :bb_id=null size="lg"></x-review.paperscores>
    </div>
    {{-- //    プライマリの査読結果（Primary部分のみ6項目）を表示する。 --}}
    @php
        $sub = App\Models\Submit::where('paper_id', $paper->id)
            ->where('category_id', $paper->category_id)
            ->first();
            $nameofmeta = App\Models\Setting::findByIdOrName('name_of_meta')->value;
    @endphp

    @foreach ($sub->reviews as $rev)
        <div class="m-6">
            <table class="table-auto">
                <thead>
                    <tr>
                        <th colspan=2 class="bg-slate-300 border-4 border-slate-300 text-left pl-10">
                            査{{ $rev->id }}

                            @if ($rev->ismeta)
                                <span class="mx-2 font-bold text-purple-500">({{$nameofmeta}}) </span>
                            @endif
                        </th>
                        {{-- <th class="bg-slate-300 border-4 border-slate-300">
                        </th> --}}
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rev->scores_and_comments(1, 0, $sub->accept_id > 0) as $vpdesc => $valstr)
                        <tr
                            class="border-4 border-slate-300 {{ $loop->iteration % 2 === 0 ? 'bg-neutral-200' : 'bg-white-50' }}">
                            <td class="p-2 bg-slate-100 border-2 border-slate-300 text-sm">
                                {{ $vpdesc }}
                            </td>
                            <td class="p-2 text-left">
                                {{-- @if ($valstr == '(未入力)')
                                    （とくにお伝えする事項は、ありません）
                                @else --}}
                                    {!! nl2br( App\Models\Review::urllink($valstr) ) !!}
                                {{-- @endif --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach
    {{-- // また、その下に、各査読者のスコアとコメントをすべて表示する。 --}}

</x-app-layout>
