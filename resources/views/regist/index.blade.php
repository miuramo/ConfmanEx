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

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-4">
        <div class="py-2 px-6">
            <x-element.h1>注意事項</x-element.h1>
            @php
                $sankakakunin = App\Models\Confirm::where('grp', 9)
                    ->where('valid', 1)
                    ->select('name', 'mes')
                    ->get()
                    ->pluck('mes', 'name')
                    ->toArray();

                $allowed_users = App\Http\Controllers\RegistController::allowed_users_string();

                $finishedCount = \App\Models\Regist::whereNotNull('submitted_at')->where('canceled', false)->count(); // 編集中の人も含め、一度は完了した人
                $notfinishedCount = \App\Models\Regist::whereNull('submitted_at')->where('canceled', false)->count(); // まだ一度も完了していない人
                $upperlimit = App\Models\Setting::getval('REG_PERSON_UPPERLIMIT');

                $is_early = auth()->user()->can('is_now_early');

            @endphp
            <ul class="m-4">
                @foreach ($sankakakunin as $name => $mes)
                    <li class="hover:bg-lime-100 dark:text-slate-400 dark:hover:bg-lime-950">
                        <input type="checkbox" checked="checked" class="checked:bg-lime-500">
                        {!! $mes !!}
                    </li>
                @endforeach
            </ul>

        </div>
        <div class="py-2 px-6">
            @php
                $reg = App\Models\Regist::where('user_id', Auth::user()->id)->first();
            @endphp
            @isset($reg)
                <x-element.h1>
                    @if ($reg->valid)
                        <x-element.linkbutton href="{{ route('regist.show', ['regist' => $reg->id]) }}" color="lime">
                            参加登録内容を確認する
                        </x-element.linkbutton>
                    @else
                        <x-element.linkbutton href="{{ route('regist.edit', ['regist' => $reg->id]) }}" color="teal">
                            参加登録を継続する
                        </x-element.linkbutton>
                    @endif

                    @if ($is_early)
                        @if ($reg->valid)
                            <span class="mx-8"></span>
                            <x-element.linkbutton href="{{ route('regist.edit', ['regist' => $reg->id]) }}" color="teal"
                                confirm="編集画面に遷移すると、登録状況が無効になります。編集画面では修正の有無にかかわらず、最後にかならず「参加登録を完了する」ボタンを押してください。">
                                参加登録内容を編集する
                            </x-element.linkbutton>
                        @endif
                        <span class="mx-8"></span>
                        <x-element.deletebutton action="{{ route('regist.destroy', ['regist' => $reg->id]) }}"
                            confirm="参加登録を削除します。よろしいですか？" color="red" align="right">
                            参加登録を削除する
                        </x-element.deletebutton>
                    @else
                        <span class="mx-8"></span>
                        <x-element.linkbutton_disabled size="sm">
                            参加登録内容を編集する（現在は編集不可）
                        </x-element.linkbutton_disabled>
                    @endif
                    <div
                        class="mx-6 mt-2 px-6 py-2 bg-yellow-50 rounded-lg dark:bg-yellow-900 dark:text-yellow-200 text-lg text-orange-600">
                        参加登録を編集・削除できるのは、早期申込期間中のみです。以降のキャンセルは「参加登録後の問い合わせ先」にご連絡ください。
                    </div>
                </x-element.h1>
                <div class="mx-6 mt-4">
                    現在の参加登録内容は以下の通りです。
                    <table class="table-auto">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2 bg-slate-200 dark:bg-slate-500">項目</th>
                                <th class="border px-4 py-2 bg-slate-200 dark:bg-slate-500">内容</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border px-4 py-2 dark:text-gray-100 text-center">状況</td>
                                <td class="border px-4 py-2 dark:text-gray-100 text-center">
                                    @if ($reg->valid)
                                        <span class="text-green-500 font-extrabold text-center">有効</span>
                                    @else
                                        @if ($reg->submitted_at == null)
                                            <span class="text-red-500 font-extrabold text-center">無効（まだ申込は完了していません）</span>
                                        @else
                                            <span
                                                class="text-red-500 font-extrabold text-center">無効（修正後の入力内容に問題があるか、編集画面で完了ボタンを押していません。「継続する」画面で参加登録を再度完了させてください。）</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            @if ($reg->valid)
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">参加者ID</td>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">{{ $reg->user_id }}</td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">参加登録ID </td>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">{{ $reg->id }}</td>
                                </tr>
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">申込日時</td>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">{{ $reg->submitted_at }}
                                    </td>
                                </tr>
                                @if ($reg->submitted_at != $reg->updated_at)
                                    <tr>
                                        <td class="border px-4 py-2 dark:text-gray-100 text-center">申込更新日時</td>
                                        <td class="border px-4 py-2 dark:text-gray-100 text-center">{{ $reg->updated_at }}
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">申込種別</td>
                                    <td class="border px-4 py-2 dark:text-gray-100 text-center">
                                        @if ($reg->isearly)
                                            早期申込
                                        @else
                                            通常申込
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @else
                @if ($upperlimit > 0 && $finishedCount + $notfinishedCount >= $upperlimit)
                    <x-element.h1>
                        <b class="text-emerald-600">参加登録は定員に達しました。申し訳ありませんが、現在の新規参加登録は受け付けていません。</b>
                    </x-element.h1>
                @else
                    <x-element.h1>
                        上記について、すべて確認・了承したうえで、参加登録を開始してください。
                        <br>
                        <b>（注：{{ $allowed_users }}）</b><br>
                        <x-element.linkbutton href="{{ route('regist.create') }}" color="cyan">
                            参加登録を開始する
                        </x-element.linkbutton>
                    </x-element.h1>
                @endif
            @endisset
            @if ($reg && $reg->valid)
                <div class="py-2 px-6">
                    <x-element.linkbutton href="{{ route('regist.email', ['regist' => $reg->id]) }}" color="cyan"
                        confirm="参加登録内容メールを送信します。よろしいですか？">
                        参加登録内容を自分にメール送信する
                    </x-element.linkbutton>
                </div>
            @endif

        </div>


        @php
            if ($reg) {
                $enqans = $reg->enqans();
                $OFFSET = 0; // paper_idのオフセット値
                $uid = $reg->user_id; // ユーザID
                $independently_editable_enqs = \App\Models\Enquete::needForRegistIndependentlyEditable();
            } else {
                $independently_editable_enqs = ['all' => [], 'canedit_idx' => [], 'readonly_idx' => [], 'until' => []];
            }
        @endphp

        @if (empty($independently_editable_enqs['all']) || !$reg || !$reg->valid)
        @else
            <div
                class="text-lg bg-lime-500 rounded-lg dark:bg-slate-800 dark:text-gray-400 p-3 mt-6 text-white font-bold">
                関連アンケートの回答修正（参加登録完了後、参加登録本体とは個別に修正できます。）
            </div>
            <div class="mx-6">

                @foreach ($independently_editable_enqs['all'] as $enq)
                    <a name="enq_{{ $enq->id }}"></a>
                    <div
                        class="text-lg mt-5 mb-1 p-3 bg-cyan-200 rounded-lg dark:bg-slate-800 dark:text-gray-400
                @isset($independently_editable_enqs['canedit_idx'][$enq->id])
                    hover:bg-cyan-300 dark:hover:bg-lime-800
                @endisset                 
                 ">
                        {{ $enq->name }}
                        @isset($enqs['readonly_idx'][$enq->id])
                            <span class="mx-10"></span>
                            <span class="text-red-500 px-4">修正期限を過ぎています</span>
                            <x-element.linkbutton2
                                href="{{ route('enq.preview', ['enq' => $enq->id, 'key' => $enq->getkey(7)]) }}"
                                size="sm" color="cyan" target="_blank">質問項目をみる</x-element.linkbutton2>
                        @else
                            <x-element.gendospan>{{ $independently_editable_enqs['until'][$enq->id] }}まで修正可</x-element.gendospan>
                        @endisset
                    </div>
                    @if ($enq->showonpaperindex)
                        <div class="mx-10">
                            @isset($independently_editable_enqs['canedit_idx'][$enq->id])
                                <form action="{{ route('enquete.update', ['paper' => $OFFSET + $uid, 'enq' => $enq]) }}"
                                    method="post" id="enqform{{ $enq->id }}">
                                    @csrf
                                    @method('put')
                                    <input type="hidden" name="paper_id" value="{{ $OFFSET + $uid }}">
                                    <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                                    <input type="hidden" name="user_id" value="{{ $OFFSET + $uid }}">
                                    {{-- ここで、参加登録について、実際のユーザではなく、代理捜査の場合の対象ユーザIDを入れる --}}
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
        @endif




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
        var debug_mode = false;
        // F1キーを押したら、デバッグモードに切り替える
        document.addEventListener('keydown', function(event) {
            if (event.key === 'F1') {
                debug_mode = !debug_mode;
                if (debug_mode) {
                    $(".debug-f1").removeClass('invisible');
                } else {
                    $(".debug-f1").addClass('invisible');
                }
            }
        });
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/sortable.js"></script>
        <script src="/js/form_changed.js"></script>
        <script src="/js/openclose.js"></script>
    @endpush

</x-app-layout>
