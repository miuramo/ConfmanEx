<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('投稿一覧') }}
            {{-- &nbsp;
            <x-element.linkbutton href="{{ route('file.create') }}" color="cyan">
                Upload New File</x-element.linkbutton>

            <x-element.deletebutton action="{{ route('file.delall') }}" color="red" confirm="全部削除してよいですか？"> Delete All
            </x-element.deletebutton> --}}

        </h2>
    </x-slot>
    <!-- paper.index -->
    @php
        $revreturn = App\Models\Category::select('status__revreturn_on', 'id')
            ->get()
            ->pluck('status__revreturn_on', 'id')
            ->toArray();
    @endphp
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
        <div id="mypaperlist" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @if (count($all) == 0)
                <div class="xs:text-sm sm:text-xl text-orange-400 bg-yellow-200 dark:bg-yellow-800 dark:text-orange-700 p-4 rounded-md text-center">
                    あなたが作成した投稿情報はまだありません。
                    <div class="mt-5 mb-2">
                        <x-element.linkbutton href="{{ route('paper.create') }}" color="yellow">
                            新規投稿 </x-element.linkbutton>
                    </div>
                </div>
            @else
                @foreach ($all as $paper)
                    @if ($paper->accepted)
                        <div class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-cyan-300"> <span
                                class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
                        @else
                            <div class="bg-slate-200 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250 dark:bg-slate-700">
                    @endif
                    <x-element.paperid size=2 :paper_id="$paper->id">
                    </x-element.paperid>
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
                        <x-element.linkbutton href="{{ route('paper.review', ['paper' => $paper->id]) }}" color="orange"
                            target="_blank">
                            結果 </x-element.linkbutton>
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
        <div class="xs:text-sm sm:text-xl text-slate-400 bg-slate-200 p-4 rounded-md text-center mt-10  dark:bg-slate-700 dark:text-slate-400">
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
                    <div class="bg-cyan-100 p-3 motion-safe:hover:scale-[1.03] transition-all duration-250  dark:bg-cyan-300"> <span
                            class="border-2 border-blue-600 p-1 text-blue-600 font-bold">投稿完了</span>
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
