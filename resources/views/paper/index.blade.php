<x-app-layout>
    <!-- paper.index -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿一覧') }}
        </h2>
    </x-slot>
    @php
        $revreturn = App\Models\Category::select('status__revreturn_on', 'id')
            ->get()
            ->pluck('status__revreturn_on', 'id')
            ->toArray();
        $show_booth = App\Models\Category::select('status__show_booth', 'id')
            ->get()
            ->pluck('status__show_booth', 'id')
            ->toArray();
    @endphp
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="mypaperlist" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if (count($all) == 0)
                <div
                    class="xs:text-sm sm:text-xl text-orange-400 bg-yellow-200 dark:bg-yellow-800 dark:text-orange-700 p-4 rounded-md text-center">
                    あなたが作成した投稿情報はまだありません。
                    <div class="mt-5 mb-2">
                        <x-element.linkbutton href="{{ route('paper.create') }}" color="yellow">
                            新規投稿 </x-element.linkbutton>
                    </div>
                </div>
            @else
                @foreach ($all as $paper)
                    @if ($paper->accepted)
                        <div
                            class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-cyan-300">
                            <span class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
                        @else
                            <div
                                class="bg-slate-200 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-slate-700">
                    @endif
                    <x-element.paperid size=2 :paper_id="$paper->id">
                    </x-element.paperid>
                    @if ($show_booth[$paper->category_id])
                        &nbsp;
                        <x-element.boothid size="xl" :paper="$paper">
                        </x-element.boothid>
                    @endif
                    &nbsp;
                    &nbsp;
                    <x-element.category :cat="$paper->category_id">
                    </x-element.category>
                    &nbsp;
                    &nbsp;
                    <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="blue">
                        Edit </x-element.linkbutton>
                    &nbsp;
                    &nbsp;
                    <x-element.linkbutton2 href="{{ route('paper.show', ['paper' => $paper->id]) }}" color="green">
                        View </x-element.linkbutton2>

                    @if ($revreturn[$paper->category_id])
                        &nbsp;
                        &nbsp;
                        <x-element.linkbutton
                            href="{{ route('paper.review', ['paper' => $paper->id, 'token' => $paper->token()]) }}"
                            color="orange" target="_blank">
                            結果 </x-element.linkbutton>
                        {{-- 議論掲示板があれば、ここにもリンクを表示する --}}
                        @php
                            $bb = App\Models\Bb::where('paper_id', $paper->id)
                                ->where('category_id', $paper->category_id)
                                ->where('type', 2) // 2: メタと著者 この条件を忘れると、議論用が表示されてしまうため注意
                                ->first();
                        @endphp
                        @isset($bb)
                            &nbsp;
                            &nbsp;
                            <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb, 'key' => $bb->key]) }}"
                                color="pink" target="_blank">
                                シェファーディング掲示板 </x-element.linkbutton>
                        @endisset

                        @php
                            $bb3 = App\Models\Bb::where('paper_id', $paper->id)
                                ->where('category_id', $paper->category_id)
                                ->where('type', 3) // 3: 出版　この条件を忘れると、議論用が表示されてしまうため注意
                                ->first();
                        @endphp
                        @isset($bb3)
                            &nbsp;
                            &nbsp;
                            <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb3, 'key' => $bb3->key]) }}"
                                color="pink" target="_blank">
                                出版掲示板 </x-element.linkbutton>
                        @endisset

                        @if (App\Models\Setting::isTrue('ENABLE_ANNOTPAPER'))
                            &nbsp;
                            &nbsp;
                            <x-element.linkbutton href="{{ route('annot.create') }}" color="lime" target="_blank">
                                AnnotPaper </x-element.linkbutton>
                        @endif
                    @endif


                    <a href="{{ route('paper.edit', ['paper' => $paper->id]) }}">
                        <x-file.paperheadimg :paper=$paper>
                        </x-file.paperheadimg>
                    </a>
        </div>
        @endforeach
        @endif
    </div>


    @if (count($coauthor_all) == 0)
        <div
            class="xs:text-sm sm:text-xl text-slate-400 bg-slate-200 p-4 rounded-md text-center mt-10  dark:bg-slate-700 dark:text-slate-400">
            あなたが表示できる共著者投稿はありません。
            <div class="text-sm mt-5">
                ここに共著の投稿を表示するには、あなたの登録メールアドレスを投稿者に伝え、投稿連絡用メールアドレスへの追加を依頼してください。
            </div>
        </div>
    @else
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400 pt-10 pb-3">
            {{ __('共著者分') }}
        </h2>
        <div id="coauthorpaperlist" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach ($coauthor_all as $paper)
                @php
                    $id_03d = sprintf('%03d', $paper->id);
                @endphp
                @if ($paper->accepted)
                    <div
                        class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250  dark:bg-cyan-300">
                        <span class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
                    @else
                        <div class="bg-slate-200 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250">
                @endif
                {{-- <div class="bg-yellow-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250"> --}}
                <x-element.paperid size=2 :paper_id="$paper->id">
                </x-element.paperid>
                &nbsp;
                &nbsp;
                <x-element.category :cat="$paper->category_id">
                </x-element.category>

                @if ($revreturn[$paper->category_id])
                    &nbsp;
                    &nbsp;
                    <x-element.linkbutton
                        href="{{ route('paper.review', ['paper' => $paper->id, 'token' => $paper->token()]) }}"
                        color="orange" target="_blank">
                        結果 </x-element.linkbutton>
                    {{-- 議論掲示板があれば、ここにもリンクを表示する --}}
                    @php
                        $bb = App\Models\Bb::where('paper_id', $paper->id)
                            ->where('category_id', $paper->category_id)
                            ->where('type', 2) // 2: メタと著者 この条件を忘れると、議論用が表示されてしまうため注意
                            ->first();
                    @endphp
                    @isset($bb)
                        &nbsp;
                        &nbsp;
                        <x-element.linkbutton href="{{ route('bb.show', ['bb' => $bb, 'key' => $bb->key]) }}"
                            color="pink" target="_blank">
                            掲示板 </x-element.linkbutton>
                    @endisset
                @endif

                <a href="{{ route('paper.show', ['paper' => $paper->id]) }}">
                    <x-file.paperheadimg :paper=$paper>
                    </x-file.paperheadimg>
                </a>
        </div>
    @endforeach
    </div>
    @endif

    </div>

</x-app-layout>
