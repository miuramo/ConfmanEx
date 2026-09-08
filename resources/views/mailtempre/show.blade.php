<!-- mailtempre.show -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('メール送信の確認') }}
        </h2>
    </x-slot>
    <style>
        h1 {
            font-size: x-large;
            color: #333;
            font-weight: bold;
            font-family: Helvetica, Arial, sans-serif;
            margin-bottom: 11px;
        }

        h2 {
            font-size: large;
            color: #333;
            font-weight: bold;
            font-family: Helvetica, Arial, sans-serif;
            margin-bottom: 11px;
        }

        hr {
            display: block;
            unicode-bidi: isolate;
            margin-block-start: 0.5em;
            margin-block-end: 0.5em;
            margin-inline-start: auto;
            margin-inline-end: auto;
            overflow: hidden;
            border-style: inset;
            border-width: 1px;
        }

        .preview a {
            color: #3869d4;
            text-decoration: underline;
            font-size: 16px;
            font-family: Helvetica, Arial, sans-serif;
        }

        p {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            position: relative;
            font-size: 16px;
            line-height: 1.5em;
            margin-top: 0px;
            margin-bottom: 16px;
            text-align: left;
        }
    </style>
    @section('title', 'Preview ' . $mt->id)

    <!-- mailtempre.index -->
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif

    <div class="py-2 px-6">
        <div class="my-5">
            <x-element.linkbutton href="{{ route('mt.index') }}" color="gray" size="sm">
                &larr; 雛形一覧に戻る
            </x-element.linkbutton>
        </div>

        <x-element.h1>{{ $mt->to }} →
            @php
                $papers = $mt->handle_to();
                $count = $mt->numpaper();
                $to_cc = $first_item->get_mail_to_cc();
            @endphp
            @if ($count > 0)
                送信対象は{{ $count }}件：
                @foreach ($papers as $paper)
                    @if ($paper instanceof \App\Models\Paper)
                        <span class="mr-1 px-1 bg-pink-100 dark:bg-slate-500 hover:bg-pink-200 hover-on-paper hover:font-bold hover:text-pink-600 pointer cursor-pointer"
                            onmouseenter="Livewire.dispatch('id-changed', { value: {{ $paper->id }} });"
                            title="{{ $paper->paperowner->name }} {{ $paper->title }}">{{ $paper->id_03d() }}</span>
                    @endif
                    @if ($paper instanceof \App\Models\User)
                        <span class="mr-1 px-1 bg-sky-100 dark:bg-slate-500 hover:bg-sky-300 hover-on-user hover:font-bold hover:text-sky-600 pointer cursor-pointer"
                            onmouseenter="Livewire.dispatch('id-changed', { value: {{ $paper->id }} });"
                            title="{{ $paper->name }}（{{ $paper->affil }}）">{{ $paper->id_03d() }}</span>
                    @endif
                @endforeach
            @else
                送信対象はありません。To に指定できるのは、accept(catid), reject(catid), paperid(pid1,pid2, ...),
                acc_id(accid1,accid2, ...), acc_judge(judge1,judge2, ...) などです。
            @endif

            <x-element.linkbutton2 href="{{ route('mt.edit', ['mt' => $mt]) }}" color="blue"
                target="editmt_{{ $mt->id }}">
                雛形を編集
            </x-element.linkbutton2>
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('mt.show', ['mt' => $mt, 'dosend' => 'do']) }}" color="pink"
                target="_blank" confirm="本当にメール送信しますか？">
                この雛形をつかって送信
            </x-element.linkbutton>


        </x-element.h1>

        上の数字にマウスをホバーすると、プレビュー対象を変更できます：
        @if ($first_item instanceof \App\Models\Paper)
            <livewire:preview-mail-template :mt="$mt" :type="'paper'" :id="$first_item->id" />
        @elseif($first_item instanceof \App\Models\User)
            <livewire:preview-mail-template :mt="$mt" :type="'user'" :id="$first_item->id" />
        @endif
        <div class="my-5">
            <x-element.linkbutton href="{{ route('mt.show', ['mt' => $mt, 'dosend' => 'do']) }}" color="pink"
                target="_blank" confirm="本当にメール送信しますか？">
                この雛形をつかって送信
            </x-element.linkbutton>
        </div>
        <div class="my-5">
            <x-element.linkbutton href="{{ route('mt.index') }}" color="gray" size="sm">
                &larr; 雛形一覧に戻る
            </x-element.linkbutton>
        </div>

        <div class="py-5"></div>
        <x-element.h1>Toと雛形の説明
        </x-element.h1>
        <x-mailtempre.manual>
        </x-mailtempre.manual>

    </div>

</x-app-layout>
