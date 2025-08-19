<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('vote.index') }}" color="green" size="sm">
                &larr; 投票Topに戻る
            </x-element.linkbutton>
            @php
                $votes = App\Models\Vote::where('isopen', true)->where('isclose', false)->get();
            @endphp
            <span class="mx-4"></span>
            @foreach ($votes as $vvv)
                @if ($vvv->for_pc)
                    @if (!auth()->check())
                        @continue
                    @endif
                    @if (!auth()->user()->is_pc_member())
                        @continue
                    @endif
                @endif
                @php
                    $col = $vvv->for_pc ? 'orange' : 'lime';
                @endphp
                @if ($vvv->isopen && !$vvv->isclose)
                    <x-element.linkbutton href="{{ route('vote.vote', ['vote' => $vvv->id]) }}"
                        color="{{ $col }}" size="md">
                        {{ $vvv->name }}に対する投票
                    </x-element.linkbutton>
                @endif
                <span class="mx-2"></span>
            @endforeach

        </div>

        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ $vote->name }}に対する投票

        </h2>
    </x-slot>
    <style>
        .hover-trigger:hover+.tooltip {
            display: block;
        }
    </style>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="mx-6">
        <x-element.h1>
            選択するとすぐに投票結果として保存されます。期限までは何度でも修正できます。
        </x-element.h1>
    </div>
    @php
        $voteitems = App\Models\VoteItem::where('vote_id', $vote->id)->orderBy('orderint')->get();
        $papers = App\Models\Paper::select('title', 'id')->pluck('title', 'id')->toArray();
        $authors = App\Models\Paper::select('authorlist', 'id')->pluck('authorlist', 'id')->toArray();
    @endphp

    <form action="{{ route('vote.vote', ['vote' => $vote]) }}" method="post" id="votevote">
        @csrf
        @method('post')

        @auth
            <input type="hidden" name="user_id" value="{{ $uid }}">
            <input type="hidden" name="comment" value="{{ auth()->user()->name }} {{ auth()->user()->affil }}">
        @else
            <input type="hidden" name="token" value="{{ $ticket->token }}">
            <input type="hidden" name="comment" value="{{ $ticket->email }}">
        @endauth

        <div class="py-4 px-6  dark:text-gray-400">
            @foreach ($voteitems as $vi)
                <livewire:vote-item-component :voteItem="$vi" :vote="$vote" />
            @endforeach
        </div>
    </form>

    <div class="mx-6 mt-4 mb-12">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('vote.index') }}" color="green" size="sm">
                &larr; 投票Topに戻る
            </x-element.linkbutton>
            @php
                $votes = App\Models\Vote::where('isopen', true)->where('isclose', false)->get();
            @endphp
            <span class="mx-4"></span>
            @foreach ($votes as $vvv)
                @if ($vvv->for_pc)
                    @if (!auth()->check())
                        @continue
                    @endif
                    @if (!auth()->user()->is_pc_member())
                        @continue
                    @endif
                @endif


                @php
                    $col = $vvv->for_pc ? 'orange' : 'lime';
                @endphp
                @if ($vvv->isopen && !$vvv->isclose)
                    <x-element.linkbutton href="{{ route('vote.vote', ['vote' => $vvv->id]) }}"
                        color="{{ $col }}" size="md">
                        {{ $vvv->name }}に対する投票
                    </x-element.linkbutton>
                @endif
                <span class="mx-2"></span>
            @endforeach

        </div>

    </div>

</x-app-layout>
