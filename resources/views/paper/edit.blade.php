<x-app-layout>
    <!-- paper.edit -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{-- <a href="/" title="トップページへのリンク"
                class="font-semibold text-gray-800 hover:text-blue-700 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">{{ env('APP_NAME') }}</a> --}}

            {{ __('投稿情報の編集') }}

            <x-element.paperid size=2 :paper_id="$paper->id">
            </x-element.paperid>
            &nbsp;
            &nbsp;
            <x-element.category :cat="$paper->category_id">
            </x-element.category>
            &nbsp;
            &nbsp;
        </h2>
        <style>
            .hidden-content {
                display: none;
                opacity: 0;
                transition: opacity 0.5s ease;
            }
        </style>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush
    @php
        $revreturn = App\Models\Category::select('status__revreturn_on', 'id')
            ->get()
            ->pluck('status__revreturn_on', 'id')
            ->toArray();
        $revedit_on = App\Models\Category::select('status__revedit_on', 'id')
            ->get()
            ->pluck('status__revedit_on', 'id')
            ->toArray();
    @endphp

    <div class="py-2">
        <div class="py-2 px-6">
            <!-- Category:leadtext -->
            @isset($cat->leadtext)
                @if (strpos($cat->leadtext, '__') !== 0)
                    <div class="m-6">
                        <x-element.h1>{!! $cat->leadtext !!}</x-element.h1>
                    </div>
                @endif
            @endisset
        </div>

        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif
        @if ($errors->any())
            <x-alert.error>
                投稿連絡用メールアドレスの更新に失敗しました。
                @foreach ($errors->all() as $err)
                    {{ $err }}
                @endforeach
            </x-alert.error>
        @endif


        @php
            $submit_finished = false;

            // 査読結果が採択、または、デモ希望がある場合は、カメラレディ提出が必要(WISS)
            // $result_accepted = $paper->submits->where('status', 'accepted')->count();
            $result_accepted = $paper->submits->where('accept_id', '<', 20)->count(); // 本来はJudgeをみるべき
            // $result_accepted = App\Models\Submit::with('accept')
            //     ->where('paper_id', $paper->id)
            //     ->whereHas('accept', function ($query) {
            //         $query->where('judge', '>', 0);
            //     })
            //     ->count();
            // ただし、デモ希望があっても、査読前に申請したものの場合は、通らない場合がある。
            $demo_ifaccepted = $paper->demo_ifaccepted();
            $need_camera_ready = ($result_accepted || $demo_ifaccepted) && $revreturn[$paper->category_id];
        @endphp
        {{-- 最初のsuccess がなく、かつ、エラーがあれば --}}
        @if ((count($fileerrors) > 0 || count($enqerrors) > 0) && !session('feedback.success'))
            {{-- もし、査読結果を返している段階なら、投稿は完了しているので、違うメッセージを表示する --}}
            @if ($revreturn[$paper->category_id])
                @if ($need_camera_ready)
                    <x-alert.warning>{{ $cat->name_of_cameraready }}提出期限までに必要となる以下の入力・操作について、ご確認ください。</x-alert.warning>
                @else
                    <x-alert.warning>投稿いただき、ありがとうございました。（{{ $cat->name_of_cameraready }}の提出をされない場合、以下の指示への対応は基本的に不要です。）</x-alert.warning>
                @endif
            @else
                @if ($revedit_on[$paper->category_id])
                    <x-alert.warning>現在査読中です。</x-alert.warning>
                @else
                    <x-alert.error2>投稿はまだ完了していません。</x-alert.error2>
                @endif
            @endif
        @endif

        {{-- ファイルエラーは、投稿フェーズに関係なく、表示して大丈夫 --}}
        @foreach ($fileerrors as $er)
            <x-alert.error>{{ $er }}</x-alert.error>
        @endforeach
        {{-- アンケートエラーは、査読中は表示しない。査読中とは、revedit_on が true かつ、revreturn が false のとき。 --}}
        @php
            // 査読中かどうか
            $is_reviewing = $revedit_on[$paper->category_id] && !$revreturn[$paper->category_id];
        @endphp
        @if (count($fileerrors) == 0)
            @if (count($enqerrors) > 0)
                {{-- 査読中ではなく、かつ、（査読後で採択があるか、またはまだ査読前）なら、エラーを表示する --}}
                @if (!$is_reviewing && ($need_camera_ready || !$revedit_on[$paper->category_id]))
                    @foreach ($enqerrors as $er)
                        @if ($loop->iteration < 4)
                            <x-alert.error>{{ $er }}</x-alert.error>
                        @endif
                    @endforeach
                    @if (count($enqerrors) > 3)
                        <x-alert.error>（このほかに、ご回答いただく項目が、{{ count($enqerrors) - 3 }}項目あります。）</x-alert.error>
                    @endif
                @endif
            @else
                @if (!$paper->locked)
                    <x-alert.success>投稿に必要なファイルと情報は、そろっています。<br>
                        投稿完了通知は「投稿完了通知メールを送信」を押すと送信します。<br>
                        締め切り日時までは、ひきつづき修正可能です。</x-alert.success>
                @endif
                @php
                    $submit_finished = true;
                @endphp
            @endif
        @endif

        <div class="py-2 px-6">
            <div class="m-6">
                @if ($paper->can_upload_files())
                    <x-element.h1>ファイルをアップロードするには <span class="bg-lime-200 text-green-700 px-1 dark:bg-lime-500">Drop
                            Files
                            Here</span> にドラッグ＆ドロップしてください。
                        <div class="text-sm mx-4 mt-2">
                            複数のファイルをまとめてアップロードできます。ファイル種別は自動で認識します。
                            @php
                                if ($cat->is_accept_altpdf()){
                                    $gendo = array_map('intval', explode('-', $cat->altpdf_accept_end));
                                    $mes = "【概要説明スライド】のみ、{$gendo[0]}月{$gendo[1]}日まで追加可";
                                } else {
                                    $gendo = array_map('intval', explode('-', $cat->pdf_accept_end));
                                    $mes = "{$gendo[0]}月{$gendo[1]}日まで修正可";
                                }
                            @endphp
                            <x-element.gendospan>{{$mes}}</x-element.gendospan>
                        </div>
                    </x-element.h1>

                    <div class="py-4 px-6">
                        <x-element.filedropzone color="lime" :paper_id="$id"></x-element.filedropzone>
                    </div>
                @else
                    <span class="text-red-500 dark:text-red-400">（現在、投稿はロックされているため、この画面からのファイルアップロードはできません。）</span>
                @endif

                <div class="py-2 px-6">
                    {{-- ファイルアップロードがあると、#filelist の中身をAjaxでかきかえていく --}}
                    <div id="filelist"
                        class="grid xs:grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        {{-- @if (count($all) == 0)
                                <div class="text-3xl bg-yellow-200 p-4 rounded-md text-white text-center">No File</div>
                            @endif --}}
                        <x-file.elem :all="$all" />
                    </div>
                </div>
            </div>

            @if ($cat->show_bibinfo_btn && !$is_reviewing)
                <div class="m-6">
                    <x-element.h1>
                        PDFファイルをアップロードしたあとで、 <x-element.linkbutton
                            href="{{ route('paper.dragontext', ['paper' => $paper->id]) }}" color="blue"
                            size="md">
                            書誌情報の設定
                        </x-element.linkbutton>
                        を行ってください。
                        @if ($paper->locked)
                            <span class="text-red-500 dark:text-red-400">（現在、投稿はロックされているため、書誌情報の設定はできません。）</span>
                        @endif
                        <div
                            class="text-lg mt-2 ml-6 p-1 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                            現在設定されている書誌情報の確認
                            {{-- <x-element.gendospan>採択後に入力</x-element.gendospan> --}}
                        </div>
                        <div class="text-md mx-6 mt-0 dark:text-gray-500">
                            <table class="border-cyan-500 border-2">
                                @foreach ($koumoku as $k => $v)
                                    <tr
                                        class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50' : 'bg-white dark:bg-cyan-100' }}">
                                        <td class="px-2 py-1 whitespace-nowrap">{{ $v }}</td>
                                        @if (strlen($paper->{$k}) < 2)
                                            <td class="px-2 py-1 text-red-600 font-bold"
                                                id="confirm_{{ $k }}">（未設定）</td>
                                        @else
                                            <td class="px-2 py-1" id="confirm_{{ $k }}">
                                                {!! nl2br($paper->{$k}) !!}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </table>

                            <div class="mt-2 text-sm px-2  dark:text-gray-400">注：和文著者名の書き方は、以下の例に合わせてください。詳細は
                                [書誌情報の設定]→[和文著者名の設定方法を表示] を参照してください。</div>
                            <textarea id="jpex" name="jpexample" rows="3"
                                class="inline-flex mb-1 block p-2.5 w-full text-md text-gray-900 bg-gray-200 rounded-lg border border-gray-300
                             focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400
                              dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="投稿 太郎 (投稿大学)&#10;和布蕪 二郎 (和布蕪大学)&#10;昆布 巻子 (ダシ大学/昆布研究所)" readonly></textarea>

                        </div>
                    </x-element.h1>
                </div>
            @endif


            <div class="m-6">
                {{-- 査読中は、編集可能なアンケートは表示しない。（査読結果を返すタイミングでの設定変更が難しいため） --}}
                @if (!$is_reviewing)
                    @foreach ($enqs['canedit'] as $enq)
                        <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400">
                            {{ $enq->name }}
                            @if (!$enq->showonpaperindex)
                                &nbsp; → <x-element.linkbutton
                                    href="{{ route('enquete.pageedit', ['paper' => $paper, 'enq' => $enq]) }}"
                                    color="cyan">
                                    ここをクリックして回答
                                </x-element.linkbutton>
                            @endif
                            <x-element.gendospan>{{ $enqs['until'][$enq->id] }}まで修正可</x-element.gendospan>
                        </div>
                        @if ($enq->showonpaperindex)
                            <form action="{{ route('enquete.update', ['paper' => $paper->id, 'enq' => $enq]) }}"
                                method="post" id="enqform{{ $enq->id }}">
                                @csrf
                                @method('put')
                                <input type="hidden" name="paper_id" value="{{ $paper->id }}">
                                <input type="hidden" name="enq_id" value="{{ $enq->id }}">
                                <div class="mx-10">
                                    <x-enquete.edit :enq="$enq" :enqans="$enqans">
                                    </x-enquete.edit>
                                </div>
                            </form>
                        @endif
                    @endforeach
                @endif
                @foreach ($enqs['readonly'] as $enq)
                    <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                        {{ $enq->name }}
                        @if (!$enq->showonpaperindex)
                            &nbsp; → <x-element.linkbutton
                                href="{{ route('enquete.pageview', ['paper' => $paper, 'enq' => $enq]) }}"
                                color="cyan">
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

            <div class="m-6  dark:text-gray-400">
                投稿に必要な情報は、項目ごとに自動保存されます。情報を修正したあとは <x-element.linkbutton2
                    href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="lime">提出物の確認（再読み込み）
                </x-element.linkbutton2> を押してください。
            </div>

            <!-- Category:midtext -->
            @isset($cat->midtext)
                @if (strpos($cat->midtext, '__') !== 0)
                    <div class="m-6">
                        <x-element.h1>{!! $cat->midtext !!}</x-element.h1>
                    </div>
                @endif
            @endisset

            <div class="m-6">
                <div class="text-lg my-5 p-1 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                    @if ($submit_finished)
                        <div class="mx-5 my-5 bg-cyan-200 p-5">
                            投稿は完了しています。
                            <x-element.linkbutton href="{{ route('paper.sendsubmitted', ['paper' => $paper->id]) }}"
                                color="cyan" confirm="本当にメール送信しますか？">
                                投稿完了通知メールを送信
                            </x-element.linkbutton> を押すと、投稿完了通知をメールで受け取ることができます。
                        </div>
                    @else
                        @if ($revreturn[$paper->category_id])
                            @if ($need_camera_ready)
                                <x-alert.warning>{{ $cat->name_of_cameraready }}提出期限までに必要となる入力・操作について、ページ上部をご確認ください。</x-alert.warning>
                            @else
                                <x-alert.warning>投稿いただき、ありがとうございました。</x-alert.warning>
                            @endif
                        @else
                            <div class="mx-5 my-5 bg-red-600 p-5 text-white font-bold text-2xl">
                                投稿はまだ完了していません。画面上部の指示に従ってください。
                            </div>
                        @endif
                    @endif

                    @if ($paper->locked)
                        <div class="mx-5 my-5">
                            <span class="text-red-500 dark:text-red-400">現在、投稿はロックされているため、投稿者による削除はできません。</span>
                        </div>
                    @else
                        @if (!$revreturn[$paper->category_id])
                            <div class="mx-5 my-5">
                                投稿をとりやめるときは
                                <x-element.deletebutton_nodiv
                                    action="{{ route('paper.destroy', ['paper' => $paper->id]) }}"
                                    confirm="アップロードファイルも消えますが、本当にPaperID : {{ $id_03d }} 投稿を削除してよいですか？"> PaperID
                                    :
                                    {{ $id_03d }} 投稿を削除
                                </x-element.deletebutton_nodiv> を押してください。
                            </div>
                        @else
                            <div class="my-5"></div>
                        @endif
                    @endif

                    <div class="mx-6 my-2">
                        <div class="container">
                            <x-element.button class="" id="toggleButton" value="投稿連絡用メールアドレス修正画面の開閉"
                                color='yellow' size='md' onclick="openclose('editcontact')">
                            </x-element.button>

                        </div>
                        <div class="hidden-content mt-2 bg-yellow-200 dark:bg-cyan-600 p-2" id="editcontact"
                            style="display:none;">
                            <x-paper.contactemail :paper="$paper">
                            </x-paper.contactemail>
                        </div>
                    </div>
                </div>


                {{-- <div class="mt-4 px-6 mb-10">
                    <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
                        &larr; 投稿一覧に戻る
                    </x-element.linkbutton>
                </div> --}}

                {{-- <x-element.sankou>
                    参考：投稿締め切り後の流れは、およそ以下のようになります。
                    <ol class="list-decimal px-8 pt-4">
                        <li> 査読結果（採否）の通知</li>
                        <li> （採択の場合）コメントに対応したPDF（カメラレディ）の再アップロード、
                            <x-element.linkbutton2 href="#authorlist" color="teal" size="md">
                                著者名と所属の入力
                            </x-element.linkbutton2>
                            、<x-element.linkbutton2 href="{{ route('paper.dragontext', ['paper' => $paper->id]) }}"
                                color="cyan" size="md">
                                書誌情報の設定
                            </x-element.linkbutton2>
                            など。
                        </li>
                    </ol>
                    原稿または入力事項に問題がある場合は、個別に連絡しますので、期日までにすみやかに対応してください。
                </x-element.sankou> --}}

                {{-- <div class="mx-6 mt-12 mb-0  dark:text-gray-400">
                投稿連絡用メールアドレスを修正する必要がある場合は、この下のフォームで送信してください。
            </div> --}}


                {{-- <div class="my-10"></div>

                {{-- 著者名と所属 
                @if (!$paper->locked)
                    <x-paper.authorlist :paper="$paper">
                    </x-paper.authorlist>
                @endif --}}

            </div>
        </div>

        <div class="mt-0 px-6 pb-10">
            <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
                &larr; 投稿一覧に戻る
            </x-element.linkbutton>
        </div>

        @push('localjs')
            <script src="/js/jquery.min.js"></script>
            <script src="/js/drop_zone_upload.js"></script>
            <script src="/js/form_changed.js"></script>
            <script src="/js/openclose.js"></script>
        @endpush

</x-app-layout>
