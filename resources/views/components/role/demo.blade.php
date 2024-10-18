<!-- components.role.demo -->
<style>
    .hidden-content {
        opacity: 0;
        transition: opacity 0.5s ease;
    }
</style>

<div class="px-6 py-4">
    <x-element.h1>投稿論文</x-element.h1>

    <x-paper.summarytable />

    <x-element.h1>デモ希望 (demoifaccepted) の状況</x-element.h1>
    <div class="mx-2 px-6 py-2">
        <div class="w-full">
            デモ希望数：{{ App\Models\EnqueteAnswer::demoCount() }}
        </div>
        <div class="w-full">
            デモ希望PaperIDリスト：{{ implode(', ', $dPIDs = App\Models\EnqueteAnswer::demoPaperIDs()) }}
            <span class="mx-2"></span>
            {{ count($dPIDs) }} 件
        </div>
        <div class="mx-4 w-full">
            カテゴリ別：
            @php
                $demoPaper_eachCat = App\Models\EnqueteAnswer::demoPaperIDs_eachCat();
            @endphp
            <div class="mx-4">
                @foreach ($demoPaper_eachCat as $cat => $papers)
                    <div>
                        {{ $cat }}： {{ implode(', ', $papers) }}
                        <span class="mx-2"></span>
                        {{ count($papers) }} 件
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    <x-element.h1>デモ希望を手動でつける</x-element.h1>
    <div class="mx-2 px-6 py-2">
        <form action="{{ route('enq.manualset') }}" method="post">
            @csrf
            <div class="w-full">
                <label for="paper_id">PaperID（数字カンマ区切り）</label>
                <input type="text" name="pids" id="pids" class="w-96" size="30" placeholder="012, 023, 034">
            </div>
            <div class="w-full">
                <x-element.submitbutton color="cyan" value="はい">
                    デモ希望「はい」
                </x-element.submitbutton>
                <x-element.submitbutton color="slate" value="いいえ">
                    デモ希望「いいえ」
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <x-element.h1>
        アンケート
        <span class="px-3"></span>
        <x-element.linkbutton href="{{ route('enq.index') }}" color="green">
            （デモRoleおよび自分のRoleで参照可能な）アンケート一覧
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
    </x-element.h1>

</div>
@push('localjs')
    <script src="/js/jquery.min.js"></script>
    <script src="/js/openclose.js"></script>
@endpush
