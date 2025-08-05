<div>
    {{ count($norole_users) }}件の役職なしユーザがいます。
    <div class="mt-4">
        <input type="button" wire:click="softdelete_norole_nopaper_users" class="bg-orange-300 text-white text-sm hover:bg-orange-600 p-2 rounded-lg" value="役職なし＆発表なしのユーザを論理削除"/>
        <div class="flex items-center justify-between p-2 bg-gray-100 rounded mb-2">
            <ul>
                @foreach ($norole_users as $u)
                    <li>{{ $u->id }} {{ $u->name }} ({{ $u->email }})
                        @foreach ($u->papers as $p)
                            <span class="text-sm text-green-500">{{ sprintf(' %03d %s', $p->id, $p->title) }}</span>
                        @endforeach
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
