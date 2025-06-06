<!-- components.enquete.pageedit -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{-- <a href="/" title="トップページへのリンク"
                class="font-semibold text-gray-800 hover:text-blue-700 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ env('APP_NAME') }}</a> --}}


            @if ($paper->id > 0)
                「{{ $enq->name }}」{{ __('の編集') }}
                <x-element.paperid size=2 :paper_id="$paper->id">
                </x-element.paperid>
                <span class="mx-2"></span>
                <x-element.category :cat="$paper->category_id">
                </x-element.category>
            @else
                アンケート「{{ $enq->name }}」の質問項目確認
                @if ($key=="foradmin")
                    <span class="mx-4 p-2 border-2 border-blue-500 bg-cyan-100 text-blue-500">プレビュー</span>
                    <span class="mx-2"></span>
                    <x-element.linkbutton2 href="{{ route('enq.preview',['enq'=>$enq->id, 'key'=>$enq->getkey(7)]) }}" color="cyan" size="sm">
                        公開リンク
                    </x-element.linkbutton2>
                @else
                    <div class="mt-4 mx-4 p-2 border-2 border-red-500 bg-pink-100 text-red-500">
                        注意：ここで回答しても、アンケートの回答には反映されません。</div>
                @endif
            @endif
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="mt-4 px-6 mb-10">
        @if ($paper->id > 0)
            <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray" size="lg">
                &larr; 投稿{{ $paper->id_03d() }} 編集に戻る
            </x-element.linkbutton>
        @else
            @if ($key=="foradmin")
                <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                    &larr; アンケート一覧に戻る
                </x-element.linkbutton>
            @endif
        @endif
    </div>

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        <div class="py-2 px-6">
            <div class="m-6">
                <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                    {{ $enq->name }}
                    <x-element.gendospan>{{ $enqs['until'][$enq->id] }}まで修正可</x-element.gendospan>
                </div>
                {{-- EDIT --}}
                <form action="{{ route('enquete.update', ['paper' => $paper->id, 'enq' => $enq]) }}" method="post"
                    id="enqform{{ $enq->id }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="paper_id" value="{{ $paper->id }}">
                    <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                    <div class="mx-10">
                        <x-enquete.edit :enq="$enq" :enqans="$enqans">
                        </x-enquete.edit>
                    </div>
                </form>

                {{-- VIEW --}}
                {{-- <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                    {{ $enq->name }}
                </div>
                <div class="mx-10">
                    <x-enquete.view :enq="$enq" :enqans="$enqans">
                    </x-enquete.view>
                </div> --}}
            </div>
        </div>
    </div>

    <div class="mt-4 px-6 pb-10">
        @if ($paper->id > 0)
            <x-element.linkbutton href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="gray"
                size="lg">
                &larr; 投稿{{ $paper->id_03d() }} 編集に戻る
            </x-element.linkbutton>
        @else
            @if ($key=="foradmin")
                <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                    &larr; アンケート一覧に戻る
                </x-element.linkbutton>
            @endif
        @endif
    </div>


    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        {{-- <script src="/js/drop_zone_upload.js"></script> --}}
        <script src="/js/form_changed.js"></script>
    @endpush

</x-app-layout>
