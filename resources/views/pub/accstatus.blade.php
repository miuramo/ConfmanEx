@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('採択状況の確認') }}
        </h2>
    </x-slot>

    @php
        $catspans = App\Models\Category::spans();
    @endphp
    <div class="px-4 py-4">
        <table class="border-pink-400 border-2">
            <tr class="bg-pink-300">
                @php
                    $hs = ['投稿時カテゴリ', '採否判定カテゴリ', '判定', '件数', 'PaperIDs'];
                @endphp
                @foreach ($hs as $h)
                    <th class="px-2 py-1">{{ $h }}</th>
                @endforeach
            </tr>
            @foreach ($stats as $st)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50' : 'bg-gray-50' }}">
                    <td class="px-2 py-1 text-center">
                        @if ($st->origcat != $st->hanteicat)
                            {!!$catspans[$st->origcat]!!}
                        @else
                            →
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        {!! $catspans[$st->hanteicat] !!}
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if ($st->judge > 0)
                            <span class="text-blue-600 font-bold">{{ $accepts[$st->accept_id] }}</span>
                        @else
                            <span class="text-slate-600">{{ $accepts[$st->accept_id] }}</span>
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        {{ $st->cnt }}
                    </td>
                    <td class="px-2 py-1 w-96">
                        {{ implode(', ', $paperlist[$st->origcat][$st->hanteicat][$st->accept_id]) }}
                    </td>
                </tr>
            @endforeach
        </table>

    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

</x-app-layout>
