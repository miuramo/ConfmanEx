@props([
    'votes' => null,
])

@php
    $votes = App\Models\Vote::all();
@endphp

<div class="py-4 px-10  dark:text-gray-400">
    @foreach ($votes as $vote)
        @if ($vote->for_pc && !auth()->user()->is_pc_member())
            @continue
        @endif
        @php
            $col = $vote->for_pc ? 'orange' : 'lime';
        @endphp
        @if ($vote->isopen && !$vote->isclose)
            <x-element.linkbutton href="{{ route('vote.vote', ['vote' => $vote->id]) }}" size="xl"
                  color="{{ $col }}">
                {{ $vote->name }} に対する投票
            </x-element.linkbutton>
        @else
            <span class="rounded-lg py-2 px-3 bg-slate-400 text-lg text-slate-500">{{ $vote->name }}</span>
            @if ($vote->isopen)
                <span class="text-red-500">投票は締め切りました。</span>
            @else
                <span class="text-blue-500">投票期間前です。</span>
            @endif
        @endif
        <span class="mx-2"></span>
    @endforeach
</div>
