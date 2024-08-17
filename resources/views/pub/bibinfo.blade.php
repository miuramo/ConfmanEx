@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>
    <!-- pub.bibinfo -->
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/dragtext.css') }}">
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('プログラム出力') }}
            <span class="mx-2"></span>
            <x-element.category :cat="$cat">
            </x-element.category>
        </h2>
    </x-slot>
    <div class="px-4 py-4">
        @foreach ([0=>"しない",1=>"する"] as $ab=>$abtxt)
        <x-element.linkbutton2 href="{{ route('pub.bibinfo', ['cat' => $catid, 'abbr'=>$ab]) }}" color="lime" size="sm">
            連続する同一所属を省略{{$abtxt}}
        </x-element.linkbutton2>

        @endforeach
    </div>

    <div class="px-4 py-4">
        @php
            $psessionid = 0;
        @endphp
        @foreach ($subs as $sub)
            @if ($psessionid != $sub->psession_id)
                <div class="mt-4 mb-2">セッション{{$sub->psession_id}}：</div>
            @endif
            <div>({{ $sub->booth }}) {{ $sub->paper->title }}</div>
            <div class="mx-7"> {{ $sub->paper->bibauthors($abbr) }}</div>
            @php
            $psessionid = $sub->psession_id;
        @endphp
        @endforeach

        <div class="my-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>

    </div>

</x-app-layout>
