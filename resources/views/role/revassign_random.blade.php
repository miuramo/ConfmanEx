<!-- role.revassign -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            ランダム査読割り当て <span class="mx-2"></span>
        </h2>
    </x-slot>
    @section('title', 'ランダム査読割当')

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-4">
        <div class="m-4 p-4 bg-yellow-100">割り当てのまえに、ファイル無しの論文を論理削除してください。
            <x-element.linkbutton2 href="{{ route('admin.deletepaper', ['cat' => 2]) }}" color="yellow">
                投稿とファイルの状況（削除済みを含む）
            </x-element.linkbutton2>
        </div>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('role.revassign_randompost') }}" method="post" id="revass">
                @csrf
                @method('post')
                <div class="m-2 p-2">割り当て対象の論文
                    <span class="mx-4"></span>
                    @foreach ($cats as $catid => $catname)
                        @if ($catid == 1)
                            @continue
                        @endif
                        <input type="checkbox" name="cat[]" value="{{ $catid }}" id="cat{{ $catid }}"
                            @if ($catid > 1) checked @endif>
                        <label for="cat{{ $catid }}">{{ $catname }}</label>
                        <span class="mx-4"></span>
                    @endforeach
                </div>

                {{-- <div class="m-2 p-2">割り当てる査読者
                    <span class="mx-4"></span>
                    @foreach ($roles as $role)
                        <input type="checkbox" name="role[]" value="{{ $role->id }}" id="role{{ $role->id }}" checked>
                        <label for="role{{ $role->id }}"> {{ $role->desc }}</label>
                        <span class="mx-4"></span>
                    @endforeach
                </div> --}}
                <div class="m-2 p-2">割り当てる人数
                    <span class="mx-4"></span>
                    チーフPC <input type="number" name="num[4]" value="1" min="0" max="10">
                    <span class="mx-4"></span>
                    査読者 <input type="number" name="num[5]" value="4" min="0" max="10">
                </div>

                <div class="m-2 p-2">査読割り当て免除者UIDs
                    @php
                        $role_pc = App\Models\Role::where('name', 'like', 'PC%')->first();
                        $uids = $role_pc->users->pluck('id')->toArray();
                    @endphp
                    <span class="mx-4"></span>
                    <input type="text" name="exclude" value="{{ implode(', ', $uids) }}" size="40">
                </div>
                <span class="mx-2"></span>
                <x-element.submitbutton value="assign">割り当て（すこし時間がかかります）</x-element.submitbutton>
                <div class="my-4"></div>
                <span class="mx-2"></span>
                <x-element.submitbutton value="reset" color="orange">チェックしたカテゴリの割り当てをリセット</x-element.submitbutton>
            </form>
        </div>
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif
    </div>


    @php
        $col = ['blue', 'red'];
        $col2 = ['cyan', 'orange'];
        $catspans = App\Models\Category::spans();
        // 査読プロセスをまわす（査読者を割り当てる）カテゴリ
        $cat_arrange_review = App\Models\Category::where('status__arrange_review', true)
            ->get()
            ->pluck('name', 'id')
            ->toArray();
        foreach ($cats as $cid => $cname) {
            $papers_in_cat[$cid] = App\Models\Category::find($cid)->paperswithpdf->pluck('title', 'id')->toArray();
            $cnt_users[$cid] = App\Models\Review::revass_stat($cid, 'user_id');
            $cnt_papers[$cid] = App\Models\Review::revass_stat($cid, 'paper_id');
        }

        $cnt_users_all = App\Models\Review::revass_stat_allcategory();
    @endphp


    @php
        $revrole = App\Models\Role::findByIdOrName('reviewer');
        $reviewers = $revrole->users;
    @endphp
    <div class="py-4 px-2  dark:text-gray-400">
        @foreach ($cats as $cid => $cname)
            @if ($cid == 1)
                @continue
            @endif
            @isset($cat_arrange_review[$cid])
                <div class="my-8"></div>
                <x-element.h1>{!! $catspans[$cid] !!}
                    <span class="mx-2"></span>
                    {{ $revrole->desc }}
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $cid, 'role' => $revrole]) }}"
                        color="lime" size="sm">
                        {{ $cname }}→{{ $revrole->desc }}
                    </x-element.linkbutton>
                </x-element.h1>
                <div class="my-2 p-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="p-1 bg-slate-200">\
                                </th>
                                @foreach ($reviewers as $rev)
                                    <th class="p-1 bg-slate-300">{{ $rev->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach (['一般'] as $n => $lbl)
                                <tr>
                                    <td class="p-0 text-center text-sm text-{{ $col[$n] }}-500">{{ $lbl }}
                                    </td>
                                    @foreach ($reviewers as $rev)
                                        <td
                                            class="p-1 text-center bg-{{ $col2[$n] }}-50 text-{{ $col[$n] }}-500 font-bold">
                                            {{ @$cnt_users[$cid][$rev->id][$n] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
        @endforeach
    </div>
    @php
        $revrole = App\Models\Role::findByIdOrName('metareviewer');
        $reviewers = $revrole->users;
    @endphp
    <div class="py-4 px-2  dark:text-gray-400">
        @foreach ($cats as $cid => $cname)
            @if ($cid == 1)
                @continue
            @endif
            @isset($cat_arrange_review[$cid])
                <div class="my-8"></div>
                <x-element.h1>{!! $catspans[$cid] !!}
                    <span class="mx-2"></span>
                    {{ $revrole->desc }}
                    <span class="mx-2"></span>
                    <x-element.linkbutton href="{{ route('role.revassign', ['cat' => $cid, 'role' => $revrole]) }}"
                        color="lime" size="sm">
                        {{ $cname }}→{{ $revrole->desc }}
                    </x-element.linkbutton>
                </x-element.h1>
                <div class="my-2 p-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="p-1 bg-slate-200">\
                                </th>
                                @foreach ($reviewers as $rev)
                                    <th class="p-1 bg-slate-300">{{ $rev->name }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach (['一般'] as $n => $lbl)
                                <tr>
                                    <td class="p-0 text-center text-sm text-{{ $col[$n] }}-500">{{ $lbl }}
                                    </td>
                                    @foreach ($reviewers as $rev)
                                        <td
                                            class="p-1 text-center bg-{{ $col2[$n] }}-50 text-{{ $col[$n] }}-500 font-bold">
                                            {{ @$cnt_users[$cid][$rev->id][$n] }}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
        @endforeach
    </div>
    {{-- @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/rev_ass.js"></script>
    @endpush --}}


</x-app-layout>
