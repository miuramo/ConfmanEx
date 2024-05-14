<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">
            {{ __('メール雛形') }}
            {{-- &nbsp;
            <x-element.linkbutton href="{{ route('file.create') }}" color="cyan">
                Upload New File</x-element.linkbutton>

            <x-element.deletebutton action="{{ route('file.delall') }}" color="red" confirm="全部削除してよいですか？"> Delete All
            </x-element.deletebutton> --}}

        </h2>
    </x-slot>
    <!-- mailtempre.index -->
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


    <div class="py-2 px-6">

        <form action="{{ route('mt.bundle') }}" method="post" id="mt_bundle">
            @csrf
            @method('post')

            <table>
                <thead>
                    <tr class="bg-pink-200">
                        <th class="px-2">chk</th>
                        <th class="px-2">id</th>
                        <th class="px-2">to</th>
                        <th class="px-2">subject</th>
                        <th class="px-2">name</th>
                        <th class="px-2">lastsent</th>
                        <th class="px-2">updated_at</th>
                        <th class="px-2">(action)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mts as $mt)
                        <tr
                            class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50 dark:bg-pink-400' : 'bg-white  dark:bg-pink-300' }}">
                            <td class="px-2 py-1 text-center">
                                <input type="checkbox" name="mt_{{ $mt->id }}" value="on">
                            </td>
                            <td class="px-2 py-1 text-center">
                                {{ $mt->id }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->to }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->subject }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->name }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->lastsent }}
                            </td>
                            <td class="px-2 py-1">
                                {{ $mt->updated_at }}
                            </td>
                            <td class="px-2 py-1">
                                <x-element.linkbutton2 href="{{ route('mt.show', ['mt' => $mt]) }}" color="lime">
                                    送信前の確認画面
                                </x-element.linkbutton2>
                                <x-element.linkbutton2
                                    href="{{ route('admin.crud', ['table' => 'mail_templates', 'row' => $mt->id]) }}"
                                    color="blue" target="_blank">
                                    雛形を編集
                                </x-element.linkbutton2>
                                {{-- <x-element.deletebutton action="{{ route('mt.destroy', ['mt' => $mt]) }}"
                                confirm="本当に削除する？">削除
                            </x-element.deletebutton> --}}

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-2">
                <x-element.submitbutton value="delete" color="red">
                    チェックをいれた雛形を削除
                </x-element.submitbutton>
            </div>
        </form>

        <div class="py-5"></div>
        <x-element.h1>Toと雛形の説明
        </x-element.h1>

        <x-mailtempre.manual>
        </x-mailtempre.manual>

    </div>



</x-app-layout>
