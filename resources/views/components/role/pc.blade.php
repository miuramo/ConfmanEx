@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();

    $cat_paper_count = App\Models\Category::withCount('papers')->get();
    // PDFファイルがある投稿の数
    $count_paper_haspdf = App\Models\Paper::select(DB::raw('count(id) as count, category_id'))
        ->groupBy('category_id')
        ->whereNotNull('pdf_file_id')
        ->get()
        ->pluck('count', 'category_id');
@endphp
<!-- components.role.reviewer -->
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
                <x-element.submitbutton value="view" color="green">↑選択カテゴリの投稿論文リスト
                </x-element.submitbutton><br>
                <x-element.submitbutton value="excel" color="teal">↑選択カテゴリのExcel Download
                </x-element.submitbutton>
            </form>
        </div>

        <div class="px-2 py-2 flex-grow">
            @php
                $fts = ['pdf', 'img', 'video', 'altpdf'];
            @endphp
            <form action="{{ route('admin.zipdownload') }}" method="post" id="admin_zipdownload">
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

                <x-element.submitbutton value="view" color="yellow">↑選択したファイルをDownload
                </x-element.submitbutton>
            </form>

        </div>
    </div>

    <x-element.h1> <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting', ['toukou' => 'on']) }}" color="cyan"
            target="_blank">
            投稿受付管理
        </x-element.linkbutton>
        <span class="px-2"></span>
        <x-element.linkbutton href="{{ route('admin.catsetting') }}" color="orange" target="_blank">
            査読進行管理
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>査読結果 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            <x-element.linkbutton href="{{ route('review.result', ['cat' => $catid]) }}" color="purple"
                target="_blank">
                {{ $catname }}
            </x-element.linkbutton>
        @endforeach
    </x-element.h1>

    <x-element.h1>査読結果＋コメント <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid]) }}" color="purple"
                target="_blank">
                {{ $catname }}
            </x-element.linkbutton>
        @endforeach
        @foreach ($cats as $catid => $catname)
            <x-element.linkbutton href="{{ route('review.comment', ['cat' => $catid, 'excel' => 'dl']) }}"
                color="teal">
                {{ $catname }}Excel
            </x-element.linkbutton>
        @endforeach
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
        <x-element.linkbutton href="{{ route('enq.index') }}" color="cyan">
            アンケート一覧
        </x-element.linkbutton>
    </x-element.h1>


    <x-element.h1>査読割り当て <span class="px-2"></span>
        @php
            $roles = App\Models\Role::where('name', 'like', '%reviewer')->get();
        @endphp
        @foreach ($cats as $catid => $catname)
            @foreach ($roles as $role)
                <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $catid, 'role' => $role]) }}"
                    color="lime">
                    {{ $catname }}→{{ $role->desc }}
                </x-element.linkbutton>
            @endforeach
        @endforeach
    </x-element.h1>



    <x-element.h1>ロック
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('file.adminlock') }}" color="orange">
            投稿ファイルのロック
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">投稿締め切り後に操作してください。PDFファイルがロックされます。</span>

        <x-element.linkbutton href="{{ route('paper.adminlock') }}" color="cyan">
            Paperのロック
        </x-element.linkbutton> <span class="text-sm mx-2 mr-10">カメラレディ締め切り後に操作してください。著者名と所属、書誌情報がロックされます。</span>
    </x-element.h1>


    <x-element.h1>査読観点(Viewpoint)の管理</x-element.h1>

    <div class="px-6 dark:text-gray-300">
        <x-element.linkbutton href="{{ route('viewpoint.export') }}" color="yellow">
            Viewpoint Download
        </x-element.linkbutton>
        でダウンロードしたExcelを修正して、<br>↓でアップロードすると変更できます。
        <form action="{{ route('viewpoint.import') }}" method="post" id="vpimport" enctype="multipart/form-data">
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
    </div>

    <x-element.h1>自分の権限確認（Role一覧）</x-element.h1>
    @php
        $user = App\Models\User::find(auth()->id());
    @endphp
    @foreach ($user->roles as $ro)
        <span class="inline-block bg-slate-300 rounded-md p-1">{{ $ro->desc }} ({{ $ro->name }})</span>
    @endforeach


    <x-element.h1>

        <x-element.linkbutton color="cyan" href="{{ route('admin.crud') }}" target="_blank">
            CRUD
        </x-element.linkbutton>
        @php
            $shortcuts = [
                'Setting' => 'settings',
                'Category' => 'categories',
                'EnqueteConfig' => 'enquete_configs',
                'Enquete' => 'enquetes',
                'EnqueteItems' => 'enquete_items',
            ];
        @endphp
        @foreach ($shortcuts as $key => $tbl)
            <span class="mx-2"></span>
            <x-element.linkbutton color="cyan" href="{{ route('admin.crud', ['table' => $tbl]) }}"
                target="_blank">
                {{ $key }}
            </x-element.linkbutton>
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
