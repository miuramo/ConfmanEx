@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

    $cat_paper_count = App\Models\Category::withCount('papers')->get();
    // PDFファイルがある投稿の数
    $count_paper_haspdf = App\Models\Paper::select(DB::raw('count(id) as count, category_id'))
        ->whereNotNull('pdf_file_id')
        ->whereNot('pdf_file_id', 0) // 一度PDFをアップして、あとで消すとnullではなく0になることがあった。現在は修正済み
        ->groupBy('category_id')
        ->get()
        ->pluck('count', 'category_id');

    // 査読プロセスをまわす（査読者を割り当てる）カテゴリ
    $cat_arrange_review = App\Models\Category::where('status__arrange_review', true)
        ->get()
        ->pluck('name', 'id')
        ->toArray();
@endphp
<!-- components.role.pc -->
<div class="px-6 py-4">

    <x-element.h1>投稿論文</x-element.h1>

    <div class="px-6 py-2 flex">
        <table class="w-32 divide-y divide-gray-400 flex-grow  dark:text-gray-300">
            <thead>
                <tr>
                    <th class="px-2">Category</th>
                    <th class="px-2">Papers</th>
                    <th class="px-2">withPDF</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cat_paper_count as $cpc)
                    <tr>
                        <td class="px-2 text-center">{{ $cpc->name }}</td>
                        <td class="px-2 text-right">{{ $cpc->papers_count }}</td>
                        @isset($count_paper_haspdf[$cpc->id])
                            <td class="px-2 text-right">{{ $count_paper_haspdf[$cpc->id] }}</td>
                        @else
                            <td class="px-2 text-right">0</td>
                        @endisset
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- <div class="px-6 py-2 flex-grow">
            <x-element.linkbutton href="{{ route('admin.paperlist') }}" color="green">
                すべての投稿論文リスト
            </x-element.linkbutton>
            <x-element.linkbutton href="{{ route('admin.paperlist_excel') }}" color="teal">
                すべての投稿論文リストのExcel Download
            </x-element.linkbutton>
        </div> --}}
        <div class="px-6 py-2 flex-grow">
            <form action="{{ route('admin.paperlist') }}" method="post" id="admin_paperlist">
                @csrf
                @method('post')
                <div>
                    @foreach ($cats as $catid => $catname)
                        <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                            id="label{{ $catid }}" @if ($catid == 1) checked="checked" @endif>
                        <label for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
                        &nbsp;
                    @endforeach
                </div>
                <x-element.submitbutton value="view" color="green">↑ 選択カテゴリの投稿論文リスト
                </x-element.submitbutton><br>
                <x-element.submitbutton value="excel" color="teal">↑ 選択カテゴリのExcel Download
                </x-element.submitbutton>
            </form>
            <div class="py-1"></div>
            <x-element.linkbutton2 href="{{ route('admin.deletepaper',['cat'=>1]) }}" color="yellow">
                投稿とファイルの状況（削除済みを含む）
            </x-element.linkbutton2>
            <div class="py-1"></div>
            <x-element.linkbutton2 href="{{ route('admin.timestamp',['cat'=>1]) }}" color="purple">
                投稿とファイルのタイムスタンプ
            </x-element.linkbutton2>
        </div>

        <div class="px-2 py-2 flex-grow">
            @php
                $fts = ['pdf', 'img', 'video', 'altpdf'];
            @endphp
            <form action="{{ route('admin.zipstream') }}" method="post" id="admin_zipdownload">
                @csrf
                @method('post')
                <div>
                    @foreach ($cats as $catid => $catname)
                        <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                            id="label{{ $catid }}" @if ($catid == 1) checked="checked" @endif>
                        <label for="label{{ $catid }}"
                            class="dark:text-gray-300">{{ $catname }}</label>&nbsp;
                    @endforeach
                </div>
                <div>
                    @foreach ($fts as $ft)
                        <input type="checkbox" name="filetype{{ $ft }}" value="{{ $ft }}"
                            id="label{{ $ft }}" @if ($ft == 'pdf') checked="checked" @endif>
                        <label for="label{{ $ft }}"
                            class="dark:text-gray-300">{{ $ft }}</label>&nbsp;
                    @endforeach
                </div>

                <x-element.submitbutton value="view" color="yellow">↑ 選択したファイルをDownload
                </x-element.submitbutton>
            </form>
            <div class="pt-4">
                <div>
                    デモ希望数：{{ App\Models\EnqueteAnswer::demoCount() }}
                </div>
                <div>
                    デモ希望PaperIDリスト：{{ implode(",", App\Models\EnqueteAnswer::demoPaperIDs()) }}
                </div>
            </div>
        </div>
    </div>

    <x-element.h1> <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting', ['toukou' => 'on']) }}" color="cyan"
            target="_blank">
            投稿受付管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting', ['mandatoryfile' => 'on']) }}" color="lime"
            target="_blank">
            サプリメントファイル受付管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting') }}" color="orange" target="_blank">
            査読進行管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton2 href="{{ route('admin.catsetting', ['leadtext' => 'on']) }}" color="gray" target="_blank">
            カテゴリ固有の案内(リード文など)
        </x-element.linkbutton2>
    </x-element.h1>

    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
        <span class="px-3">掲示板</span>
        <x-element.linkbutton href="{{ route('bb.index') }}" color="pink">
            掲示板一覧
        </x-element.linkbutton>
        <span class="px-3">アンケート</span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            アンケート一覧
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>査読結果と判定 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @php
                $btncolor = isset($cat_arrange_review[$catid]) ? 'purple' : 'gray';
            @endphp
            <x-element.linkbutton href="{{ route('review.result', ['cat' => $catid]) }}" color="{{ $btncolor }}"
                target="_blank">
                {{ $catname }}
            </x-element.linkbutton>
            <span class="mx-1"></span>
        @endforeach
    </x-element.h1>

    <x-element.h1>査読結果（コメント非表示・スコアのみ） <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment_scoreonly', ['cat' => $catid]) }}" color="purple"
                    target="_blank">
                    {{ $catname }}
                </x-element.linkbutton>
            @endisset
        @endforeach
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment_scoreonly', ['cat' => $catid, 'excel' => 'dl']) }}"
                    color="teal">
                    {{ $catname }}Excel
                </x-element.linkbutton>
            @endisset
        @endforeach
    </x-element.h1>

    <x-element.h1>査読結果＋コメント <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid]) }}" color="purple"
                    target="_blank">
                    {{ $catname }}
                </x-element.linkbutton>
            @endisset
        @endforeach
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid, 'excel' => 'dl']) }}"
                    color="teal">
                    {{ $catname }}Excel
                </x-element.linkbutton>
            @endisset
        @endforeach
    </x-element.h1>

    <x-element.h1>査読進捗 <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('revcon.revstatus') }}" color="orange" target="_blank">査読進捗
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>査読者一覧と利害表明者 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
            <x-element.linkbutton href="{{ route('revcon.revname', ['cat' => $catid]) }}"
                color="lime">
                {{ $catname }} 
            </x-element.linkbutton>
                @endisset
        @endforeach
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
            <x-element.linkbutton href="{{ route('revcon.revname', ['cat' => $catid, 'excel' => 'dl']) }}"
                color="teal">
                {{ $catname }} Excel
            </x-element.linkbutton>
                @endisset
        @endforeach
    </x-element.h1>


    <x-element.h1>査読割り当て <span class="px-2"></span>
        @php
            $roles = App\Models\Role::where('name', 'like', '%reviewer')->get();
        @endphp
        @foreach ($roles as $role)
            @if ($role->users->count() > 1)
                @foreach ($cats as $catid => $catname)
                    @isset($cat_arrange_review[$catid])
                        <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $catid, 'role' => $role]) }}"
                            color="lime">
                            {{ $catname }}→{{ $role->desc }}
                        </x-element.linkbutton>
                    @endisset
                @endforeach
            @endif
        @endforeach
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.index') }}" color="orange" target="_blank">
            Bidding未完了状態
        </x-element.linkbutton>
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.stat') }}" color="green" target="_blank">
            Bidding Stat
        </x-element.linkbutton>
        <span class="mx-3"></span>
        <x-element.linkbutton href="{{ route('revcon.revstat') }}" color="lime" target="_blank">
            査読割り当て Stat
        </x-element.linkbutton>


    </x-element.h1>



    <x-element.h1>ロック
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('file.adminlock') }}" color="orange">
            投稿ファイルの管理
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">投稿締め切り後に操作してください。PDFファイルがロックされます。</span>

        <x-element.linkbutton href="{{ route('paper.adminlock') }}" color="cyan">
            書誌情報(Paper)の管理
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">カメラレディ締め切り後に操作してください。著者名と所属、書誌情報がロックされます。</span>
    </x-element.h1>


    <x-element.h1>査読観点(Viewpoint)の管理
        <span class="mx-2"></span>
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                <x-element.linkbutton
                    href="{{ route('viewpoint.itmsetting', ['cat_id' => $catid, 'cat_name' => $catname]) }}"
                    color="yellow" size="sm">
                    {{ $catname }}
                </x-element.linkbutton>

                {{-- <form class="inline" action="{{ route('admin.crud') }}?table=viewpoints" method="post"
                    id="admincrudwhere{{ $catid }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__category_id"
                        value={{ $catid }}>
                    <x-element.submitbutton color="yellow" size="sm">{{ $catname }}
                    </x-element.submitbutton>
                </form> --}}
                <span class="mx-2"></span>
            @endisset
        @endforeach
        <span class="text-sm mx-2 mr-10">編集画面をひらくとき、orderintを自動再調整します。</span>

        <br>
        プレビュー用査読フォーム
        <span class="mx-2"></span>
        @php
            $nameofmeta = App\Models\Setting::findByIdOrName('NAME_OF_META', 'value');
        @endphp
        @foreach ($cats as $catid => $catname)
            @isset($cat_arrange_review[$catid])
                @foreach (['一般', $nameofmeta] as $ismeta => $revtype)
                    <x-element.linkbutton2 href="{{ route('review.edit_dummy', ['cat' => $catid, 'ismeta' => $ismeta]) }}"
                        color="blue" size="sm" target="_blank">
                        {{ $catname }}({{ $revtype }})
                    </x-element.linkbutton2>

                    {{-- <form class="inline" action="{{ route('admin.crud') }}?table=viewpoints" method="post"
                    id="admincrudwhere{{ $catid }}">
                    @csrf
                    @method('post')
                    <input id="whereby" type="hidden"
                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full" name="whereBy__category_id"
                        value={{ $catid }}>
                    <x-element.submitbutton color="yellow" size="sm">{{ $catname }}
                    </x-element.submitbutton>
                </form> --}}
                    <span class="mx-2"></span>
                @endforeach
            @endisset
        @endforeach
        {{-- <div class="my-2 px-6 py-2 dark:text-gray-300 bg-slate-300 text-sm">
            <x-element.linkbutton href="{{ route('viewpoint.export') }}" color="yellow">
                Viewpoint Download
            </x-element.linkbutton>
            でダウンロードしたExcelを修正して、<br>↓でアップロードしても変更できます。
            <form action="{{ route('viewpoint.import') }}" method="post" id="vpimport"
                enctype="multipart/form-data">
                @csrf
                @method('post')
                <input type="file" name="file" id="file">
                <div>
                    <input type="hidden" id="append" name="append" value="off">
                    <input type="checkbox" id="append" name="append" checked switch>
                    <label class="form-check-label" for="append">
                        アップロードした内容を追加する(一旦全削除してから追加する場合は、チェックを外す)
                    </label>
                </div>
                <x-element.submitbutton color="yellow">Viewpoint Upload
                </x-element.submitbutton>
            </form>
        </div> --}}
    </x-element.h1>

    <x-element.h1>自分の権限確認（Role一覧）
        <span class="mx-3"></span>
        @php
            $user = App\Models\User::find(auth()->id());
        @endphp
        @foreach ($user->roles as $ro)
            <span class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
    </x-element.h1>

    <x-element.h1> <x-element.linkbutton href="{{ route('admin.hiroba_excel') }}" color="teal">
            情報学広場登録用Excel Download
        </x-element.linkbutton>
    </x-element.h1>


</div>


@php
    // 担当の査読状況

    // もし担当したときの査読フォーム review.
@endphp
