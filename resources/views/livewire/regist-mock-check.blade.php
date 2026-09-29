<div>
    <h3 class="text-lg font-semibold">入力内容チェック結果（モック）</h3>
    <button type="button" x-data
        x-on:click="$wire.checkMockAnswers(collectMockRegistrationAnswers())"
        class="bg-pink-500 text-white rounded-lg px-2 py-0.5 mx-1">
        入力内容をチェックする
    </button>
    <div class="mx-6 mt-2">
        @if (!$checked)
            <span>入力内容チェックを行ってください。</span>
        @elseif (count($errors) === 0)
            <span class="text-green-500">チェック結果：問題はありませんでした。</span>
        @else
            @foreach ($errors as $error)
                <div class="text-red-500 text-lg">{{ $error }}</div>
            @endforeach
        @endif
    </div>
</div>