<x-app-layout>
    <!-- review.index -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="sm">
                &larr; 査読者Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('担当査読論文一覧') }}


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
    @endphp

    <div class="py-4 px-6  dark:text-gray-400">
        @foreach ($cats as $cat)
            @if ($revon[$cat->id] && count($revbycat[$cat->id]) > 0)
                <x-element.linkbutton href="{{ route('review.downzip', ['cat' => $cat]) }}" color="yellow">
                    担当査読論文ファイルをダウンロード ({{ $cat->name }})
                </x-element.linkbutton>
            @endif
        @endforeach
        @if (count($reviews) > 0)
        @else
            担当査読論文はまだありません。
        @endif
    </div>

    <div class="py-2 px-6">

        <div id="revlist" class="grid sm:grid-cols-3 gap-4">

            @foreach ($reviews as $rev)
                @if ($revon[$rev->category_id])
                    <div classs="bg-slate-400 p-5">
                        <x-element.paperid size=2 :paper_id="$rev->paper->id">
                        </x-element.paperid>

                        @if (!$revoff[$rev->category_id])
                            <x-element.linkbutton href="{{ route('review.edit', ['review' => $rev]) }}" color="blue">
                                Edit
                            </x-element.linkbutton>
                        @else
                            <x-element.linkbutton href="{{ route('review.show', ['review' => $rev]) }}" color="green">
                                View
                            </x-element.linkbutton>
                        @endif

                        @if ($revbbon[$rev->paper->category_id])
                            <x-element.bblink :rev="$rev">
                            </x-element.bblink>
                        @endif

                        {!! $catspans[$rev->paper->category_id] !!}

                        @if ($rev->paper->pdf_file_id != null)
                            <a href="{{ route('file.altimgshow', ['file' => $rev->paper->pdf_file_id, 'hash' => substr($rev->paper->pdf_file->key, 0, 8)]) }}"
                                target="_blank">
                        @endif
                        <x-file.paperheadimg :paper="$rev->paper">
                        </x-file.paperheadimg>
                        @if ($rev->paper->pdf_file_id != null)
                            </a>
                        @endif

                        <div class="text-sm mt-2 ml-2">
                            {{-- まず、showonreviewerindex アンケートをあつめる。 --}}
                            <x-enquete.Rev_enqview :rev="$rev">
                            </x-enquete.Rev_enqview>
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
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
