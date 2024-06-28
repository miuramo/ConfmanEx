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

    <div class="py-2">
        @if (session('feedback.success'))
            <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
        @endif
        @if (session('feedback.error'))
            <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
        @endif

        {{-- 最初のsuccess がなく、かつ、エラーがあれば --}}
        @if ( (count($fileerrors) > 0 || count($enqerrors) > 0) && !session('feedback.success') )
            <x-alert.error2>投稿はまだ完了していません。</x-alert.error2>
        @endif
        @foreach ($fileerrors as $er)
            <x-alert.error>{{ $er }}</x-alert.error>
        @endforeach
        @if (count($fileerrors) == 0)
            @if (count($enqerrors) > 0)
                @foreach ($enqerrors as $er)
                    @if ($loop->iteration < 4)
                        <x-alert.error>{{ $er }}</x-alert.error>
                    @endif
                @endforeach
                @if (count($enqerrors) > 3)
                    <x-alert.error>（このほかに、ご回答いただく項目が、{{ count($enqerrors) - 3 }}項目あります。）</x-alert.error>
                @endif
            @else
                <x-alert.success>投稿に必要なファイルと情報は、そろっています。<br>投稿完了通知は「投稿状況メールを送信」をおすと送信します。<br>締め切り日時までは、ひきつづき修正可能です。</x-alert.success>

            @endif

        @endif

        <div class="py-2 px-6">
            <!-- Category:leadtext -->
            @isset($cat->leadtext)
                @if (strpos($cat->leadtext, '__') !== 0)
                    <div class="m-6">
                        <x-element.h1>{!! $cat->leadtext !!}</x-element.h1>
                    </div>
                @endif
            @endisset

            <div class="m-6">
                <x-element.h1>ファイルをアップロードするには <span class="bg-lime-200 text-green-700 px-1 dark:bg-lime-500">Drop Files
                        Here</span> にドラッグ＆ドロップしてください。
                    <div class="text-sm mx-4 mt-2">
                        複数ファイルまとめてアップロードできます。ファイル種別は自動で認識します。
                    </div>
                </x-element.h1>

                <div class="py-4 px-6">
                    <x-element.filedropzone color="lime" :paper_id="$id"></x-element.filedropzone>
                </div>

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

            <div class="m-6">
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
                投稿に必要な情報は、項目ごとに自動保存されます。正しく保存されているかどうか確認するには <x-element.linkbutton2
                    href="{{ route('paper.edit', ['paper' => $paper->id]) }}" color="lime">再読み込み
                </x-element.linkbutton2> を押してください。
            </div>

            <div class="m-6">
                <div class="text-lg my-5 p-1 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                    <div class="mx-5 my-5">
                        投稿が正しく完了しているとき、
                        <x-element.linkbutton href="{{ route('paper.sendsubmitted', ['paper' => $paper->id]) }}"
                            color="cyan" confirm="本当にメール送信する？">
                            投稿状況メールを送信
                        </x-element.linkbutton> を押すと、現在の投稿状況をメールで受け取ることができます。
                    </div>

                    @if ($paper->locked)
                        <div class="mx-5 my-5">
                            <span class="text-red-500 dark:text-red-400">現在、投稿はロックされているため、投稿者による削除はできません。</span>
                        </div>
                    @else
                        <div class="mx-5 my-5">
                            投稿をとりやめるときは
                            <x-element.deletebutton_nodiv
                                action="{{ route('paper.destroy', ['paper' => $paper->id]) }}"
                                confirm="アップロードファイルも消えますが、本当にPaperID : {{ $id_03d }} 投稿を削除してよいですか？"> PaperID :
                                {{ $id_03d }} 投稿を削除
                            </x-element.deletebutton_nodiv> を押してください。
                        </div>
                    @endif

                    <div class="mx-6 my-2">
                        <div class="container">
                            <x-element.button class="" id="toggleButton" value="投稿連絡用メールアドレス修正画面の開閉"
                                color='yellow' size='sm' onclick="openclose('editcontact')">
                            </x-element.button>

                            <span class="mx-2"></span>
                            <x-element.linkbutton2 href="{{ route('paper.dragontext', ['paper' => $paper->id]) }}"
                                color="cyan" size="md">
                                書誌情報の設定
                            </x-element.linkbutton2>
                            @if ($paper->locked)
                                <span class="text-red-500 dark:text-red-400">（現在、投稿はロックされているため、書誌情報の設定はできません。）</span>
                            @endif
                        </div>
                        <div class="hidden-content mt-2 bg-yellow-200 dark:bg-cyan-600 p-2" id="editcontact"
                            style="display:none;">
                            <x-paper.contactemail :paper="$paper">
                            </x-paper.contactemail>
                        </div>
                    </div>
                </div>


                <div class="mt-4 px-6 mb-10">
                    <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
                        &larr; 投稿一覧に戻る
                    </x-element.linkbutton>
                </div>

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

        {{-- <div class="mt-4 px-6 pb-10">
            <x-element.linkbutton href="{{ route('paper.index') }}" color="gray" size="lg">
                &larr; 投稿一覧に戻る
            </x-element.linkbutton>
        </div> --}}

        @push('localjs')
            <script src="/js/jquery.min.js"></script>
            <script src="/js/drop_zone_upload.js"></script>
            <script src="/js/form_changed.js"></script>
            <script src="/js/openclose.js"></script>
        @endpush

</x-app-layout>
