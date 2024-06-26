<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('未完了Bidding一覧') }}

        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-4 px-6  dark:text-gray-400">
        @foreach ($missing as $name=>$papers)
        <x-element.h1>{{$name}} ({{count($papers)}}件)</x-element.h1>
        <div class="m-2 p-2">
            @foreach ($papers as $pid=>$title)
                {{sprintf("%03d",$pid)}} {{$title}}<br>
            @endforeach
        </div>
        @endforeach
    </div>


</x-app-layout>
