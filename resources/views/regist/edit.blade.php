<x-app-layout>
    <!-- regist.index -->
    @php
    @endphp
    @section('title', '参加登録')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('参加登録') }}
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

    <div class="py-2 px-6">

        @foreach ($enqs['canedit'] as $enq)
            <a name="enq_{{ $enq->id }}"></a>
            <div
                class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400 hover:bg-green-300 dark:hover:bg-green-800">
                {{ $enq->name }}
                {{-- @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageedit', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}" color="cyan">
                        ここをクリックして回答
                    </x-element.linkbutton>
                @endif --}}
                {{-- <x-element.gendospan>{{ $enqs['until'][$enq->id] }}まで修正可</x-element.gendospan> --}}
            </div>
            @if ($enq->showonpaperindex)
                <form action="{{ route('enquete.update', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}" method="post"
                    id="enqform{{ $enq->id }}">
                    @csrf
                    @method('put')
                    <input type="hidden" name="paper_id" value="{{ $OFFSET + $uid }}">
                    <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                    <div class="mx-10">
                        @if ($reg->valid)
                            <x-enquete.view :enq="$enq" :enqans="$enqans">
                            </x-enquete.view>
                        @else
                            <x-enquete.edit :enq="$enq" :enqans="$enqans">
                            </x-enquete.edit>
                        @endif
                    </div>
                </form>
            @endif
        @endforeach

        @foreach ($enqs['readonly'] as $enq)
            <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                {{ $enq->name }}
                @if (!$enq->showonpaperindex)
                    &nbsp; → <x-element.linkbutton
                        href="{{ route('enquete.pageview', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}"
                        color="cyan">
                        ここをクリックして回答参照
                    </x-element.linkbutton>
                @endif
                <span class="mx-10"></span>
                <x-element.linkbutton2 href="{{ route('enq.preview', ['enq' => $enq->id, 'key' => $enq->getkey(7)]) }}"
                    size="xs" color="cyan" target="_blank">質問項目をみる</x-element.linkbutton2>

            </div>
            @if ($enq->showonpaperindex)
                <div class="mx-10">
                    <x-enquete.view :enq="$enq" :enqans="$enqans">
                    </x-enquete.view>
                </div>
            @endif
        @endforeach

    </div>
    @if (!$reg->valid)
        <div class="py-2 px-6">
            <livewire:regist-check :regid="$regid" />
        </div>
    @endif
    <div class="my-20"></div>
    <div class="py-2 px-6">
        <x-element.linkbutton href="{{ route('regist.index') }}" color="gray">
            参加登録トップに戻る
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
