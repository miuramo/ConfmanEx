<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            @php
                $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
            @endphp
            <span class="text-gray-700">{{ $conf->value }}</span> {{ __('投票ページ') }}



        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6">

        @auth
        @else
            @if ($formData)
                <x-element.h1>一時的に保存された氏名と所属</x-element.h1>
                <div class="mx-4">
                    <p>氏名： {{ $formData['sssname'] }}</p>
                    <p>所属： {{ $formData['sssaffil'] }}</p>
                    {{-- <p>token： {{ $formData['_token'] }}</p> --}}
                </div>
                <div class="my-6 bg-lime-200 text-lg p-3 rounded-md leading-loose">
                    上記の情報がただしければ、下のボタンから、投票してください。
                    <br>
                    ただしくない場合は、画面下のフォームで、再度入力してください。
                </div>
            @endif

        @endauth
    </div>

    <div class="mx-6">
        @auth
        @else
            @if (!$formData)
                <x-element.h1>
                    同一の発表者に対する多重投票を避けるため、<b>最初に、氏名と、所属を入力</b>してください。<br>
                    （SSS2024投稿システムにアカウントをお持ちの方は、先に
                    <x-element.linkbutton href="{{ route('login') }}" color="cyan">
                        ログイン
                    </x-element.linkbutton>
                    していただくと入力不要になります。）
                </x-element.h1>

                <form action="{{ route('vote.index') }}" method="post" id="voteindex">
                    @csrf
                    @method('post')

                    <div class="mx-2">
                        <label for="sssname">氏名：</label>
                        <input type="text" id="sssname" name="sssname" placeholder="例：情報 花子" size=30></input>
                    </div>
                    <div class="mx-2 mb-2">
                        <label for="sssaffil">所属：</label>
                        <input type="text" id="sssaffil" name="sssaffil" placeholder="例：情報教育大学" size=30></input>
                    </div>
                    <div class="mx-2 my-2 text-red-500">
                        下のボタンをおすと、ここで入力した氏名、所属と、ランダム生成文字列が、Cookie に3日間保存されます。<br>
                        また、投票を行うと、投票先の情報と上記の情報が、本投稿システムに記録されます。<br>
                        了解して投票をつづける場合のみ、ボタンをおしてください。
                    </div>
                    <x-element.submitbutton color="green" value="9999">
                        了解して投票をつづける（氏名と所属を一時保存する）
                    </x-element.submitbutton>
                </form>
            @endif
        @endauth
    </div>

    @php
        $votes = App\Models\Vote::where('isopen', true)->where('isclose', false)->get();
    @endphp

    @auth
        <div class="mx-6">
            <x-element.h1>アカウントに登録された氏名と所属</x-element.h1>
            <div class="mx-4">
                <p>氏名： {{ auth()->user()->name }}</p>
                <p>所属： {{ auth()->user()->affil }}</p>
                {{-- <p>token： {{ $formData['_token'] }}</p> --}}
            </div>
            <div class="my-6 bg-lime-200 text-lg p-3 rounded-md leading-loose">
                上記の情報がただしければ、下のボタンから、投票してください。<br>
                ただしくない場合は、右上の名前→登録情報から修正してください。
            </div>
        </div>

        <x-vote.votelink>
        </x-vote.votelink>
    @else
        @if ($formData)
            <x-vote.votelink>
            </x-vote.votelink>
        @else
        @endif
    @endauth

    <div class="mx-6 my-10">
        @auth
        @else
            @if ($formData)
                <x-element.h1>一時的に保存された氏名と所属の修正</x-element.h1>
                <div class="mx-4">
                    <form action="{{ route('vote.index') }}" method="post" id="voteindex">
                        @csrf
                        @method('post')

                        <div class="mx-2">
                            <label for="sssname">氏名：</label>
                            <input type="text" id="sssname" name="sssname" placeholder="例：情報 花子" size=30
                                value="{{ $formData['sssname'] }}"></input>
                        </div>
                        <div class="mx-2 mb-2">
                            <label for="sssaffil">所属：</label>
                            <input type="text" id="sssaffil" name="sssaffil" placeholder="例：情報教育大学" size=30
                                value="{{ $formData['sssaffil'] }}"></input>
                        </div>
                        <x-element.submitbutton color="cyan" value="9999">
                            氏名と所属を修正する
                        </x-element.submitbutton>
                    </form>
                </div>
            @endif

        @endauth
    </div>


</x-app-layout>
