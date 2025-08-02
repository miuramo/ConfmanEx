<div>
    @if(!$vote->isopen || $vote->isclose)
        <x-alert.error>投票は終了しました。</x-alert.error>
        <x-element.linkbutton href="{{ route('vote.index') }}" color="green" size="sm">
            &larr; 投票Topに戻る
        </x-element.linkbutton>
        @return
    @endif
    
    <x-element.h1>
        {{ $vi->name }} {{ $vi->desc }} を<b>
            @if ($vi->upperlimit > 1)
                {{ $vi->upperlimit }} 件以内で
            @elseif ($vi->upperlimit > 0)
                {{ $vi->upperlimit }} 件
            @else
                すべて
            @endif
        </b> 選択してください。（あと {{ $vi->upperlimit - count($selectedPapers) }} 件 選択できます）
    </x-element.h1>

    <div class="mx-4">
@php
    $boothes = json_decode($vi->submits, true);
@endphp
        @foreach ($boothes as $booth => $pid)
            @php
                $disabled = count($selectedPapers) >= $vi->upperlimit && !in_array($booth, $selectedPapers);
                if ($vi->upperlimit == 0) {
                    $disabled = false;
                    $vi->upperlimit = count($boothes) ; // 0 means unlimited, but we set it to a high number for display purposes
                }
            @endphp
            <div class="mx-1 my-1">
                <input type="checkbox" class="cursor-pointer mt-0 mb-1 disabled:cursor-not-allowed disabled:bg-slate-400"
                    id="{{ $booth }}" value="{{ $booth }}" wire:model.defer="selectedPapers"
                    wire:change="checkLimit" @disabled(count($selectedPapers) >= $vi->upperlimit && !in_array($booth, $selectedPapers)) />
                <label for="{{ $booth }}"
                    class="hover:bg-yellow-100 hover:border-2 hover:border-yellow-300 hover:p-1 hover-trigger hover:font-bold cursor-pointer p-0.5 transition-all duration-150 {{ $disabled ? 'cursor-not-allowed text-gray-400 hover:border-0 hover:border-gray-300 hover:bg-gray-200 hover:p-0' : 'cursor-pointer' }}">
                    {{ $booth }} : {{ $papers[$pid] }}</label>
                <div
                    class="absolute hidden border-2 border-lime-300 p-1 ml-64 mt-4 text-black bg-lime-100 bg-opacity-85 tooltip text-sm transition-all duration-150">
                    {{ str_replace("\n", ' ', trim($authors[$pid])) }}
                </div>

                @if ($vi->show_pdf_link)
                    @php
                        $paper = App\Models\Paper::where('id', $pid)->first();
                    @endphp
                    <span class="ml-2">
                        <x-file.link_anyfile :fileid="$paper->pdf_file_id" label="PDF" linktype='link' />
                    </span>
                @endif
            </div>
        @endforeach
    </div>

    <x-element.h1>
        <span class="px-4 py-2 font-bold text-blue-800 bg-cyan-100 border-2  border-blue-800">選択済 → {{ implode(' , ', $selectedPapers) }} </span>
        <span class="mx-4"></span>
        あと {{ $vi->upperlimit - count($selectedPapers) }} 件 選択できます
        <br><br>
                    投票後に <x-element.linkbutton2 href="{{ route('vote.vote', ['vote' => $vote]) }}" color="cyan">再読み込み
            </x-element.linkbutton2>
            をおすと、ただしく投票できているか確認できます。

    </x-element.h1>

    {{-- If your happiness depends on money, you will never be happy with yourself. --}}
</div>
