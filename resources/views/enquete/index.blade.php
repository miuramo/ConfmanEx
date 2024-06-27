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

    <div class="py-4 px-6  dark:text-gray-400">
        @foreach ($enqs as $enq)
            <div class="bg-white mx-2 my-4 px-4 py-2 inline-block">
                {{-- {{$enq->id}} --}}
                {{ $enq->name }}
                <div class="my-2">
                    <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id]) }}" color="green"
                        size="sm">
                        回答をみる
                    </x-element.linkbutton>
                    <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id, 'action' => 'excel']) }}"
                        color="teal" size="sm">
                        Excel
                    </x-element.linkbutton>

                    <x-element.linkbutton2 href="{{ route('enq.enqitmsetting', ['enq_id' => $enq->id, 'enq_name' => $enq->name]) }}"
                        color="yellow" size="sm">
                        項目編集
                    </x-element.linkbutton2>

                    {{-- <form class="inline" action="{{ route('admin.crud') }}?table=enquete_items" method="post"
                        id="admincrudwhere{{ $enq->id }}">
                        @csrf
                        @method('post')
                        <input id="whereby" type="hidden"
                            class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full"
                            name="whereBy__enquete_id" value={{ $enq->id }}>
                        <x-element.submitbutton2 color="yellow" size="sm">項目編集
                        </x-element.submitbutton2>
                    </form> --}}
                </div>

                <div class="text-sm ml-8 text-gray-400">
                    {{ count($enq->items) }}個の質問項目
                    <span class="mx-4"></span>

                    <ul class="ml-8">
                        @foreach ($enq->items as $itm)
                            <li>{{ $itm->desc }} ({{ $itm->name }}) {{$itm->orderint}}</li>
                        @endforeach
                    </ul>
                </div>

            </div>
        @endforeach
    </div>

    <div class="py-2 px-6">


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
