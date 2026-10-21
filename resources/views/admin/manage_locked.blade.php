<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'acc']) }}" color="gray" size="sm">
                &larr; 会計 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Locked Registrations') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-2 px-2">
        <form id="regist-lock-form" action="{{ route('regist.update_locked') }}" method="post"
            class="mb-6 border border-slate-300 bg-slate-100 p-4 dark:bg-slate-700">
            <script>
                function checkIfDelayHoursPassed() {
                    const delayInput = document.getElementById('delay_hours');
                    const delayHours = delayInput.valueAsNumber;
                    const delayMilliseconds = delayHours * 60 * 60 * 1000;

                    if (!delayInput.reportValidity() || !Number.isFinite(delayHours) || delayHours < 0) {
                        return;
                    }

                    const cutoff = Date.now() - delayMilliseconds;
                    document.querySelectorAll('input[name="chk[]"]').forEach(checkbox => {
                        const submittedAt = Date.parse(checkbox.dataset.submittedAt);
                        checkbox.checked = checkbox.dataset.valid === 'true' &&
                            Number.isFinite(submittedAt) &&
                            submittedAt <= cutoff;
                    });
                }

                function uncheckAll() {
                    document.querySelectorAll('input[name="chk[]"]').forEach(el => el.checked = false);
                }
                function checkAll() {
                    document.querySelectorAll('input[name="chk[]"]').forEach(el => el.checked = true);
                }
            </script>
            @csrf
            <label for="delay_hours" class="mr-2 dark:text-gray-200">
                submitted_at から
            </label>
            <input id="delay_hours" name="delay_hours" type="number" min="0" step="any"
                value="{{ old('delay_hours', 24) }}" required class="w-18 rounded border-slate-300">
            時間経過した参加登録に
            <x-element.button type="button" onclick="checkIfDelayHoursPassed()" value="チェックを入れる" color="blue">
            </x-element.button>
            <span class="mx-4"></span>

            <x-element.button type="button" onclick="uncheckAll()" value="すべてのチェックを外す" color="yellow">
            </x-element.button>
            <span class="mx-2"></span>
            <x-element.button type="button" onclick="checkAll()" value="すべてにチェックを入れる" color="teal">
            </x-element.button>
            <br>


            <br>
            <br>
            チェックを入れた参加登録のうち、Valid なものに対して、
            <x-element.submitbutton type="submit" name="action" value="do_lock" color="cyan" onclick="return confirm('チェックを入れた参加登録をまとめてロックしますか？')">
                一括Lockする
            </x-element.submitbutton>
            <span class="mx-2"></span>
            <x-element.submitbutton type="submit" name="action" value="do_unlock" color="orange" onclick="return confirm('チェックを入れた参加登録のロックをまとめて解除しますか？')">
                一括解除する
            </x-element.submitbutton>

            @error('delay_hours')
                <p class="mt-2 text-red-600">{{ $message }}</p>
            @enderror
            @error('action')
                <p class="mt-2 text-red-600">{{ $message }}</p>
            @enderror
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 sortable" id="regist-table">
                <thead>
                    <tr class="bg-slate-300 dark:bg-slate-600">
                        <th class="p-2 text-left">chk</th>
                        <th class="p-2 text-left">submitted_at</th>
                        <th class="p-2 text-left">参照</th>
                        <th class="p-2 text-left">名前</th>
                        <th class="p-2 text-left">所属</th>
                        <th class="p-2 text-center">Locked</th>
                        <th class="p-2 text-center">Valid</th>
                        <th class="p-2 text-left">uID</th>
                        <th class="p-2 text-left">regID</th>
                        <th class="p-2 text-center">isearly</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white dark:bg-slate-800">
                    @forelse ($regists as $regist)
                        <tr class="{{ $loop->even ? 'bg-slate-100 dark:bg-slate-700' : '' }}">
                            <td class="p-2 dark:text-gray-200">
                                <input type="checkbox" name="chk[]" value="{{ $regist->id }}"
                                    form="regist-lock-form"
                                    data-submitted-at="{{ $regist->submitted_at ? \Illuminate\Support\Carbon::parse($regist->submitted_at)->toIso8601String() : '' }}"
                                    data-valid="{{ $regist->valid ? 'true' : 'false' }}">
                            </td>
                            <td class="p-2 dark:text-gray-200">{{ $regist->submitted_at ?? '未完了' }}</td>
                            <td class="p-2 dark:text-gray-200">
                                <x-element.linkbutton2
                                    href="{{ route('regist.showwithtoken', ['regist' => $regist->id, 'token' => $regist->token()]) }}"
                                    color="green" target="_blank" size="xs">参照</x-element.linkbutton2>
                            </td>   
                            <td class="p-2 dark:text-gray-200">{{ $regist->user?->name ?? 'ユーザー不明' }}
                            </td>
                            <td class="p-2 dark:text-gray-200">{{ $regist->user?->affil ?? '所属不明' }}</td>
                            <td class="p-2 text-center dark:text-gray-200">
                                {!! $regist->locked ? '<span class="bg-cyan-500 text-white px-2 py-1 rounded">Locked</span>' : '<span class="bg-orange-500 text-white px-2 py-1 rounded">Off</span>' !!}
                            </td>
                            <td class="p-2 text-center dark:text-gray-200">
                                {!! $regist->valid ? '<span class="bg-cyan-500 text-white px-2 py-1 rounded">Valid</span>' : '<span class="bg-red-500 text-white px-2 py-1 rounded">Invalid</span>' !!}
                            </td>
                            <td class="p-2 text-left dark:text-gray-200">{{ $regist->user?->id ?? '不明' }}</td>
                            <td class="p-2 text-left dark:text-gray-200">{{ $regist->id }}</td>
                            <td class="p-2 text-center dark:text-gray-200">
                                {{ $regist->isearly ? 'Early' : 'Late' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-2 text-center dark:text-gray-200">
                                参加登録はありません。
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @push('localjs')
        <script src="/js/sortable.js"></script>
    @endpush


</x-app-layout>
