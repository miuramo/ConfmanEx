<div>
    <x-element.h1>入力内容チェック結果
        <span class="mx-4"></span>
        <button wire:click="check" wire:poll.10s="check"
            class="bg-pink-500 text-white rounded-lg px-2 py-0.5 mx-1">入力内容をチェックする</button>
        <span class="mx-4"></span>
        前回チェック日時：{{ date('Y-m-d H:i:s') }} <span class="ml-4 text-sm text-blue-500">(10秒ごとに自動更新します)</span>

    </x-element.h1>
    <div class="mx-6">
        {{-- RegID: {{ $regid }} --}}
        {{-- チェック結果をここに表示します。 --}}
        @if (count($errors) === 0)
            <span class="text-green-500">チェック結果：問題はありませんでした。</span>
            <span class="mx-2">→</span>
            @php
                $bg = $is_early ? 'bg-cyan-500' : 'bg-green-500';
            @endphp
            <button
                @if ($is_early || $regobj->isearly) x-data x-on:click.prevent="if (confirm('早期申込で参加登録を完了します。よろしいですか？')) { $wire.doregist() }" 
            @else x-data
                x-on:click.prevent="if (confirm('通常申込で参加登録を完了します。申込完了後の変更はできません。本当によろしいですか？')) { $wire.doregist() }" @endif
                class="{{ $bg }} text-white rounded-lg px-5 py-2 mx-1 text-2xl">参加登録を完了する
                @if ($is_early || $regobj->isearly)
                    （早期申込）
                @else
                    （通常申込）※完了後の変更はできませんので、入力内容をよくご確認ください。
                @endif
            </button>
        @else
            @foreach ($errors as $error)
                <div class="text-red-500 text-lg">{{ $error }}</div>
            @endforeach
        @endif
    </div>
</div>
