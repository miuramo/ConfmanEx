<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role'=>'admin']) }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('必要なプログラムの確認') }}
        </h2>
    </x-slot>

    <div class="px-6 py-2">

        @foreach ($in as $com)
            <x-element.h1>
                {{$com}}
            </x-element.h1>
            <pre class="mx-3 text-sm">{{$out[$com]}}</pre>
        @endforeach
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role'=>'admin']) }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>


    </div>


</x-app-layout>
