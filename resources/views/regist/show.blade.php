<x-app-layout>
    <!-- regist.index -->
    @section('title', '参加登録の参照')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{-- {{ __('参加登録の参照') }} --}}
            @php
                $user = \App\Models\User::find($reg->user_id);
            @endphp
            <span class="mx-4"></span>
            {{ $user->name }} さん（{{ $user->affil }}）の参加登録の参照

        </h2>
    </x-slot>

    @php
        $OFFSET = 0; // paper_idのオフセット値
        $uid = $reg->user_id; // ユーザID
        $enqs = App\Models\Enquete::needForRegist();
        $enqans = $reg->enqans();
    @endphp
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="pt-4 px-6">
        <x-element.linkbutton href="{{ route('regist.index') }}" color="lime">
            参加登録トップに戻る
        </x-element.linkbutton>
    </div>

    <div class="pt-0 px-6">
        @foreach ($enqs['all'] as $enq)
            <a name="enq_{{ $enq->id }}"></a>
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400">
                {{ $enq->name }}
                <span class="mx-10"></span>
                <x-element.linkbutton2 href="{{ route('enq.preview', ['enq' => $enq->id, 'key' => $enq->getkey(7)]) }}"
                    size="sm" color="cyan" target="_blank">質問項目をみる</x-element.linkbutton2>
            </div>
            @if ($enq->showonpaperindex)
                <form action="{{ route('enquete.update', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}" method="post"
                    id="enqform{{ $enq->id }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="paper_id" value="{{ $OFFSET + $uid }}">
                    <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                    <div class="mx-10">
                        <x-enquete.view :enq="$enq" :enqans="$enqans">
                        </x-enquete.view>
                    </div>
                </form>
            @endif
        @endforeach
    </div>
    <div class="pt-8 px-6 pb-6">
        <x-element.linkbutton href="{{ route('regist.index') }}" color="lime">
            参加登録トップに戻る
        </x-element.linkbutton>
    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
        <script src="/js/form_changed.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
