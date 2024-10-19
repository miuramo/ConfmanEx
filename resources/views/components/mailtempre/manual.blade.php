<!-- components.mailtempre.manual -->
<div class="flex">
    <div class="mx-2 my-5 flex-grow dark:text-gray-200">
        雛形(subject,body)で使える埋め込み文字列
        @php
            $a = ['PID' => 'PaperID', 'TITLE' => 'タイトル', 'OWNER'=>'投稿者の「所属 氏名 様」', 'ACCNAME'=>'採択Name', 'CATNAME'=>'投稿カテゴリ'];
        @endphp
        <table class="border-pink-200 border-2">
            @foreach ($a as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-500' }}">
                    <td class="px-2 py-1">[:{{ $k }}:]</td>
                    <td class="px-2 py-1">{{ $v }}</td>
                </tr>
            @endforeach
        </table>
        @php
            $u = ['NAME' => '氏 名', 'AFFIL' => '所属', 'EMAIL' => 'メール', 'URL_FORGETPASS' => 'パスワード再設定メール発行URL'];
        @endphp
        <table class="border-cyan-200 border-2">
            @foreach ($u as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50 dark:bg-cyan-400' : 'bg-white  dark:bg-cyan-500' }}">
                    <td class="px-2 py-1">[:{{ $k }}:]</td>
                    <td class="px-2 py-1">{{ $v }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-2 my-5 flex-grow dark:text-gray-300">
        to に指定できる文字列
        @php
            $a = [
                'accept(catid)' => 'catidで採択 (judgeが正)',
                'reject(catid)' => 'catidで不採択 (judgeが負)',
                'paperid(pid1,pid2, ...)' => 'PaperIDの羅列',
                'acc_id(accid1,accid2, ...)' => '採択IDの羅列',
                'acc_judge(judge1,judge2, ...)' => '採択ジャッジ値の羅列',
                'nofile(catid1,catid2,...)' => 'ファイル無し',
                'noauthorlist(catid)' => '著者リスト未記入',
                'oldfile(catid, "2024-07-10")' => '期日以前のファイル',
                'nobib(catid1,catid2,...)' => '書誌情報なし(和文アブスト、和文キーワード、英文Titleのうち1つ以上)',
                '' => '',
            ];
        @endphp
        <table class="border-pink-200 border-2">
            @foreach ($a as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-500' }}">
                    <td class="px-2 py-1">{{ $k }}</td>
                    <td class="px-2 py-1">{{ $v }}</td>
                </tr>
            @endforeach
        </table>
        @php
            $u = [
                'userid(uid1,uid2, ...)' => 'UserIDの羅列',
                'roleid(roleid1, roleid2, ...)' => 'RoleIDの羅列',
                'roleid_noaccess(roleid1, roleid2, ...)' => 'RoleIDで未アクセス',
                'miss_bid()' => 'Bidding未完了',
                'norev()' => '査読未完了',
                'norev_cat(catid)' => '査読未完了(category限定)',
                'norev_catmeta(catid, 0|1)' => '査読未完了(ismeta=1)',
                'notdownloaded(catid)' => '未ダウンロード',
            ];
        @endphp
        <table class="border-cyan-200 border-2">
            @foreach ($u as $k => $v)
                <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-cyan-50 dark:bg-cyan-400' : 'bg-white  dark:bg-cyan-500' }}">
                    <td class="px-2 py-1">{{ $k }}</td>
                    <td class="px-2 py-1">{{ $v }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="mx-2 my-5 flex-grow dark:text-gray-300">
        現在の採択ID、採択ジャッジ値
        @php
            $acc = App\Models\Accept::where('name','not like',"予備%")->get();
        @endphp
        <table>
            <thead>
                <tr class="bg-pink-200 dark:bg-pink-600">
                    <th class="px-2 text-center">
                        accID
                    </th>
                    <th class="px-2 text-center">
                        Name
                    </th>
                    <th class="px-2 text-center">
                        Judge
                    </th>
                </tr>
            </thead>
            @foreach ($acc as $a)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-500' }}">
                    <td class="px-2 py-1 text-center">{{ $a->id }}</td>
                    <td class="px-2 py-1 text-center">{{ $a->name }}</td>
                    <td class="px-2 py-1 text-center">{{ $a->judge }}</td>
                </tr>
            @endforeach
        </table>

        現在のRole一覧
        @php
            $role = App\Models\Role::all();
        @endphp
        <table>
            <thead>
                <tr class="bg-green-200 dark:bg-green-600">
                    <th class="px-2 text-center">
                        roleID
                    </th>
                    <th class="px-2 text-center">
                        Name
                    </th>
                </tr>
            </thead>
            @foreach ($role as $r)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-green-50 dark:bg-green-400' : 'bg-white  dark:bg-green-500' }}">
                    <td class="px-2 py-1 text-center">{{ $r->id }}</td>
                    <td class="px-2 py-1 text-center">{{ $r->desc }}</td>
                </tr>
            @endforeach
        </table>

        現在のカテゴリ一覧
        @php
            $cat = App\Models\Category::all();
        @endphp
        <table>
            <thead>
                <tr class="bg-blue-200 dark:bg-blue-600">
                    <th class="px-2 text-center">
                        catID
                    </th>
                    <th class="px-2 text-center">
                        Name
                    </th>
                </tr>
            </thead>
            @foreach ($cat as $c)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-blue-50 dark:bg-blue-400' : 'bg-white  dark:bg-blue-500' }}">
                    <td class="px-2 py-1 text-center">{{ $c->id }}</td>
                    <td class="px-2 py-1 text-center">{{ $c->name }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
