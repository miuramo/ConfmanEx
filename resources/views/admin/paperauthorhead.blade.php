@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('第一著者の名前設定') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @php
        $catspans = App\Models\Category::spans();
        $cur = '';
        foreach ($papers as $paper) {
            $title = $paper->title_candidate();
            foreach ($sets as $set) {
                $title = str_replace($set->value, '', $title);
            }
            // authorheadが含まれていたら
            $pos = mb_strpos($title, mb_substr($paper->authorhead, 0, 2));
            if ($pos > -1) {
                $title = mb_substr($title, 0, $pos) ;
            }
            if (mb_strlen($paper->authorhead) < 1) {
                $cur .= $paper->id . ' ;; ' . $paper->authorhead . ' ;; ★★第一著者未設定★★ ' . $paper->title . " 【" . $paper->paperowner->name . "】\n";
            } else {
                $cur .= $paper->id . ' ;; ' . $paper->authorhead . ' ;; ' . $title . "\n";
            }
        }
    @endphp

    <div class="py-2 px-2">
        <div class="mx-2 py-4">
            <form action="{{ route('admin.paperauthorhead') }}" method="post" id="paperauthorhead">
                @csrf
                @method('post')
                <div class="mb-1">
                    <textarea name="authorheads" class="w-full text-sm mt-1 p-2" cols="30" rows="45">{{ $cur }}</textarea>
                    <x-element.submitbutton color="cyan" value="ahead">
                        第一著者の名前（;;で区切られた第2要素のみ）を設定する
                    </x-element.submitbutton>
                    <span class="mx-2"></span>
                    （おそらく、最初に1回だけ実行→）
                    <x-element.submitbutton color="lime" value="setfirstauthor_ifnull">
                        ★★第一著者未設定★★ について、第一著者の苗字を設定する
                    </x-element.submitbutton>
                <div class="my-1"></div>        
                    <x-element.submitbutton color="purple" value="titleupdate">
                        上記の第3要素のタイトルで書き換える
                    </x-element.submitbutton>
                </div>
            </form>
        </div>

        <div class="mx-2 py-4">
            <x-element.h1>現状設定されているタイトル</x-element.h1>
            <dix class="my-2"></dix>
            <pre class="text-sm">
@foreach ($papers as $paper)
{{ $paper->id }} ;; {{ $paper->title }}
@endforeach
            </pre>
        </div>

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('admin.dashboard') }}" color="gray" size="sm">
                &larr; Admin Topに戻る
            </x-element.linkbutton>
        </div>
    </div>

</x-app-layout>
