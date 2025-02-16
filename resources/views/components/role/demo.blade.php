<!-- components.role.demo -->
<style>
    .hidden-content {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>

<div class="px-6 py-4">
    <x-element.h1>投稿論文</x-element.h1>

    <div class="px-6 py-2 flex">
    <x-paper.summarytable width='' />

    @php
        $role = App\Models\Role::where('name', 'demo')->first();
    @endphp
    @if (strlen($role->catcsv) > 0)
        @php
            $cats = App\Models\Category::select('name', 'id')->whereIn('id', explode(",",$role->catcsv))->get()->pluck('name', 'id')->toArray();
            $targetcat = $role->cat_id;
        @endphp
        <div class="ml-10 px-2 py-2 flex-grow">
            <form action="{{ route('admin.paperlist') }}" method="post" id="admin_paperlist">
                @csrf
                @method('post')
                <div>
                    @foreach ($cats as $catid => $catname)
                        <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                            id="label{{ $catid }}" @if ($catid == $targetcat) checked="checked" @endif>
                        <label for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
                        &nbsp;
                    @endforeach
                </div>
                <x-element.submitbutton value="view" color="green">↑ の投稿論文リスト
                </x-element.submitbutton><br>
                <x-element.submitbutton value="excel" color="teal">↑ の投稿論文リストExcel
                </x-element.submitbutton>
            </form>
            <div class="py-1"></div>
            <x-element.linkbutton2 href="{{ route('admin.deletepaper', ['cat' => $targetcat]) }}" color="yellow">
                投稿とファイルの状況（削除済みを含む）
            </x-element.linkbutton2>
            <div class="py-1"></div>
            <x-element.linkbutton2 href="{{ route('admin.timestamp', ['cat' => $targetcat]) }}" color="purple">
                投稿とファイルのタイムスタンプ
            </x-element.linkbutton2>
        </div>

        <div class="ml-10 px-2 py-2 flex-grow">
            @php
                $fts = ['pdf', 'img', 'video', 'altpdf'];
            @endphp
            <form action="{{ route('admin.zipstream') }}" method="post" id="admin_zipdownload">
                @csrf
                @method('post')
                <div>
                    @foreach ($cats as $catid => $catname)
                        <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                            id="ziplabel{{ $catid }}" @if ($catid == $targetcat) checked="checked" @endif>
                        <label for="ziplabel{{ $catid }}"
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

                <x-element.submitbutton value="downzip" color="yellow">↑ 選択したファイルをDownload
                </x-element.submitbutton>
            </form>
        </div>
    @endif
    </div>

    <x-element.h1>デモ希望アンケートの状況</x-element.h1>
    <div class="mx-2 px-6 py-2">
        <div class="w-full">
            デモ希望数：{{ App\Models\EnqueteAnswer::demoCount() }}
        </div>
        <div class="w-full">
            デモ希望PaperIDリスト：{{ implode(', ', $dPIDs = App\Models\EnqueteAnswer::demoPaperIDs()) }}
            <span class="mx-2"></span>
            {{ count($dPIDs) }} 件
        </div>
        {{-- <div class="mx-4 w-full">
            カテゴリ別：
            @php
                $demoPaper_eachCat = App\Models\EnqueteAnswer::demoPaperIDs_eachCat();
            @endphp
            <div class="mx-4">
                @foreach ($demoPaper_eachCat as $cat => $papers)
                    <div>
                        {{ $cat }}： {{ implode(', ', $papers) }}
                        <span class="mx-2"></span>
                        {{ count($papers) }} 件
                    </div>
                @endforeach
            </div>
        </div> --}}
        <div class="mx-4 w-full mt-2">
            @php
                $dPP = App\Models\EnqueteAnswer::demoPaperIDs_eachCat_eachAccID();
            @endphp
            <table class="divide-y divide-gray-400 border-2">
                <tr class="bg-gray-200">
                    <th>カテゴリ</th>
                    <th>採択ラベル</th>
                    <th>PaperIDリスト</th>
                    <th class="px-2">件数</th>
                </tr>
                @foreach ($dPP['ary'] as $cat => $cat_ary)
                    @foreach ($cat_ary as $acc => $papers)
                        <tr>
                            <td class="text-center px-2">{{ $dPP['cat'][$cat] }}</td>
                            <td class="text-center px-2">{{ $dPP['acc'][$acc] }}</td>
                            <td class="text-center px-2">{{ implode(', ', $papers) }}</td>
                            <td class="text-center px-2">{{ count($papers) }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </table>
        </div>
    </div>
    <x-element.h1>デモ希望を手動でつける</x-element.h1>
    <div class="mx-2 px-6 py-2">
        <form action="{{ route('enq.manualset') }}" method="post">
            @csrf
            <div class="w-full">
                <label for="paper_id">PaperID（数字カンマ区切り）</label>
                <input type="text" name="pids" id="pids" class="w-96" size="30"
                    placeholder="012, 023, 034">
            </div>
            <div class="w-full mt-2 mx-6">
                指定したPaperについて、
                <x-element.submitbutton color="cyan" value="はい">
                    デモ希望を「はい」にする
                </x-element.submitbutton>
                <x-element.submitbutton color="slate" value="いいえ">
                    デモ希望を「いいえ」にする
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <x-element.h1>
        アンケート
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            （デモRoleおよび自分のRoleで参照可能な）アンケート一覧
        </x-element.linkbutton>

    </x-element.h1>

    <x-element.h1>
        採択状況
        <span class="mx-3"></span>
        <x-element.linkbutton2 href="{{ route('pub.accstatus') }}" color="cyan" target="_blank">
            採択状況の確認
        </x-element.linkbutton2>
        <span class="mx-2"></span>
        <x-element.linkbutton2 href="{{ route('pub.accstatusgraph') }}" color="cyan" target="_blank" size="xs">
            採択状況のグラフ表示（試験的）
        </x-element.linkbutton2>
    </x-element.h1>

    <x-element.h1>査読結果と判定 <span class="px-2"></span>
        @foreach ($cats as $catid => $catname)
            @php
                $btncolor = isset($cat_arrange_review[$catid]) ? 'purple' : 'cyan';
            @endphp
            <x-element.linkbutton href="{{ route('review.result', ['cat' => $catid]) }}" color="{{ $btncolor }}"
                target="_blank">
                {{ $catname }}
            </x-element.linkbutton>
            <span class="mx-1"></span>
        @endforeach
    </x-element.h1>

    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>ブース番号の微調整</x-element.h1>
    <div class="mx-2 px-6 py-2">
        <form action="{{ route('pub.boothmodify') }}" method="get" target="result">
            <div class="w-full">
                <label for="booth">ブース番号（複数入力するときはアンダースコア _ で区切ってください）</label>
                <input type="text" name="booth" id="booth" class="w-96" size="30"
                    placeholder="1A01_3A33">
            </div>
            <div class="w-full">
                <label for="pid">PaperID（複数入力するときはアンダースコア _ で区切ってください）</label>
                <input type="text" name="pid" id="pid" class="w-96" size="30"
                    placeholder="111_222">
            </div>
            <div class="w-full mt-2 mx-6">
                <x-element.submitbutton color="cyan" value="show">
                    編集画面をひらく
                </x-element.submitbutton>
                <span class="mx-2"></span>
                <x-element.submitbutton color="yellow" value="swap" confirm="すぐに交換しますがよろしいですか？">
                    （ブース番号が2つのとき）2つの発表ブースを交換する
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <x-element.h1>自分の権限確認（Role一覧）
        <span class="mx-3"></span>
        @php
            $user = App\Models\User::find(auth()->id());
        @endphp
        @foreach ($user->roles as $ro)
            <span
                class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
    </x-element.h1>

</div>
@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush
