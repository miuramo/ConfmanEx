<x-app-layout>
    <!-- revcon.revstat -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
            <span class="mx-6"></span>
            表示切り替え → 
            <span class="mx-1"></span>
            <x-element.linkbutton2 href="{{ route('revcon.revstat', ['role' => 'reviewer']) }}" color="lime" size="sm">
                査読者
            </x-element.linkbutton>
            <span class="mx-2"></span>
            <x-element.linkbutton2 href="{{ route('revcon.revstat', ['role' => 'metareviewer']) }}" color="lime" size="sm">
                {{ App\Models\Setting::getval('NAME_OF_META') }}
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読割り当て Stat') }}
<span class="mx-2">
</span>
<span class="mx-2 p-2 border-2 bg-lime-200">{{$revrole->desc}}</span>
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @php
        $col = ['blue', 'red'];
        $col2 = ['cyan', 'orange'];
        $catspans = App\Models\Category::spans();
        // 査読プロセスをまわす（査読者を割り当てる）カテゴリ
        $cat_arrange_review = App\Models\Category::where('status__arrange_review', true)
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    @endphp


    <div class="py-4 px-2  dark:text-gray-400">
        <x-element.h1>発表カテゴリ通算 </x-element.h1>
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
                    @foreach (['一般', 'メタ'] as $n => $lbl)
                        <tr>
                            <td class="p-0 text-center text-sm text-{{ $col[$n] }}-500">{{ $lbl }}
                            </td>
                            @foreach ($reviewers as $rev)
                                <td
                                    class="p-1 text-center bg-{{ $col2[$n] }}-50 text-{{ $col[$n] }}-500 font-bold">
                                    {{ @$cnt_users_all[$rev->id][$n] }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


        @foreach ($cats as $cid => $cname)
            @isset($cat_arrange_review[$cid])
                <div class="my-8"></div>
                <x-element.h1>{!! $catspans[$cid] !!} のみ </x-element.h1>
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
                            @foreach (['一般', 'メタ'] as $n => $lbl)
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

                <div class="my-2 p-1">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                @foreach (['メタ', '一般', 'pid', 'title'] as $h)
                                    <th class="p-1 bg-slate-300 text-sm">{{ $h }}</th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($papers_in_cat[$cid] as $pid => $pname)
                                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                                    <td class="p-1 text-center text-red-500 font-bold">
                                        {{ @$cnt_papers[$cid][$pid][1] }}
                                    </td>
                                    <td
                                        class="p-1 text-center text-blue-500 font-bold
                                @if (@$cnt_papers[$cid][$pid][0] == 2) bg-cyan-100 @endif
                                ">
                                        {{ @$cnt_papers[$cid][$pid][0] }}
                                    </td>
                                    <td class="p-1 text-center">{{ sprintf('%03d', $pid) }}
                                    </td>
                                    <td class="p-1 text-sm">{{ $pname }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endisset
        @endforeach
    </div>


</x-app-layout>
