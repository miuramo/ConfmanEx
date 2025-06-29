<x-app-layout>
    <!-- role.edit -->
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            Role『{{ $role->desc }}』のメンバー編集 (RoleID: {{ $role->id }})
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

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            Roleの編集：
            @foreach ($roles as $ro)
                <span>
                    <x-element.linkbutton href="{{ route('role.edit', ['role' => $ro->name]) }}" color="slate">
                        {{ $ro->desc }}
                    </x-element.linkbutton>
                </span>
            @endforeach
        </div>
    </div>


    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-2">
                <x-element.button onclick="CheckAll('addusertorole')" color="lime" size="sm" value="すべてチェック">
                </x-element.button>
                &nbsp;
                <x-element.button onclick="UnCheckAll('addusertorole')" color="orange" size="sm" value="すべてチェック解除">
                </x-element.button>
            </div>

            {{-- メンバーリストは別ページ --}}
            <x-role.members :users="$users" :role="$role" chkfor="addusertorole">
            </x-role.members>
        </div>
    </div>

    <form action="{{ route('role.editpost', ['role' => $role->name]) }}" method="post" id="addusertorole">
        @csrf
        @method('post')

        <div class="mx-6">
            <x-element.button class="" id="toggleButton" value="メール送信パネルを開く／閉じる" color='pink'
                onclick="openclose('content')">
            </x-element.button>
            <span class="mx-2"></span>
            <x-element.button class="" id="toggleButton" value="他のRole追加パネルを開く／閉じる" color='cyan'
                onclick="openclose('otherroles')">
            </x-element.button>
            <span class="mx-2"></span>
            <x-element.button class="" id="toggleButton" value="Role削除パネルを開く／閉じる" color='red'
                onclick="openclose('leaverole')">
            </x-element.button>
            <span class="mx-2"></span>
            <x-element.button class="" id="toggleButton" value="mailnotify修正パネルを開く／閉じる" color='yellow'
                onclick="openclose('editnotify')">
            </x-element.button>

            <div class="container">
                <div class="hidden-content mt-2 bg-pink-200 dark:bg-pink-600 p-2" id="content" style="display:none;">
                    subject: <input class="w-3/4 p-1 text-sm text-black  bg-white dark:text-gray-200 dark:bg-gray-800"
                        type="text" name="subject" value="[[:CONFTITLE:]] 投稿・査読システムのログイン方法">
                    <textarea class="w-full p-1 text-sm text-black  bg-white dark:text-gray-200 dark:bg-gray-800" name="body"
                        id="" cols="80" rows="10">[:AFFIL:] [:NAME:] 様

[:CONFTITLE:] プログラム委員長です。

以下の手順にしたがって、[:CONFTITLE:] 投稿・査読システムのパスワードを設定してください。

(1) [[:URL_FORGETPASS:]]([:URL_FORGETPASS:]) にて、[:EMAIL:] を入力してください。

しばらくすると、パスワード再設定メールがとどきます。

(2) パスワード再設定メールに書かれたURLから、パスワードを設定してください。


