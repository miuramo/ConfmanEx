<x-app-layout>
    @section('title', '参加登録フォームのプレビュー')

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            参加登録フォームのプレビュー
            <span class="mx-2"></span>
            <x-element.linkbutton2 href="{{ route('regist.edit_dummy', ['key' => $sha1]) }}"
                color="cyan" size="sm">
                内部公開リンク
            </x-element.linkbutton2>
        </h2>
    </x-slot>

    <div class="mx-6 mt-2 p-3 bg-cyan-100 rounded-lg dark:bg-cyan-900 dark:text-blue-200 text-lg text-blue-500">
        モック画面です。入力を操作できますが、内容は保存されません。
    </div>

    <div class="pt-0 px-6">
        <form id="mock-registration-form" action="#" method="get" onsubmit="return false;">
            @foreach ($enqs['all'] as $enq)
                <a name="enq_{{ $enq->id }}"></a>
                <div class="text-lg mt-5 mb-1 p-3 bg-slate-200 rounded-lg dark:bg-slate-800 dark:text-gray-400">
                    {{ $enq->name }}
                </div>
                @if ($enq->showonpaperindex)
                    <div class="mx-10">
                        <x-enquete.edit :enq="$enq" :enqans="$enqans" :mock="true">
                        </x-enquete.edit>
                    </div>
                @endif
            @endforeach
        </form>
    </div>

    <div class="py-2 px-6">
        <livewire:regist-mock-check />
    </div>

    <div class="py-2 px-6">
        <button type="button" disabled class="bg-gray-400 text-white rounded-lg px-5 py-2 mx-1 text-2xl opacity-70">
            参加登録を完了する（プレビューでは実行できません）
        </button>
    </div>

    <script>
        function collectMockRegistrationAnswers() {
            return Object.fromEntries(new FormData(document.getElementById('mock-registration-form')).entries());
        }

        function updateMockAnswer(input) {
            const fields = Array.from(document.getElementsByName(input.name));
            const selected = fields.find((field) => field.checked);
            const field = selected || fields.slice().reverse().find((item) => item.type !== 'hidden');
            const answer = document.getElementById(input.name + '_answer');
            if (!answer) return;

            answer.textContent = field && field.value.trim() ? field.value : '(未入力)';
            answer.style.whiteSpace = 'pre-line';
        }
    </script>
</x-app-layout>
