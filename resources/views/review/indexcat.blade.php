<x-app-layout>
    <!-- review.index -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="sm">
                &larr; 査読者Topに戻る
            </x-element.linkbutton>
        </div>
        @php
            $catspans = App\Models\Category::spans();
            $revon = App\Models\Category::select('id', 'status__revedit_on')
                ->get()
                ->pluck('status__revedit_on', 'id')
                ->toArray();
            $revoff = App\Models\Category::select('id', 'status__revedit_off')
                ->get()
                ->pluck('status__revedit_off', 'id')
                ->toArray();

            // 査読掲示板On
            $revbbon = App\Models\Category::select('id', 'status__revbb_on')
                ->get()
                ->pluck('status__revbb_on', 'id')
                ->toArray();

            $nameofmeta = App\Models\Setting::getval('NAME_OF_META');
        @endphp

        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('担当査読論文一覧') }}
            <span class="mx-2"></span>
            {!! $catspans[$cat_id] !!}

        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-4 px-6  dark:text-gray-400">
        @if ($revon[$cat_id] && count($reviews) > 0)
            <x-element.linkbutton href="{{ route('review.downzip', ['cat' => $cat_id]) }}" color="yellow">
                担当査読論文ファイルをダウンロード ({{ $cat->name }})
            </x-element.linkbutton>
        @endif
        @if (count($reviews) > 0)
        @else
            担当査読論文はまだありません。
        @endif
    </div>

    <div class="py-2 px-6">

        <div id="revlist" class="grid sm:grid-cols-2 lg:grid-cols-2 gap-4">

            @foreach ($reviews as $rev)
                @if ($revon[$rev->category_id])
                    @if ($rev->status == 2)
                        <div class="bg-cyan-100 px-3 pt-4">
                        @else
                            <div class="bg-yellow-50 px-3 pt-4">
                    @endif
                    <x-element.paperid size=1 :paper_id="$rev->paper->id">
                    </x-element.paperid>
                    <span class="mx-2"></span>

                    @if (!$revoff[$rev->category_id])
                        @if ($rev->ismeta)
                            <x-element.linkbutton2 href="{{ route('review.edit', ['review' => $rev]) }}" color="red">
                                Edit ({{ $nameofmeta }})
                            </x-element.linkbutton2>
                        @else
                            <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                                Edit
                            </x-element.linkbutton>
                        @endif
                    @else
                        <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                            View
                        </x-element.linkbutton>
                    @endif
                    <span class="mx-2"></span>

                    @if ($revbbon[$rev->paper->category_id])
                        <x-element.bblink :rev="$rev" label="議論掲示板">
                        </x-element.bblink>
                        <span class="mx-2"></span>
                    @endif

                    @if ($rev->status == 2)
                        <span class="inline-block border-2 border-blue-600 p-0.5 text-blue-600 font-bold text-sm">
                            査読完了
                        </span>
                    @endif

                    @if ($rev->paper->pdf_file_id != null)
                        @if (!$revoff[$rev->category_id])
                            <a href="{{ route('review.edit', ['review' => $rev]) }}">
                            @else
                                <a href="{{ route('review.show', ['review' => $rev]) }}">
                        @endif
                        {{-- <a href="{{ route('file.altimgshow', ['file' => $rev->paper->pdf_file_id, 'hash' => substr($rev->paper->pdf_file->key, 0, 8)]) }}"
                                target="_blank"> --}}
                    @endif
                    <x-file.paperheadimg :paper="$rev->paper">
                    </x-file.paperheadimg>
                    @if ($rev->paper->pdf_file_id != null)
                        </a>
                    @endif

                    <div class="mt-2 ml-2">
                        {{-- まず、showonreviewerindex アンケートをあつめる。 --}}
                        <x-enquete.Rev_enqview :rev="$rev" size="sm">
                        </x-enquete.Rev_enqview>
                    </div>

                    <div class="mt-2 ml-2 grid xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 gap-4">
                        <x-file.on_paper :paper="$rev->paper">
                        </x-file.on_paper>
                    </div>
        </div>
        @endif
        @endforeach
    </div>
    <div class="mb-4 my-10">
        <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="sm">
            &larr; 査読者Topに戻る
        </x-element.linkbutton>
    </div>
    <div class="mb-4 my-10">
        <x-review.myscores :cat_id="$cat_id">
        </x-review.myscores>
    </div>

    {{-- // （おもにインタラクション）メタレビューワーは、査読者の査読結果を途中でも見ることができる(commentpaperを見ることができる) --}}
    @can('role', 'metareviewer')
        <div class="mb-4 my-10">
            <x-element.h1>
                自分が{{$nameofmeta}}を担当している、査読結果を見る
            </x-element.h1>
            <div class="px-2">
                <table class="min-w-full divide-y divide-gray-200 mb-2">
                    <thead>
                        <tr>
                            <th class="p-1 bg-slate-300"> PaperID</th>
                            <th class="p-1 bg-slate-300"> Title</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($reviews as $rev)
                            @if ($rev->ismeta)
                                <tr
                                    class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500' }}">
                                    <td class="p-1 text-center">
                                        {{ sprintf('%03d', $rev->paper->id) }}
                                    </td>
                                    <td class="p-1">
                                        <x-review.commentpaper_link :sub="$rev->submit"></x-element.commentpaper_link>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endcan

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
