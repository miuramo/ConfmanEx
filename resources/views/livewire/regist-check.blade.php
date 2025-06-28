<div>
    <x-element.h1>チェック結果
<span class="mx-4"></span>
            <button wire:click="check" class="bg-pink-500 text-white rounded-lg px-2 py-0.5 mx-1">入力チェックする</button>
<span class="mx-4"></span>
    前回チェック日時：{{ date('Y-m-d H:i:s') }}

    </x-element.h1>
    RegID: {{$regid}}
    チェック結果をここに表示します。
    @if(count($errors) === 0)
        <div class="text-green-500">チェック結果: 問題なし</div>
        <button wire:click="doregist" class="bg-green-500 text-white rounded-lg px-5 py-2 mx-1 text-2xl">登録する</button>
    @else
    @foreach($errors as $error)
        <div class="text-red-500 text-lg">{{ $error }}</div>
    @endforeach
    @endif

</div>
