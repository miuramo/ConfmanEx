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
    @section('title', 'Preview '.$mt->id)

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
                    <span class="mr-1 px-1 bg-slate-100 dark:bg-slate-500">{{ $paper->id_03d() }}</span>
                @endforeach
            @else
                送信対象はありません。To に指定できるのは、accept(catid), reject(catid), paperid(pid1,pid2, ...),
                acc_id(accid1,accid2, ...), acc_judge(judge1,judge2, ...) などです。
            @endif

            <x-element.linkbutton2 href="{{ route('mt.edit', ['mt' => $mt]) }}"
                color="blue" target="editmt_{{$mt->id}}">
                雛形を編集
            </x-element.linkbutton2>
            <span class="px-2"></span>
            <x-element.linkbutton href="{{ route('mt.show', ['mt' => $mt, 'dosend' => 'do']) }}" color="pink"
                target="_blank" confirm="本当にメール送信しますか？">
                この雛形をつかって送信
            </x-element.linkbutton>


        </x-element.h1>

        最初の一件のみ、以下でプレビューできます：
        <div class="bg-sky-100 p-9">
            <div class="bg-slate-50 py-2 px-4 font-bold text-sm flex justify-between">
                To : {{ $to_cc['to'] }}
                <span class="text-slate-400 text-right">To</span>
            </div>
            <div class="bg-slate-100 py-2 px-4 font-bold text-sm flex justify-between">
                Cc : {{ implode(' , ', $to_cc['cc']) }}
                @isset($mt->cc)
                    , {{ str_replace(',', ' , ', $mt->cc) }}
                @endisset
                <span class="text-slate-400 text-right">Cc</span>
            </div>
            @isset($mt->bcc)
                <div class="bg-slate-100 py-2 px-4 font-bold text-sm flex justify-between">
                    Bcc :
                    {{ str_replace(',', ' , ', $mt->bcc) }}
                    <span class="text-slate-400 text-right">Bcc</span>
                </div>
            @endisset
            <div class="bg-slate-200 py-2 px-4 font-bold text-xl flex justify-between">
                {{ $subject }} <span class="text-slate-400 text-right">subject</span>
            </div>
            <div class="bg-white px-7 py-4 text-gray-700 text-md preview">
                {!! $markdown !!}
            </div>

        </div>
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
