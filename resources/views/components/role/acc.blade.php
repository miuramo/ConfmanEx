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
        <x-element.resist_sponsorlink /> {{-- スポンサー向けの特殊な参加登録URLをクリップボードにコピー --}}
        <br>
        {{-- ここから2段組にしたい。 --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REGOPEN'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REGOPEN_PUBLIC'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_EARLY_LIMIT'" textsize=10 />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_START_FOR_PCACC'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_START_FOR_REVIEWERS'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_START_FOR_ACCEPTED_AUTHORS'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_START_FOR_VALID_AUTHORS'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_START_FOR_ALL'" />
                <span class="px-3"></span>
                <livewire:setting-switch :name="'REG_PERSON_UPPERLIMIT'" />
                <span class="px-3"></span>
                <livewire:regist-summary />
            </div>
            <div>
                <livewire:regist-detach-incomplete />
                <span class="px-3"></span>
                <livewire:regist-check-author />
            </div>
        </div>
        <livewire:regist-admin-search />

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
            <span
                class="inline-block bg-slate-300 rounded-md p-1 mb-0.5 dark:bg-slate-500 dark:text-gray-300">{{ $ro->desc }}
                ({{ $ro->name }})
            </span>
        @endforeach
        <span class="mx-4"></span>
        <x-element.linkbutton href="{{ route('role.myroles') }}" color="cyan">
            自分が参加しているRoleに他の登録済みユーザを招待する
        </x-element.linkbutton>
    </x-element.h1>



</div>

<script>
    function copyToClipboard(text) {
        // const text = document.getElementById("copyText").innerText;
        navigator.clipboard.writeText(text).then(() => {
            alert("コピーしました！");
        }).catch(err => {
            console.error("コピーに失敗しました:", err);
        });
    }
</script>

@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush
