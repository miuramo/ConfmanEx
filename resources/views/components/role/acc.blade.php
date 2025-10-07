<!-- components.role.acc -->
<style>
    .hidden-content {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>

<div class="px-6 py-4">
    <x-element.h1>
        参加登録
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('regist.index') }}" color="teal">
            参加登録
        </x-element.linkbutton>
        <span class="px-3"></span>
    </x-element.h1>

    <x-element.h1>
        参加登録設定
        <span class="px-3"></span>
        <livewire:setting-switch :name="'REGOPEN'" />
        <span class="px-3"></span>
    </x-element.h1>

    <x-element.h1>
        アンケート
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            （会計Roleおよび自分のRoleで参照可能な）アンケート一覧
        </x-element.linkbutton>

    </x-element.h1>


    <x-element.h1>メール送信
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('mt.index') }}" color="pink">
            メール雛形
        </x-element.linkbutton>
    </x-element.h1>

    <x-element.h1>自分の権限確認（Role一覧）
        <span class="mx-3"></span>
        @php
            $user = App\Models\User::find(auth()->id());
        @endphp
        @foreach ($user->roles as $ro)
            <span class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
    </x-element.h1>



</div>
@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush


