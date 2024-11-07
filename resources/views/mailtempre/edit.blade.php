<!-- mailtempre.index -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            メール雛形 {{$mt->id}} の編集
        </h2>
    </x-slot>
    @section('title', 'Mail Edit '.$mt->id)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-6">
        <div class="my-5">
            <x-element.linkbutton href="{{ route('mt.index') }}" color="gray" size="sm">
                &larr; 雛形一覧に戻る
            </x-element.linkbutton>
        </div>
    
        <form action="{{ route('mt.store') }}" method="post" id="mt_store">
            @csrf
            @method('post')

            <table>
                <thead>
                    {{-- <tr class="bg-pink-200">
                        <th class="px-2">field</th>
                        <th class="px-2">id</th>
                    </tr> --}}
                </thead>
                <tbody>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1">
                            <label for="to">To</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="to" id="to" size="30" value="{{ $mt->to }}">
                            <span class="text-sm bg-yellow-100">注：To にメールアドレスは指定できません。下の「Toと雛形の説明」を参照してください。</span>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1">
                            <label for="subject">Subject</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="subject" id="subject" size="100" value="{{ $mt->subject }}">
                        </td>
                    </tr>
                    <tr class="bg-pink-100 dark:bg-pink-300">
                        <td class="px-2 py-1">
                            <label for="body">Body</label>
                        </td>
                        <td class="px-2 py-1">
                            <textarea name="body" cols="100" rows="20">{{ $mt->body }}</textarea>
                        </td>
                    </tr>
                    <tr class="bg-pink-50 dark:bg-pink-200">
                        <td class="px-2 py-1">
                            <label for="subject">name</label>
                        </td>
                        <td class="px-2 py-1">
                            <input type="text" name="name" id="name" size="60" value="{{ $mt->name }}">
                            <span class="text-sm bg-yellow-100">（オプション）雛形一覧での識別用。送信内容には影響しません。</span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-2">
                <input type="hidden" name="id" value="{{ $mt->id }}">
                先に
                <x-element.submitbutton color="pink">
                    保存
                </x-element.submitbutton>
                してから、
                <x-element.linkbutton2 href="{{ route('mt.show', ['mt' => $mt]) }}" color="lime" target="previewmt_{{$mt->id}}">
                    送信前の確認画面
                </x-element.linkbutton2>
                を押してください。
                <span class="mx-10"></span>
                <x-element.linkbutton href="{{ route('admin.crud', ['table' => 'mail_templates', 'row' => $mt->id])}}" target="_blank" color="gray">
                    （管理者編集）
                </x-element.linkbutton>


            </div>
        </form>

        <div class="py-5"></div>
        <x-element.h1>Toと雛形の説明
        </x-element.h1>

        <x-mailtempre.manual>
        </x-mailtempre.manual>

    </div>



</x-app-layout>
