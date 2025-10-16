<x-app-layout>
    <!-- regist.index -->
    @section('title', '参加登録')

    @php
        $OFFSET = 0; // paper_idのオフセット値
        $uid = $reg->user_id; // ユーザID
        $enqs = App\Models\Enquete::needForRegist();
        $enqans = $reg->enqans();
    @endphp
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{-- {{ __('参加登録') }} --}}
            @php
                $user = \App\Models\User::find($reg->user_id);
            @endphp
            <span class="mx-4"></span>
            {{ $user->name }} さん（{{ $user->affil }}）の参加登録
        </h2>
    </x-slot>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6 mt-2 p-3 bg-cyan-100 rounded-lg dark:bg-cyan-900 dark:text-blue-200 text-lg text-blue-500">
        入力内容はフォーカスを外すと、すぐに保存されます。<br>参加登録を完了するには、一番下の「入力内容をチェックする」ボタンを押した後に表示される「参加登録を完了する」ボタンを押してください。<br>

    </div>
    <div class="pt-4 px-6">
        <x-element.linkbutton href="{{ route('regist.index') }}" color="orange"
            confirm="中断した場合、入力内容は保存されますが、登録は未完了となります。中断してよろしいですか？">
            参加登録を中断する（参加登録トップに戻る）
        </x-element.linkbutton>
    </div>

    <div class="pt-0 px-6">
        @foreach ($enqs['all'] as $enq)
            <a name="enq_{{ $enq->id }}"></a>
            <div
                class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400
                @isset($enqs['canedit_idx'][$enq->id])
                    hover:bg-green-300 dark:hover:bg-green-800
                @endisset                 
                 ">
                {{ $enq->name }}
                @isset($enqs['readonly_idx'][$enq->id])
                    <span class="mx-10"></span>
                    <span class="text-red-500 px-4">修正期限を過ぎています</span>
                    <x-element.linkbutton2 href="{{ route('enq.preview', ['enq' => $enq->id, 'key' => $enq->getkey(7)]) }}"
                        size="sm" color="cyan" target="_blank">質問項目をみる</x-element.linkbutton2>
                @endisset
            </div>
            @if ($enq->showonpaperindex)
                <div class="mx-10">
                    @isset($enqs['canedit_idx'][$enq->id])
                        <form action="{{ route('enquete.update', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}"
                            method="post" id="enqform{{ $enq->id }}">
                            @csrf
                            @method('put')
                            <input type="hidden" name="paper_id" value="{{ $OFFSET + $uid }}">
                            <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                            <input type="hidden" name="user_id" value="{{ $OFFSET + $uid }}"> {{-- ここで、参加登録について、実際のユーザではなく、代理捜査の場合の対象ユーザIDを入れる --}}
                            <x-enquete.edit :enq="$enq" :enqans="$enqans">
                            </x-enquete.edit>
                        </form>
                    @else
                        <x-enquete.view :enq="$enq" :enqans="$enqans">
                        </x-enquete.view>
                    @endisset
                </div>
            @endif
        @endforeach

    </div>
    <div class="py-2 px-6">
        <livewire:regist-check :regid="$regid" />
    </div>
    <div class="pt-8 px-6 pb-6">
        <x-element.linkbutton href="{{ route('regist.index') }}" color="orange"
            confirm="中断した場合、入力内容は保存されますが、登録は未完了となります。中断してよろしいですか？">
            参加登録を中断する（参加登録トップに戻る）
        </x-element.linkbutton>
    </div>

    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
        }

        function CheckNoTag(formname, cls) {
            // JQueryで、クラスがclsである要素を取得し、その要素のチェックボックスをチェックする
            $("." + cls).prop('checked', true);
        }

        function UnCheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = false;
                }
            }
        }
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
        <script src="/js/form_changed.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