[:CONFTITLE:] 投稿システムのURLは、[[:APP_URL:]]([:APP_URL:])  です。
</textarea>
                    <x-element.submitbutton value="mailsend" color="pink">
                        チェックをいれた人に、メール送信
                    </x-element.submitbutton>
                    （注：ボタンを押すとすぐにメール送信します。確認画面はありません。メールを送信した後で、雛形を作成します。）

                    <div class="m-4">
                        または、雛形
                        @php
                            $mts = App\Models\MailTemplate::orderBy('updated_at', 'desc')->get();
                        @endphp
                        <select name="mailtemplate" id="mailtemplate"
                            class="w-3/4 p-1 text-sm text-black  bg-white dark:text-gray-200 dark:bg-gray-800">
                            <option value="">メールテンプレートを選択</option>
                            @foreach ($mts as $mt)
                                <option value="{{ $mt->id }}">(ID: {{ $mt->id }}) {{ $mt->subject }}
                                    【{{ $mt->to }}】</option>
                            @endforeach
                        </select><br>
                        のsubjectとbodyを利用し、
                        <x-element.submitbutton value="addtemplate" color="pink">
                            チェックをいれた人をTo:に指定した雛形を、新規作成
                        </x-element.submitbutton>
                        <div>
                        （注：上の「新規作成」を押しても、メールは送信しません。
                        <x-element.linkbutton href="{{route('mt.index')}}" color="pink" size="sm">メール雛形一覧</x-element.linkbutton>
                        から送信してください。）
                        </div>
                    </div>
                </div>

                <div class="hidden-content mt-2 bg-cyan-200 dark:bg-cyan-600 p-2" id="otherroles" style="display:none;">

                    @foreach ($roles as $ro)
                        @if ($ro->name != $role->name)
                            <span>
                                <input type="checkbox" id="id_{{ $ro->name }}" name="ROLE_{{ $ro->id }}"
                                    value="on" />
                                <label for="id_{{ $ro->name }}" class="mr-4">{{ $ro->desc }}</label>
                            </span>
                        @endif
                    @endforeach

                    <x-element.submitbutton value="otherroles" color="cyan">
                        チェックをいれた人に、選択したRoleを追加する
                    </x-element.submitbutton>
                </div>


                <div class="hidden-content mt-2 bg-red-200 dark:bg-red-600 p-2" id="leaverole" style="display:none;">

                    <x-element.submitbutton value="leaverole" color="red" confirm="チェックした人のRole『{{ $role->desc }}』をまとめて削除してよいですか？（ユーザは消えません）">
                        チェックをいれた人のRole『{{ $role->desc }}』をまとめて削除する
                    </x-element.submitbutton>
                    <span class="mx-2"></span> Role『{{ $role->desc }}』からは脱退しますが、ユーザ情報は残ります。
                </div>



                <div class="hidden-content mt-2 bg-yellow-200 dark:bg-yellow-600 p-2" id="editnotify"
                    style="display:none;">

                    <input type="hidden" name="notify" value="off" />
                    <input type="checkbox" id="id_notify" name="notify" value="on" />
                    <label for="id_notify" class="mr-4">通知するならチェック、しないならチェックをはずす</label>

                    <x-element.submitbutton value="editnotify" color="yellow">
                        チェックをいれた人の、mailnotifyを修正する
                    </x-element.submitbutton>
                </div>
            </div>
        </div>

        <div class="mx-6 my-2">
            <div class="text-lg mt-6 my-2 p-3 bg-slate-300 rounded-lg dark:bg-slate-800 dark:text-slate-400">
            <input id="search-box" placeholder="ユーザを検索" type="text"
                    name="query" value="" class="text-sm px-2 py-1 text-teal-700 bg-teal-100" size=20>
            <ul id="results" class="list-disc list-inside">
            </ul>
            </div>
        </div>

        <div class="mx-6 my-2">
            <div class="text-lg mt-10 my-2 p-3 bg-slate-300 rounded-lg dark:bg-slate-800 dark:text-slate-400">
                <div class="mb-3">
                    <label for="contact"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">追加するユーザを【氏 名 (所属)
                        メールアドレス】またはタブ区切り（Excelのセルから氏 名・所属・メールの3列でコピー）の形式で入力。
                        <br>
                        ここではユーザ作成とRole『{{ $role->desc }}』に追加するだけで、メール送信はしません。<br>
                        メールアドレスのみの場合、既存ユーザを検索して<b>見つかったときのみ</b> Role『{{ $role->desc }}』に追加します。</label>

                    <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="p-0">
                            <div class="text-sm px-2  dark:text-gray-400">追加ユーザ</div>
                            <textarea id="adduser" name="adduser" rows="5"
                                class="mx-1 block p-2.5 text-lg w-full text-gray-900 bg-gray-50 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-900 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="昆布 太郎 (昆布大) kombu@email.com&#10;和布蕪 二郎 (和布蕪大) mekabu@email.com&#10;hoge@email.com"></textarea>
                        </div>
                        <div class="p-0">
                            <div class="text-sm px-2  dark:text-gray-400">追加ユーザの入力例（括弧は半角・全角どちらも可）</div>
                            <textarea id="example_adduser" name="example_adduser" rows="3" readonly
                                class="mx-1 block p-2.5 text-lg w-full text-gray-900 bg-gray-200 rounded-lg border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                                placeholder="昆布 太郎 (昆布大) kombu@email.com&#10;和布蕪 二郎 (和布蕪大) mekabu@email.com&#10;hoge@email.com"></textarea>
                        </div>
                    </div>



                </div>

                <x-element.submitbutton value="adduser" color="cyan">
                    ユーザ作成とRole『{{ $role->desc }}』への追加
                </x-element.submitbutton>
            </div>

        </div>

        <div class="mx-6 my-2">
            <x-element.submitbutton value="excel" color="lime">
                Role『{{ $role->desc }}』のメンバーをExcel出力
            </x-element.submitbutton>
        </div>

    </form>
    @php
        // REVIEWER_MEMBER をチェックして、まだアカウントがない人を表示する
        $mem = App\Models\Setting::where('name', strtoupper($role->name) . '_MEMBER')
            ->where('valid', true)
            ->first();
        if ($mem != null) {
            $noreg_members = [];
            $ary = explode('|', $mem->value);
            foreach ($ary as $n => $name) {
                $uobj = App\Models\User::where('name', $name)->first();
                if ($uobj == null) {
                    $noreg_members[] = $name;
                }
            }
        }
    @endphp
    @isset($noreg_members)
        <div class="mx-6 my-2">
            <x-element.h1>{{ strtoupper($role->name) }}_MEMBER に登録されているが、まだアカウントがない人</x-element.h1>
            <div class="mx-4">
                {{ implode('，', $noreg_members) }}
            </div>
        </div>
    @endisset

    <script>
        var searchUrl = "{{ route('user.search') }}";
        var rolename = "{{ $role->name }}";
    </script>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/openclose.js"></script>
        <script src="/js/chk_all.js"></script>
        <script src="/js/usearch.js"></script>
        <script src="/js/sortable.js"></script>
    @endpush




</x-app-layout>
