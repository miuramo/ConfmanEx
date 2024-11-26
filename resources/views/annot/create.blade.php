<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('AnnotPaperの作成') }}
        </h2>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $papers = \App\Models\Paper::where('owner', auth()->id())->get();
    @endphp

    <div class="mx-4">

        <x-element.h1>
            AnnotPaperを作成したい論文を選択し、作成ボタンを押してください
        </x-element.h1>

        <form action="{{ route('annot.store') }}" method="post">
            @csrf
            <select name="paper_id" id="paper_id" class="form-select mt-1">
                @foreach ($papers as $paper)
                    <option value="{{ $paper->id }}">({{ $paper->id }}) {{ $paper->title }}</option>
                @endforeach
            </select>
            <x-element.submitbutton action="submit" color="blue">
                作成
            </x-element.submitbutton>
        </form>
    </div>


    <div class="mx-4">

        <x-element.h1>
            あなたが作成したAnnotPaper
        </x-element.h1>

        @php
            $annotpapers = \App\Models\AnnotPaper::where('user_id', auth()->id())->get();
        @endphp
        <div class="mx-8">
            @foreach ($annotpapers as $anpaper)
                
                <x-element.linkbutton href="{{ route('annot.show', ['annot' => $anpaper->id]) }}" color="lime">
                    ({{$anpaper->paper->id_03d()}}) {{ $anpaper->paper->title }}
                </x-element.linkbutton>
            @endforeach

        </div>
    </div>
</x-app-layout>
