<div>
    @if (count($notfinished) == 0)
        <span class="text-green-600">未完了者はいません。</span>
    @else
        未完了者一覧（{{ count($notfinished) }}名）
        <table class="text-sm border-collapse border border-slate-400 mb-4">
            <tr class="bg-slate-300">
                <th class="px-2">RegID</th>
                <th class="px-2">User ID</th>
                <th class="px-2">氏名</th>
                <th class="px-2">所属</th>
                <th class="px-2">開始日時</th>
                <th class="px-2">状態</th>
                <th class="px-2">Action</th>
            </tr>
            @foreach ($notfinished as $reg)
                <tr
                    class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200 dark:bg-slate-700' : 'bg-slate-50 dark:bg-slate-600' }} hover:bg-yellow-50">
                    <td class="px-1 text-center">{{ $reg->id }}</td>
                    <td class="px-1 text-center">{{ $reg->user_id }}</td>
                    <td class="px-1">{{ $reg->user->name }}</td>
                    <td class="px-1">{{ $reg->user->affil }}</td>
                    <td class="px-1 text-center">{{ substr($reg->created_at, 5, 11) }}</td>
                    <td class="px-1 text-center text-xs">
                        @if ($reg->submitted_at)
                            再編集中 （一度は完了済）
                        @else
                            未完了
                        @endif
                    </td>
                    <td class="px-1 text-center">
                        <x-element.deletebutton action="{{ route('regist.destroy', ['regist' => $reg->id]) }}"
                            confirm="{{ $reg->user->name }}さんの未完了の参加登録を削除します。よろしいですか？" color="red" align="right"
                            size="xs">
                            未完了登録の削除
                        </x-element.deletebutton>
                    </td>
                </tr>
            @endforeach
        </table>
    @endif
</div>
