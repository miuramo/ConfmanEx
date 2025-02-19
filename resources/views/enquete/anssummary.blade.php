<!-- components.enquete.anssummary -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            「{{ $enq->name }}」 {{ __('アンケート回答サマリー') }}

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

    <div class="bg-teal-200 px-4 py-3 m-6">
        注意：不採択側の採択種別は、集計からは除外しています。<br>
        未回答についても、集計には含まれていません。
    </div>

    <div class="py-4 px-6  dark:text-gray-400">

        @foreach ($enqitems as $ei)
            <x-element.h1>
                {{ $ei->desc }} <span class="mx-4"></span> ({{ $ei->name }} / enqItemID: {{ $ei->id }}
                in enqID: {{ $enq->id }})
            </x-element.h1>
            <div class="mx-2">
                <table class="table-auto">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 bg-slate-500 text-white border">選択肢</th>
                            <th class="px-2 py-2 bg-slate-500 text-white border">回答数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @isset($res[$ei->id])
                            @foreach ($res[$ei->id] as $str => $cnt)
                                <tr class="hover:bg-white">
                                    <td class="border px-4 py-2">{{ $str }}</td>
                                    <td class="border px-4 py-2 text-right">{{ $cnt }}</td>
                                </tr>
                            @endforeach
                        @endisset
                    </tbody>
                </table>
            </div>

            {{--  with うちわけ --}}
            <div class="m-2">
                <table class="table-auto">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 bg-slate-500 text-white border">選択肢</th>
                            <th class="px-2 py-2 bg-slate-500 text-white border">回答数</th>
                            <th class="px-4 py-2 bg-slate-500 text-white border">内訳</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (count($res) > 0)
                            @foreach ($res[$ei->id] as $str => $cnt)
                                <tr class="hover:bg-white">
                                    <td class="border px-4 py-2">{{ $str }}</td>
                                    <td class="border px-4 py-2 text-right">{{ $cnt }}</td>

                                    <td>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <th class="px-4 py-1 bg-slate-400 text-white border">カテゴリ</th>
                                                    <th class="px-2 py-1 bg-slate-400 text-white border">回答数</th>
                                                    <th class="px-4 py-1 bg-slate-400 text-white border">内訳</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @isset($res2[$ei->id][$str])
                                                    @foreach ($res2[$ei->id][$str] as $catid => $accary)
                                                        @if (!is_numeric($catid))
                                                            @continue
                                                        @endif
                                                        <tr class="hover:bg-cyan-50">
                                                            <td class="border px-4 py-2">{{ $catlist[$catid] }}
                                                                <sub>({{ $catid }})</sub>
                                                            </td>
                                                            <td class="border px-4 py-2">
                                                                {{ $res2c[$ei->id][$str][$catid] }}
                                                            </td>
                                                            <td class="border p-1">

                                                                <table>
                                                                    <thead>
                                                                        <tr>
                                                                            <th
                                                                                class="px-4 py-1 bg-slate-300 text-gray-600 border">
                                                                                採択種別</th>
                                                                            <th
                                                                                class="px-2 py-1 bg-slate-300 text-gray-600 border">
                                                                                回答数</th>
                                                                            <th
                                                                                class="px-4 py-1 bg-slate-300 text-gray-600 border">
                                                                                PIDs</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach ($accary as $accid => $cnt2)
                                                                            <tr class="hover:bg-yellow-50">
                                                                                <td class="border px-4 py-0.5">
                                                                                    {{ $acclist[$accid] }}
                                                                                    <sub>({{ $accid }})</sub>
                                                                                </td>
                                                                                <td
                                                                                    class="border px-4 py-0.5 text-right">
                                                                                    {{ $cnt2 }}</td>
                                                                                <td class="text-xs">
                                                                                    {{ implode(', ', $res3[$ei->id][$str][$catid][$accid]) }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>

                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endisset
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mx-2 mt-8">
                <span class="bg-teal-200 p-2">（以下の未回答数について補足）カテゴリがアンケート受付対象だったかどうかは考慮していません。</span>
                <table class="table-auto mt-2">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 bg-slate-500 text-white border">カテゴリ</th>
                            <th class="px-2 py-2 bg-slate-500 text-white border">未回答数</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($noans_cat as $catid => $cnt)
                            <tr class="hover:bg-white">
                                <td class="border px-4 py-2">{{ $catlist[$catid] }}
                                    <sub>({{ $catid }})</sub>
                                </td>
                                <td class="border px-4 py-2 text-right">{{ $cnt }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-element.h1>
                参考
                <span class="mx-4"></span>
                <x-element.linkbutton2 href="{{ route('pub.accstatus') }}" color="cyan" target="_blank">
                    採択状況の確認
                </x-element.linkbutton2>

            </x-element.h1>
        @endforeach
    </div>

    <div class="py-2 px-6">

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
