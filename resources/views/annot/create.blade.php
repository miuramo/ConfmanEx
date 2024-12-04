<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('AnnotPaper の作成と管理') }}
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
        $papers = \App\Models\Paper::where('owner', auth()->id())->whereNotNull('pdf_file_id')->get();
    @endphp

    <div class="mx-4">

        <x-element.h1>
            AnnotPaper を作成したい論文を選択し、作成ボタンを押してください。（デフォルトは公開状態になります）
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
            あなたが作成した AnnotPaper （非公開・公開の設定）
        </x-element.h1>
        <div class="mx-8 my-4">
            非公開にすると、自分だけが閲覧・書き込みできます。
        </div>
        @php
            $annotpapers = \App\Models\AnnotPaper::where('user_id', auth()->id())->get();
        @endphp
        <div class="mx-8">
            @foreach ($annotpapers as $anpaper)
                @php
                    $color = $anpaper->is_public ? 'lime' : 'gray';
                @endphp
                <x-element.linkbutton href="{{ route('annot.show', ['annot' => $anpaper->id]) }}"
                    color="{{ $color }}">
                    ({{ $anpaper->paper->id_03d() }})
                    {{ $anpaper->paper->title }}
                </x-element.linkbutton>
                @if ($anpaper->is_public)
                    公開中(Public)
                    <form action="{{ route('annot.setpublic', ['annot' => $anpaper->id]) }}" method="post"
                        class="inline">
                        @csrf
                        <input type="hidden" name="is_public" value="0">
                        <x-element.submitbutton action="submit" color="orange">
                            非公開にする
                        </x-element.submitbutton>
                    </form>
                @else
                    非公開(Private)
                    <form action="{{ route('annot.setpublic', ['annot' => $anpaper->id]) }}" method="post"
                        class="inline">
                        @csrf
                        <input type="hidden" name="is_public" value="1">
                        <x-element.submitbutton action="submit" color="lime">
                            公開にする
                        </x-element.submitbutton>
                    </form>
                @endif
            @endforeach

        </div>
    </div>
</x-app-layout>
