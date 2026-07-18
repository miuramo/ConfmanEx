<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            予約更新 {{ $scheduledUpdate->exists ? $scheduledUpdate->id . ' の編集' : 'の作成' }}
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

    @php
        $targetType = old('target_type', $scheduledUpdate->target_type);
        $targetId = old('target_id', $scheduledUpdate->target_id);
        $fieldName = old('field_name', $scheduledUpdate->field_name);
        $rawValue = old('new_value_text');
        if ($rawValue === null && $scheduledUpdate->exists) {
            $stored = $scheduledUpdate->new_value;
            $rawValue = is_array($stored) && array_key_exists($scheduledUpdate->field_name, $stored)
                ? $stored[$scheduledUpdate->field_name]
                : $stored;
            $rawValue = is_string($rawValue) ? $rawValue : json_encode($rawValue, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
    @endphp

    <div class="py-2 px-6">
        <div class="my-3">
            <x-element.linkbutton href="{{ route('scheduled_update.index') }}" color="gray" size="sm">
                &larr; 予約更新一覧に戻る
            </x-element.linkbutton>
        </div>

        <form action="{{ $scheduledUpdate->exists ? route('scheduled_update.update', ['scheduled_update' => $scheduledUpdate]) : route('scheduled_update.store') }}" method="post">
            @csrf
            @if ($scheduledUpdate->exists)
                @method('put')
            @endif

            <table>
                <tbody>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1"><label for="target_type">対象モデル</label></td>
                        <td class="px-2 py-1">
                            <select name="target_type" id="target_type" class="text-sm">
                                @foreach ($models as $class => $label)
                                    <option value="{{ $class }}" @selected($targetType === $class)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="text-sm bg-yellow-100">変更後に「対象を確認」を押すとカラム候補を更新します。</span>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1"><label for="target_id">対象ID</label></td>
                        <td class="px-2 py-1">
                            <input type="number" name="target_id" id="target_id" value="{{ $targetId }}" class="text-sm w-32">
                            <button type="submit" formmethod="get" formaction="{{ route('scheduled_update.create') }}" class="inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-teal-700 bg-teal-300 hover:text-teal-50 hover:bg-teal-500">
                                対象を確認
                            </button>
                            @if ($target)
                                <span class="text-sm bg-lime-100 text-lime-700 p-1">対象あり: {{ class_basename($target) }} #{{ $target->id }}</span>
                            @elseif ($targetId)
                                <span class="text-sm bg-red-100 text-red-700 p-1">対象が見つかりません</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1"><label for="field_name">更新カラム</label></td>
                        <td class="px-2 py-1">
                            <select name="field_name" id="field_name" class="text-sm">
                                @foreach ($columns as $column)
                                    <option value="{{ $column }}" @selected($fieldName === $column)>{{ $column }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1">現在値</td>
                        <td class="px-2 py-1">
                            @if ($target && $fieldName)
                                <pre class="font-mono text-xs bg-white p-2 whitespace-pre-wrap">{{ json_encode($target->{$fieldName}, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <span class="text-sm bg-yellow-100">対象IDとカラムを指定してください。</span>
                            @endif
                        </td>
                    </tr>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1"><label for="new_value_text">予約する値</label></td>
                        <td class="px-2 py-1">
                            <textarea name="new_value_text" id="new_value_text" cols="100" rows="8" class="font-mono text-sm">{{ $rawValue }}</textarea><br>
                            <span class="text-sm bg-yellow-100">JSON として解釈できる値は JSON として保存します。文字列として保存したい場合は JSON にならない形で入力してください。</span>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1"><label for="execute_at">実行日時</label></td>
                        <td class="px-2 py-1">
                            <input type="datetime-local" name="execute_at" id="execute_at" value="{{ old('execute_at', optional($scheduledUpdate->execute_at)->format('Y-m-d\TH:i')) }}" class="text-sm">
                            <button type="button" class="scheduled-update-delay inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-teal-700 bg-teal-300 hover:text-teal-50 hover:bg-teal-500" data-delay-seconds="60">
                                1分後
                            </button>
                            <button type="button" class="scheduled-update-delay inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-teal-700 bg-teal-300 hover:text-teal-50 hover:bg-teal-500" data-delay-seconds="120">
                                2分後
                            </button>
                            <button type="button" class="scheduled-update-delay inline-flex justify-center py-1 px-2 mb-0.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-teal-700 bg-teal-300 hover:text-teal-50 hover:bg-teal-500" data-delay-seconds="180">
                                3分後
                            </button>
                        </td>
                    </tr>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1"><label for="status">status</label></td>
                        <td class="px-2 py-1">
                            <select name="status" id="status" class="text-sm">
                                @foreach (['pending', 'completed', 'failed', 'canceled'] as $status)
                                    <option value="{{ $status }}" @selected(old('status', $scheduledUpdate->status ?: 'pending') === $status)>{{ $status }}</option>
                                @endforeach
                            </select>
                            @if ($scheduledUpdate->executed_at)
                                <span class="text-sm bg-white p-1">executed_at: {{ $scheduledUpdate->executed_at }}</span>
                            @endif
                        </td>
                    </tr>
                    @if ($scheduledUpdate->error_message)
                        <tr class="bg-red-100">
                            <td class="px-2 py-1">error</td>
                            <td class="px-2 py-1 font-mono text-xs">{{ $scheduledUpdate->error_message }}</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="mt-3">
                <x-element.submitbutton color="pink">
                    保存
                </x-element.submitbutton>
                @if ($scheduledUpdate->exists && $scheduledUpdate->status !== 'pending')
                    <x-element.submitbutton type="submit" value="reuse" color="teal">
                        再利用して予約
                    </x-element.submitbutton>
                @endif
                @if ($scheduledUpdate->exists)
                    <x-element.linkbutton href="{{ route('admin.crud', ['table' => 'scheduled_updates', 'row' => $scheduledUpdate->id]) }}" target="_blank" color="gray">
                        （管理者編集）
                    </x-element.linkbutton>
                @endif
            </div>
        </form>
    </div>

    @push('localjs')
        <script>
            document.getElementById('execute_at').addEventListener('change', () => {
                document.getElementById('status').value = 'pending';
            });

            document.querySelectorAll('.scheduled-update-delay').forEach((button) => {
                button.addEventListener('click', () => {
                    const executeAt = document.getElementById('execute_at');
                    const status = document.getElementById('status');
                    const date = new Date(Date.now() + Number(button.dataset.delaySeconds) * 1000);
                    const offset = date.getTimezoneOffset() * 60000;

                    executeAt.value = new Date(date.getTime() - offset).toISOString().slice(0, 16);
                    status.value = 'pending';
                });
            });
        </script>
    @endpush
</x-app-layout>
