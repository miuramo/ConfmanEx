<div>
    <input id="id_regist_searchbox" type="text" wire:model.live.debounce.500ms="search" wire:keydown.escape="resetSearch"
        placeholder="氏名・メール等で検索" size="25" x-init="$el.focus()" class="p-1" />
    <span class="px-3 text-sm text-blue-500">「編集」を開くと、一旦「未完了」となります。完了すると申込更新日時が変更されます。</span>
    <table class="text-sm border-collapse border border-slate-400 mt-2">
        <tr class="bg-slate-300">
            <th class="px-2">UserID</th>
            <th class="px-2">氏名</th>
            <th class="px-2">所属</th>
            <th class="px-2">状況</th>
            <th class="px-2">初回完了</th>
            <th class="px-2">操作1</th>
            <th class="px-2">申込更新</th>
            <th class="px-2">メールアドレス</th>
            <th class="px-2">操作2</th>
        </tr>
        @foreach ($users as $u)
            <tr
                class="hover:bg-yellow-50 {{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }} ">
                <td class="px-2 bg-slate-200 text-right">{{ $u->id }}</td>
                <td class="px-2 bg-slate-200">{{ $u->name }}</td>
                <td class="px-2 bg-slate-200">{{ $u->affil }}</td>
                @isset($regD[$u->id])
                    @if ($regD[$u->id]->valid)
                        <td class="px-2 bg-slate-200 text-green-600">有効（完了）</td>
                    @else
                        <td class="px-2 bg-slate-200 text-red-600">無効（未完了）</td>
                    @endif
                    <td class="px-2 bg-slate-200">{{ substr($regD[$u->id]->submitted_at,5,11) }}</td>
                    <td class="px-2 bg-slate-200">
                        <x-element.linkbutton2
                            href="{{ route('regist.edit', ['regist' => $regD[$u->id]->id, 'token' => $regD[$u->id]->token()]) }}"
                            color="blue" target="_blank" size="xs" confirm="{{ $u->name }} (UserID: {{$u->id}}) さんの参加登録を代理で編集します。よろしいですか？">編集</x-element.linkbutton2>
                        <x-element.linkbutton2
                            href="{{ route('regist.show', ['regist' => $regD[$u->id]->id, 'token' => $regD[$u->id]->token()]) }}"
                            color="green" target="_blank" size="xs">参照</x-element.linkbutton2>
                    </td>
                @else
                    <td class="px-2 bg-slate-200 text-orange-500 text-center">未登録</td>
                    {{-- 初回完了を飛ばす --}}
                    <td class="px-2 bg-slate-200"></td>
                    <td class="px-2 bg-slate-200">
                        <x-element.linkbutton2
                            href="{{ route('regist.admin', ['user_id' => $u->id]) }}" color="gray" target="_blank" confirm="{{ $u->name }} (UserID: {{$u->id}}) さんの参加登録を代理で作成します。よろしいですか？"
                            size="xs">代理作成</x-element.linkbutton2>
                    </td>
                @endisset
                <td class="px-2 bg-slate-200">
                    @isset($regD[$u->id])
                        {{ substr($regD[$u->id]->updated_at,5,11) }}
                    @endisset
                </td>
                <td class="px-2 bg-slate-200">{{ $u->email }}</td>
                <td class="px-2 bg-slate-200">
                    @isset($regD[$u->id])
                        <x-element.deletebutton action="{{ route('regist.destroy', ['regist' => $regD[$u->id]->id]) }}"
                            confirm="{{ $u->name }}さんの参加登録を削除します。よろしいですか？" color="red" size="xs">
                            削除
                        </x-element.deletebutton>
                    @endisset
                </td>
            </tr>
        @endforeach
    </table>
</div>
