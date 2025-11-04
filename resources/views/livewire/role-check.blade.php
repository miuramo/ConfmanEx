<div class="mt-4">
    <div class="font-bold">{{$role->desc}}({{$role->name}}) {{$role->users->count()}}名</div>

    <div class="pl-4">
        @foreach ($role->users as $user)
            <span class="inline-block bg-slate-200 rounded-md p-1 mb-0.5 dark:bg-slate-600 dark:text-gray-300">
                {{$user->name}} ({{$user->affil}})
            </span>
        @endforeach

        @if(!$invitemode)
        <button wire:click="open_invite" class="mt-2 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 dark:bg-blue-700 dark:hover:bg-blue-800">
            既存ユーザを検索して「{{$role->desc}}」に追加する
        </button>
        @else
        <div class="mt-0 bg-blue-100 p-3 rounded dark:bg-blue-900">
            <input id="id_regist_searchbox" type="text" wire:model.live.debounce.500ms="search" wire:keydown.escape="resetSearch"
        placeholder="氏名・メール等で検索" size="25" x-init="$el.focus()" class="p-1" />
        <span class="ml-2 text-blue-500 font-bold">既存ユーザを検索して【{{$role->desc}}】に追加します。
        </span>
        <span class="ml-2 text-red-400 font-bold">※注意：この画面ではRoleからの脱退操作はできません</span>
        <table class="text-sm border-collapse border border-slate-400 mt-2">
            <tr class="bg-slate-300">
                <th class="px-2">UserID</th>
                <th class="px-2">氏名</th>
                <th class="px-2">所属</th>
                <th class="px-2">メールの先頭</th>
                <th class="px-2">操作</th>
            </tr>
            @foreach ($users as $u)
                <tr
                    class="hover:bg-yellow-50 {{ $loop->iteration % 2 === 0
                        ? 'bg-slate-200 dark:bg-slate-700'
                        : 'bg-slate-50 dark:bg-slate-600' }} ">
                    <td class="px-2 bg-slate-200 text-right">{{ $u->id }}</td>
                    <td class="px-2 bg-slate-200">{{ $u->name }}</td>
                    <td class="px-2 bg-slate-200">{{ $u->affil }}</td>
                    <td class="px-2 bg-slate-200">{{ substr($u->email, 0, 10) }}...</td>
                    <td class="px-2 bg-slate-200">
                        <button wire:click="addUser({{ $u->id }})" class="px-2 py-1 bg-cyan-500 text-white rounded hover:bg-cyan-600 dark:bg-cyan-700 dark:hover:bg-cyan-800">
                            追加
                        </button>
                    </td>
                </tr>
            @endforeach
        </table>
        <button wire:click="close_invite" class="mt-2 px-3 py-1 bg-gray-500 text-white rounded hover:bg-gray-600 dark:bg-gray-700 dark:hover:bg-gray-800">
            キャンセル
        </button>
        </div>
        @endif
    </div>
</div>
