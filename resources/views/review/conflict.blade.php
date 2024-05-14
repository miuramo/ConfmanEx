<x-app-layout>
    @php
        $catspans = App\Models\Category::spans();
    @endphp
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="sm">
                &larr; 査読者Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('利害関係の表明とBidding') }}

            {!! $catspans[$cat_id] !!}

        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <!-- papers cat_id -->
    @php
        $sel = [
            2 => '利害',
            3 => '困難',
            4 => '可能',
            5 => '希望',
        ];
        $col = [
            2 => 'pink',
            3 => 'orange',
            4 => 'lime',
            5 => 'cyan',
        ];
        $enqans = [];
    @endphp
    <div class="py-2 px-6">

        <div id="plist" class="grid sm:grid-cols-2 gap-4">

            @foreach ($papers as $paper)
                <div classs="bg-slate-200 p-2">
                    @if ($paper->pdf_file_id != null)
                        <a href="{{ route('file.altimgshow', ['file' => $paper->pdf_file_id, 'hash'=> substr($paper->pdf_file->key,0,8)]) }}" target="_blank">
                    @endif
                    <x-file.paperheadimg :paper=$paper>
                    </x-file.paperheadimg>
                    @if ($paper->pdf_file_id != null)
                        </a>
                    @endif
                    {{-- <img src="{{ route('paper.headimgshow', ['paper' => $paper->id, 'file' => Str::random(5)]) }}"
                        title="{{ $paper->title }}" loading="lazy" class="w-1/2 mt-2"> --}}

                </div>
                <div classs="bg-slate-200 p-5">
                    <x-element.paperid size=2 :paper_id="$paper->id">
                    </x-element.paperid>

                    {!! $catspans[$paper->category_id] !!}

                    @php
                        $current = isset($revcon[$paper->id])
                            ? $revcondiv[$revcon[$paper->id]]
                            : '<span class="text-red-600 font-extrabold">(未入力)</span>';
                        $curval = isset($revcon[$paper->id]) ? $revcon[$paper->id] : 0;
                    @endphp
                    <div id="bid{{ $paper->id }}" class="p-2">
                        {!! $current !!}</td>
                    </div>
                    @if ($curval != 1)
                        <form action="{{ route('revconflict.update', ['paper' => $paper->id]) }}" method="post"
                            id="revconflict{{ $paper->id }}">
                            @csrf
                            @method('post')
                            <input type="hidden" name="paper_id" value="{{ $paper->id }}">
                            <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                            <div class="mx-10 mt-2">
                                @foreach ($sel as $val => $choice)
                                    <input type="radio" id="revcon{{ $paper->id }}_{{ $loop->iteration }}"
                                        name="bidding_id" value="{{ $val }}"
                                        onchange="changed('revconflict{{ $paper->id }}','bid{{ $paper->id }}');"
                                        @if ($curval == $val) checked @endif>
                                    <label for="revcon{{ $paper->id }}_{{ $loop->iteration }}"
                                        class="bg-{{ $col[$val] }}-200 rounded-md py-2 px-3 dark:bg-{{ $col[$val] }}-700">{{ $choice }}</label>
                                    &nbsp;&nbsp;
                                @endforeach
                            </div>
                        </form>
                    @else
                        <div class="text-red-600 font-extrabold text-xl px-4">
                            共著者のため表明不要
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-4 px-6 pb-10">
        <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="lg">
            &larr; 査読者Topに戻る
        </x-element.linkbutton>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
