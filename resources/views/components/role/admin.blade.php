@php
    $roles = auth()->user()->roles;
    $roleall = App\Models\Role::all();

    // Setting seeder
    App\Models\Setting::seeder();
    // Confirm seeder
    App\Models\Confirm::seeder_policy();

    // Userが存在しないContactを参照していたら、直す
    App\Models\User::fix_broken_contact_all();

@endphp
<div class="px-4 py-4">
    <x-element.h1>あなたのRole</x-element.h1>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @foreach ($roles as $role)
            <span>
                <x-element.linkbutton href="{{ route('role.top', ['role' => $role->name]) }}" color="cyan">
                    {{ $role->desc }}
                </x-element.linkbutton>
            </span>
        @endforeach
    </div>
</div>
{{-- @if (session('feedback.success'))
    <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
@endif
@if (session('feedback.error'))
    <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
@endif --}}

<div class="px-4">
    <x-element.h1>CRUD</x-element.h1>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-element.linkbutton color="cyan" href="{{ route('admin.crud') }}" target="_blank">
                CRUD
            </x-element.linkbutton>
            @php
                $shortcuts = [
                    'Setting' => 'settings',
                    'LogAccess' => 'log_accesses',
                    'EnqueteConfig' => 'enquete_configs',
                    'Enquete' => 'enquetes',
                    'EnqueteItems' => 'enquete_items',
                ];
            @endphp
            @foreach ($shortcuts as $key => $tbl)
                <span class="mx-2"></span>
                <x-element.linkbutton color="cyan" href="{{ route('admin.crud', ['table' => $tbl]) }}"
                    target="_blank">
                    {{ $key }}
                </x-element.linkbutton>
            @endforeach
        </div>
    </div>

    <x-element.h1>権限・Roleの管理 <span class="ml-10 text-blue-600 font-bold">凡例：</span><span
            class="mx-1 bg-slate-500 text-white rounded-md py-1 px-2"><sub>（RoleID）</sub>Role名
            n=人数</span></x-element.h1>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @foreach ($roleall as $role)
            <span>
                <x-element.linkbutton href="{{ route('role.edit', ['role' => $role->name]) }}" color="slate"
                    target="_blank">
                    <sub>({{ $role->id }})</sub>{{ $role->desc }} n={{ $role->users->count() }}
                </x-element.linkbutton>
            </span>
        @endforeach
    </div>

    <x-element.h1>各種設定・ファイルの保存先</x-element.h1>
    <div class="py-2">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 dark:text-gray-300">
            @php
                $fileput_dir = App\Models\Setting::where('name', 'FILEPUT_DIR')->first()['value'];
                $domain = config('database.default');
                $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');
                $apf = App\Models\File::apf();
                $pf = App\Models\File::pf();
                $queuework_date = App\Models\Setting::getval('LAST_QUEUEWORK_DATE');
            @endphp
            App::environment(APP_ENV): {{ config('app.env') }} <span class="mx-4"></span>
            (use "production" for https)<br>
            App::environment(APP_DEBUG): {{ env('APP_DEBUG') }} <span class="mx-4"></span>1の場合、login-asが使える<br>
            DB_Setting FILEPUT_DIR: {{ $fileput_dir }} <br>
            DB_Setting LAST_QUEUEWORK_DATE: {{ $queuework_date }}<br>
            config('database.default'): {{ $domain }} <br>
            config('database.connections.[default].database'): {{ $db_name }} <br>
            File::$filedir: {{ App\Models\File::$filedir }}<br>
            File::apf(): {{ $apf }} <br>
            File::pf(): {{ $pf }} <br>
            Laravel: v{{ Illuminate\Foundation\Application::VERSION }}<br>
            PHP: v{{ PHP_VERSION }}<br>
            upload_max_filesize: {{ ini_get('upload_max_filesize') }}<br>
            post_max_size: {{ ini_get('post_max_size') }}<br>


        </div>
    </div>

    <x-element.h1>問題のあるメールアドレスの管理</x-element.h1>
    <div class="mx-10 py-4">
        <form action="{{ route('admin.disable_email') }}" method="post" id="disable_email">
            @csrf
            @method('post')
            <div class="mb-1">
                <label for="invalid_email"
                    class="block text-sm font-medium text-gray-900 dark:text-white">無効にするメールアドレス</label>
                <input id="invalid_email" name="invalid_email" size="60"
                    class="mb-1 block p-1 w-3/6 text-sm text-gray-900 bg-gray-50 rounded-lg dark:bg-slate-800 dark:text-slate-400 border border-gray-300 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <div>
                    <input type="checkbox" id="dryrun_chk" name="dryrun" checked switch>
                    <label class="form-check-label dark:text-gray-300" for="dryrun_chk">
                        Dry Run (本当に実行したいときはチェックを外す)
                    </label>
                </div>
                <x-element.submitbutton color="red" value="9999">
                    無効にする
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <x-element.h1>その他：
        <x-element.linkbutton href="{{ route('admin.chkexefiles') }}" color="slate">
            必要なプログラムの確認
        </x-element.linkbutton>
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('admin.rebuildpdf') }}" color="slate">
            rebuildPDFThumb
        </x-element.linkbutton>
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('admin.test9w') }}" color="slate">
            test QueueWork
        </x-element.linkbutton>
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
        {{-- <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.mailtest') }}" color="pink">
            mailtest
        </x-element.linkbutton> --}}
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.paperlist_headimg') }}" color="yellow">
            切り取り画像の確認
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.paperauthorhead') }}" color="cyan">
            第一著者名の設定
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.importpaperjson') }}" color="cyan">
            Import Paper JSON
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('revcon.fill_biddingpost') }}" color="lime">
            Fill Bidding
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.upsearch') }}" color="orange">
            検索
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('add_invited_paper') }}" color="blue">
            招待論文の作成
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('regist.index') }}" color="lime">
            参加登録
        </x-element.linkbutton>

    </x-element.h1>

    @if (env('APP_DEBUG') && auth()->id() == 1)
        <div class="text-sm mx-10">
            採択タグごとの、ランダムなPaperIDとその投稿者IDの表示<br>
            {{-- @foreach (\App\Models\Category::where('status__revreturn_on', 1)->get() as $cat) --}}
            @foreach (\App\Models\Category::all() as $cat)
                @php
                    $combined = App\Models\Accept::random_pids_for_each_accept($cat->id);
                    $show = $combined['show'];
                    $showpid = $combined['showpid'];
                    $metarev = $combined['metarev'];
                    $rev = $combined['rev'];
                    $accepts = $combined['accepts'];
                @endphp
                <span class="font-bold border border-blue-600 p-1 m-1">{{$cat->name}}</span><br>
                @foreach ($show as $accid => $random_paper_owner)
                    {{ $accepts[$accid] }}: {{ $showpid[$accid] }} -
                    <x-element.linkbutton href="{{ route('role.login-as', ['user' => $random_paper_owner]) }}"
                        color="purple">{{ $random_paper_owner }}</x-element.linkbutton>
                    --
                    @isset($metarev[$accid])
                        <x-element.linkbutton href="{{ route('role.login-as', ['user' => $metarev[$accid]->user_id]) }}"
                            color="orange">meta</x-element.linkbutton>
                    @endisset
                    -
                    @isset($rev[$accid])
                        <x-element.linkbutton href="{{ route('role.login-as', ['user' => $rev[$accid]->user_id]) }}"
                            color="lime">rev</x-element.linkbutton>
                    @endisset
                @endforeach
                <br>
            @endforeach
        </div>
        <div class="text-sm mx-10">
            シェファーディング対象論文を担当しているメタ査読者<br>
        </div>
    @endif

    <x-element.h1>Danger Zone：
        <x-element.linkbutton href="{{ route('admin.resetbidding') }}" color="pink"
            confirm="RevConflict::truncate() で、利害表明 / Biddingがすべて消えます。本当に、リセットしてよいですか？">
            利害表明 / Bidding をすべてリセットする
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('enq.resetenqans') }}" color="blue">
            アンケート・参加登録回答の選択的削除
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('score.resetscore') }}" color="purple">
            査読回答の選択的削除
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.forcedelete') }}" color="lime"
            confirm="ソフトデリートしたUserがすべて消えますが、よろしいですか？">
            ソフトデリート済みUserの削除
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('file.cleanup_files') }}" color="cyan">
            削除済みファイル管理
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.resetpaper') }}" color="red"
            confirm="ユーザやロール、設定、アクセスログ以外、すべて消えます。ファイルを含め、事前にバックアップをとってください。本当に、リセットしてよいですか？">
            投稿をすべてリセットする
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.resetregist') }}" color="purple"
            confirm="参加登録(regists table)が消えます。本当に、リセットしてよいですか？">
            参加登録をすべてリセットする
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.resetaccesslog') }}" color="orange"
            confirm="アクセスログが消えます。本当に、リセットしてよいですか？">
            アクセスログをすべてリセットする
        </x-element.linkbutton>
        <span class="px-5"></span>
        <x-element.linkbutton href="{{ route('admin.gen_dlkey') }}" color="yellow"
            confirm="AWARDJSON_DLKEYの先頭4文字が、CONFTITLE_YEARと異なる場合のみ、変更します。変更してよいですか？">
            AWARDJSON_DLKEYの変更
        </x-element.linkbutton>
    </x-element.h1>


</div>
