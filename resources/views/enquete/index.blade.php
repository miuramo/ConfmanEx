<!-- components.enquete.index -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('アンケート一覧') }}

        </h2>
    </x-slot>
    {{-- <div class="mx-4">
        <x-element.h1>
            <x-element.submitbutton2 color="yellow" size="sm">項目編集
            </x-element.submitbutton2>ページから戻るときは、ブラウザのBackボタンを使用してください。
        </x-element.h1>
    </div> --}}
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $configs = App\Models\EnqueteConfig::all();
    @endphp

    <div class="py-4 px-6  dark:text-gray-400">
        @foreach ($enqs as $enq)
            <div class="bg-white mx-2 my-4 px-4 py-2 inline-block">
                {{-- {{$enq->id}} --}}
                {{ $enq->name }} <span class="ml-2 text-gray-400">(enqID:{{$enq->id}})</span>
                <div class="my-2">
                    @foreach($configs as $config)
                        @if($config->enquete_id == $enq->id)
                            @if($config->valid)
                            @if($config->isopen())
                                <span class="text-sm bg-green-200 text-green-800 rounded-md px-2 py-1">受付中
                                @else
                                <span class="text-sm bg-blue-200 text-blue-800 rounded-md px-2 py-1">受付期間外
                                @endif
                                [{{$config->catcsv}}] {{$config->openstart}} 〜 {{$config->openend}} (id:{{$config->id}})</span>
                            @else 
                                <span class="text-sm bg-red-100 text-red-800 rounded-md px-2 py-1">無効 (not valid)
                                    [{{$config->catcsv}}] (id:{{$config->id}})
                                </span>
                            @endif
                        @endif
                    @endforeach
                    <span class="mx-1"></span>
                    <x-element.linkbutton2 href="{{ route('enq.config', ['enq' => $enq->id]) }}" color="slate" size="sm">
                        受付設定
                    </x-element.linkbutton2>
                </div>
                <div class="my-2">
                    <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id]) }}" color="green"
                        size="sm">
                        回答をみる
                    </x-element.linkbutton>
                    <span class="mx-1"></span>
                    <x-element.linkbutton2 href="{{ route('enq.anssummary', ['enq' => $enq->id]) }}" color="green"
                        size="sm">
                        サマリー
                    </x-element.linkbutton2>
                    <span class="mx-1"></span>
                    <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id, 'action' => 'excel']) }}"
                        color="teal" size="sm">
                        Excel
                    </x-element.linkbutton>
                </div>

                <div class="text-sm ml-0 text-gray-400">
                    {{ count($enq->items) }}個の質問項目

                    <ul class="ml-2 mb-2">
                        @foreach ($enq->items as $itm)
                            <li>{{ $itm->desc }} ({{ $itm->name }})</li>
                        @endforeach
                    </ul>

                    <x-element.linkbutton2
                        href="{{ route('enq.enqitmsetting', ['enq_id' => $enq->id, 'enq_name' => $enq->name]) }}"
                        color="yellow" size="sm">
                        項目編集
                    </x-element.linkbutton2>
                    <span class="mx-1"></span>
                    <x-element.linkbutton2 href="{{ route('enq.preview', ['enq' => $enq->id, 'key'=>'foradmin']) }}" color="blue"
                        size="sm">
                        プレビュー
                    </x-element.linkbutton2>
                    <span class="mx-1"></span>
                    <x-element.deletebutton action="{{ route('enq.destroy', ['enq' => $enq->id]) }}" 
                        size="sm" confirm="アンケートを論理削除してよいですか？" align="right">
                        削除
                    </x-element.deletebutton>

                </div>
                <div class="text-sm ml-0 my-1 text-gray-800">
                    管理権限：<span class="mx-1"></span>
                    @foreach ($enq->roles as $rl)
                        {{ $rl->desc }} <span class="mx-1"></span>
                    @endforeach
                </div>

            </div>
        @endforeach
    </div>

    <div class="py-2 px-6">
        @can('role', 'pc')
        <x-element.h1>PC長のみ <span class="mx-2"></span>
                <x-element.linkbutton href="{{ route('enq.maptoroles') }}" color="pink" size="md">
                    アンケート管理権限の設定
                </x-element.linkbutton>
        </x-element.h1>
        @endcan

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
