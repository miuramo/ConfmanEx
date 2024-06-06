<!-- role.top -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            参加登録 (EventID: {{ $part->event_id }})
        </h2>

        <style>
            .hidden-content {
                display: none;
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @if ($part->valid)
        <x-alert.success>参加登録は完了しています。</x-alert.success>
    @else
        <x-alert.error>まだ参加登録は完了していません。以下の項目に回答し、ページ下の「登録」を押してください。</x-alert.error>
    @endif

    <div class="m-6">
        @foreach ($enqs['canedit'] as $enq)
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400">
                {{ $enq->name }}
                @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageedit', ['paper' => $part->id, 'enq' => $enq]) }}" color="cyan">
                        ここをクリックして回答
                    </x-element.linkbutton>
                @endif
                <x-element.gendospan>{{ $enqs['until'][$enq->id] }}まで修正可</x-element.gendospan>
            </div>
            @if ($enq->showonpaperindex)
                <form action="{{ route('enquete.update', ['paper' => $part->id, 'enq' => $enq]) }}" method="post"
                    id="enqform{{ $enq->id }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="paper_id" value="{{ $part->id }}">
                    <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                    <div class="mx-10">
                        <x-enquete.edit :enq="$enq" :enqans="$enqans">
                        </x-enquete.edit>
                    </div>
                </form>
            @endif
        @endforeach
        @foreach ($enqs['readonly'] as $enq)
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                {{ $enq->name }}
                @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageview', ['paper' => $part->id, 'enq' => $enq]) }}" color="cyan">
                        ここをクリックして回答参照
                    </x-element.linkbutton>
                @endif
            </div>
            @if ($enq->showonpaperindex)
                <div class="mx-10">
                    <x-enquete.view :enq="$enq" :enqans="$enqans">
                    </x-enquete.view>
                </div>
            @endif
        @endforeach
    </div>


    <div class="mx-20 pb-10">
        <form action="{{ route('part.update', ['part' => $part]) }}" method="post" id="addusertorole">
            @csrf
            @method('put')
            <x-element.submitbutton value="regist" color="cyan">
                登録
            </x-element.submitbutton>
        </form>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
