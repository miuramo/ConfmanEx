<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('AnnotPaper の一覧') }}
            <span class="mx-2"></span>
            <x-element.linkbutton2 href="https://scrapbox.io/confman/AnnotPaper_%E3%81%AE%E3%81%A4%E3%81%8B%E3%81%84%E3%81%8B%E3%81%9F" color="lime" target="_blank">使い方(Scrapbox/Cosense)</x-element.linkbutton2>
        
        </h2>
    </x-slot>
    <style>
        .hidden-content {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
    </style>

    {{-- @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif --}}

    <div class="mx-4">
        <x-element.h1>
            <ruby>AnnotPaper<rt>アノットペーパー</rt></ruby> とは、論文に対するコメント共有機能です。<br>
            著者が、自分の論文に対する AnnotPaper を作成すると、このページにリンクが表示され、<u>ログインユーザ</u>に公開されます。<br>
            <u>ログインユーザ</u>は、このページのリンク一覧から参照したり、コメントを書き込んだりできます。<br>
            <b>注意：コメントを書いたユーザの名前と所属は、コメントへのマウスホバー時にポップアップ表示します。</b>
        </x-element.h1>
        <div class="mx-3 bg-yellow-100 p-2">
            <b>利用シナリオ(1)：</b> 著者が、自分の論文に対して、補足説明を加える。<br>
            <b>利用シナリオ(2)：</b> ログインユーザが、他の人の論文記述ですごいと思ったところや、共感できるところ、参考になったところに、コメントを残して表明する。<br>
        </div>

        <div class="m-3 bg-lime-100 p-2">
            <b>著者のかたへ：</b> 論文に対するコメントの書き込みと共有を有効にするには、最初に
            <x-element.linkbutton href="{{ route('annot.create') }}" color="lime">
                AnnotPaper の作成と管理
            </x-element.linkbutton>
            を押してください。<br>
            （公開 AnnotPaper へのリンクは、ここに表示します。あとで非公開（自分だけ閲覧可能）に戻すこともできます。）
        </div>
    </div>

    <div class="mx-4 my-10">

        <x-element.h1>
            公開 AnnotPaper の一覧  <b>（注：同一ページ内の同一ユーザの複数アノテーションは、1件としてカウントされます）</b>
        </x-element.h1>

        @php
            $annotpapers = \App\Models\AnnotPaper::where('is_public', true)->get();
        @endphp
        <div class="mx-8">
            @foreach ($annotpapers as $anpaper)
                <x-element.linkbutton href="{{ route('annot.show', ['annot' => $anpaper->id]) }}" color="lime">
                    {{ $anpaper->paper->boothes_accepted() }}
                    &nbsp;
                    {{ $anpaper->paper->title }} (PaperID: {{ $anpaper->paper->id_03d() }})
                </x-element.linkbutton> <span class="mx-2"></span>
                {{$anpaper->annots->count()}} 件のアノテーション
            @endforeach

        </div>
    </div>
</x-app-layout>
