<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            予約更新
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @if ($errors->any())
        <x-alert.error>{{ implode(' / ', $errors->all()) }}</x-alert.error>
    @endif

    <div class="py-2 px-6">
        <div class="my-3">
            <x-element.linkbutton href="{{ route('scheduled_update.create') }}" color="pink" size="sm">
                新しい予約更新を作成
            </x-element.linkbutton>
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'admin']) }}" color="gray" size="sm">
                &larr; admin に戻る
            </x-element.linkbutton>
        </div>

        <form action="{{ route('scheduled_update.bulk_reschedule_next_year') }}" method="post" id="scheduled-update-bulk-reschedule" class="my-3">
            @csrf
            <x-element.submitbutton color="teal" size="sm" confirm="選択した予約更新のexecute_atを1年延長し、pendingとして再スケジュールします。よろしいですか？">
                選択した予約更新を1年延長して再スケジュール
            </x-element.submitbutton>
        </form>

        <table class="table-auto w-full sortable" id="sortable">
            <thead>
                <tr class="bg-pink-200">
                    <th class="px-2 unsortable">
                        <input type="checkbox" id="scheduled-update-check-all">
                    </th>
                    <th class="px-2">id</th>
                    <th class="px-2">status</th>
                    <th class="px-2">execute_at</th>
                    <th class="px-2">target</th>
                    <th class="px-2">field</th>
                    <th class="px-2">new_value</th>
                    <th class="px-2">executed_at</th>
                    <th class="px-2 unsortable">(action)</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($scheduledUpdates as $scheduledUpdate)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white dark:bg-pink-300' }}">
                        <td class="px-2 py-1 text-center">
                            <input type="checkbox" name="scheduled_update_ids[]" value="{{ $scheduledUpdate->id }}" form="scheduled-update-bulk-reschedule" class="scheduled-update-check">
                        </td>
                        <td class="px-2 py-1 text-center">{{ $scheduledUpdate->id }}</td>
                        <td class="px-2 py-1">{{ $scheduledUpdate->status }}</td>
                        <td class="px-2 py-1">{{ optional($scheduledUpdate->execute_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-2 py-1">
                            {{ class_basename($scheduledUpdate->target_type) }} #{{ $scheduledUpdate->target_id }}
                            @if (!$scheduledUpdate->target)
                                <span class="text-red-600 font-bold">not found</span>
                            @endif
                        </td>
                        <td class="px-2 py-1">{{ $scheduledUpdate->field_name }}</td>
                        <td class="px-2 py-1 font-mono text-xs break-all">
                            {{ json_encode($scheduledUpdate->new_value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}
                        </td>
                        <td class="px-2 py-1">{{ optional($scheduledUpdate->executed_at)->format('Y-m-d H:i') }}</td>
                        <td class="px-2 py-1">
                            <x-element.linkbutton2 href="{{ route('scheduled_update.edit', ['scheduled_update' => $scheduledUpdate]) }}" color="blue" size="xs">
                                編集
                            </x-element.linkbutton2>
                            <form action="{{ route('scheduled_update.destroy', ['scheduled_update' => $scheduledUpdate]) }}" method="post" class="inline-block">
                                @csrf
                                @method('delete')
                                <x-element.submitbutton color="red" size="xs" confirm="本当に削除する？">
                                    削除
                                </x-element.submitbutton>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            {{ $scheduledUpdates->links() }}
        </div>
    </div>

    @push('localjs')
        <script src="/js/sortable.js"></script>
        <script>
            document.getElementById('scheduled-update-check-all').addEventListener('change', (event) => {
                document.querySelectorAll('.scheduled-update-check').forEach((checkbox) => {
                    checkbox.checked = event.target.checked;
                });
            });
        </script>
    @endpush
</x-app-layout>
