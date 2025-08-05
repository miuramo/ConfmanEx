@props([
    'users' => [],
    'role' => null,
    'heads' => ['chk','uid', 'name','affil','email','mailnofity','last_access','last_login_at','created_at','(action)','i'],
    'chkfor' => null,
])
<!-- components.role.members -->
<table class="min-w-full divide-y divide-gray-200 sortable" id="members">
    <thead>
        <tr>
            @foreach ($heads as $h)
                <th class="p-1 bg-slate-300">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @foreach ($users as $u)
            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                <td class="p-1 text-center">
                    <input type="checkbox" name="u_{{ $u->id }}" value="on" form="{{$chkfor}}">
                </td>
                <td class="p-1 text-center">{{ $u->id }}
                </td>
                <td class="p-1">
                    <x-element.login_as :user="$u"></x-element.login_as>
                </td>
                <td class="p-1">{{ $u->affil }}
                </td>
                <td>
                    {{ $u->email }}
                </td>
                <td>
                    {{ $u->pivot->mailnotify ? '受信する' : '受信しない' }}
                </td>
                <td class="text-sm">
                    {{ $u->last_access() }}
                </td>
                <td class="text-sm">
                    {{ $u->last_login_at }}
                </td>
                <td class="text-sm">
                    {{ $u->created_at }}
                </td>
                <td>
                    <x-element.deletebutton action="{{ route('role.leave', ['role' => $role, 'user' => $u]) }}"
                        confirm="Roleを削除する？（ユーザは消えません）" size="sm">Role削除
                    </x-element.deletebutton>
                </td>
                <td class="text-center text-gray-600">
                    {{$loop->iteration}}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
