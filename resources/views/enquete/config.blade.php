<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('アンケート受付設定') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    <div class="bg-teal-200 px-4 py-3 m-6">
        catcsv は、受け付ける対象の投稿カテゴリIDをカンマ区切りで入力してください。<br>
        例：<b>d1,2,3</b> と書くと、登壇のデモ希望と、デモ発表と、ポスター発表が対象となります。（カテゴリIDが 1:登壇、2:デモ発表、3:ポスター発表 の場合）<br>
        <br>
        投稿論文のカテゴリによって、受付の期間を変えたい場合は、行を追加してください。<br>
        <br>
        openstart, openend は、MM-DD の形式で入力してください。openendのほうが若い場合、年始をまたぐ設定となります。<br>
        openend は、受付最終日である（この日まで受け付ける）ことに注意してください。<br>
        <br>
        openstart〜openendの期間を過ぎた場合、回答入力が1つ以上あれば、「参照のみ」表示になります。<br>
        回答入力が0個の場合でも、openendから60日間は「参照のみ」表示になります。（著者に未入力アンケートの存在に気づかせるため）<br>
        <br>
        アンケートの並び順 (orderint) は、投稿者の編集画面で、複数のアンケートを受け付けることになった場合の表示順です（小さい数字のものから順番に表示します）。
        つまり、このアンケート受付設定だけではなく、他のアンケート受付設定との兼ね合いで決まります。<br>
    </div>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $enq = App\Models\Enquete::find($enq_id);
    @endphp
    <div class="mx-6">
        <x-element.h1>
            アンケート 【{{ $enq->name }}】 (enqid: {{ $enq->id }}) の受付設定
            <span class="mx-6"></span>
            <x-element.linkbutton2 href="{{ route('enq.preview', ['enq' => $enq->id, 'key'=>'foradmin']) }}"
                color="blue" size="sm">
                プレビュー
            </x-element.linkbutton2>
            <span class="mx-1"></span>
            <x-element.linkbutton2
                href="{{ route('enq.enqitmsetting', ['enq_id' => $enq->id, 'enq_name' => $enq->name]) }}" color="yellow"
                size="sm">
                項目編集
            </x-element.linkbutton2>
            <span class="mx-4"></span>
            <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id]) }}" color="green" size="sm">
                回答をみる
            </x-element.linkbutton>
            <span class="mx-1"></span>
            <x-element.linkbutton href="{{ route('enq.answers', ['enq' => $enq->id, 'action' => 'excel']) }}"
                color="teal" size="sm">
                Excel
            </x-element.linkbutton>

        </x-element.h1>

        <div class="py-2 px-2">
            <div class="mx-2 py-4">
                <table>
                    <tr class="border border-gray-400 p-2">
                        {{-- <th class="p-2">id</th>
                        <th class="p-2">enquete_id</th> --}}
                        <th class="p-2">有効なら1 (valid)</th>
                        <th class="p-2">受け付けるカテゴリID<br>のカンマ区切り<br> (catcsv)<br> dではじまる場合は<br>デモ希望のみが対象となる</th>
                        <th class="p-2">受付開始日<br>(openstart)</th>
                        <th class="p-2">受付最終日<br>(openend)</th>
                        <th class="p-2">アンケート名、対象カテゴリ、<br>覚書など<br> (memo)</th>
                        <th class="p-2">アンケートの並び順<br> (orderint)</th>
                        <th class="p-2">操作</th>
                    </tr>

                    @foreach ($configs as $config)
                        <tr class="border border-gray-400 p-2">
                            <input form="ec" type="hidden" name="id[]" value="{{ $config->id }}" />
                            <td class="p-2 text-center">
                                <input form="ec" type="number" name="valid[]" value="{{ $config->valid }}"
                                    min="0" max="1" />
                            </td>
                            <td class="p-2 text-center">
                                <input form="ec" type="text" name="catcsv[]" value="{{ $config->catcsv }}"
                                    size="10" />
                            </td>
                            <td class="p-2 text-center">
                                <input form="ec" type="text" name="openstart[]"
                                    value="{{ $config->openstart }}" size="6" />
                            </td>
                            <td class="p-2 text-center">
                                <input form="ec" type="text" name="openend[]" value="{{ $config->openend }}"
                                    size="6" />
                            </td>
                            <td class="p-2 text-center">
                                <input form="ec" type="text" name="memo[]" value="{{ $config->memo }}"
                                    size="30" />
                            </td>
                            <td class="p-2 text-center">
                                <input form="ec" type="number" name="orderint[]" value="{{ $config->orderint }}"
                                    min="0" max="999" />
                            </td>
                            <td class="p-2">
                                <x-element.deletebutton
                                    action="{{ route('enqconfig.delete', ['enqconfig' => $config->id]) }}"
                                    color="red">
                                    この行を削除
                                </x-element.deletebutton>
                            </td>
                        </tr>
                    @endforeach
                </table>
                <form action="{{ route('enq.config', ['enq' => $enq_id]) }}" method="post" id="ec">
                    @csrf
                    @method('post')
                    <div class="m-2">
                        <x-element.submitbutton color="cyan" value="update">
                            更新（複数行の場合、まとめて更新）
                        </x-element.submitbutton>

                        <span class="mx-4"></span>
                        <x-element.submitbutton color="yellow" value="addrow">
                            行を追加
                        </x-element.submitbutton>
                    </div>
                </form>

            </div>
        </div>


        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
    </div>

</x-app-layout>
