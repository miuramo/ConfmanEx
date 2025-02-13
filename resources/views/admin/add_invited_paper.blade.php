<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top',['role'=>'admin']) }}" color="gray" size="sm">
                &larr; 管理者 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('招待論文の作成') }}
            <span class="mx-6"></span>
        </h2>

    </x-slot>

    <div class="bg-teal-200 px-4 py-3 m-6">
        「招待論文の作成」をおすと、操作者を Owner とした投稿情報が作成されます。<br>
        アンケート回答やファイル追加、contactemail の編集は、投稿一覧から操作者(Owner) が行ってください。<br>
        セッション割り当て、書誌情報の修正は、出版/Web Toppage から行ってください。<br>
        Owner の変更は、CRUD で行ってください。<br>
    </div>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        $accepts = App\Models\Accept::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    @endphp

    <div class="py-2 px-2">
        <div class="mx-2 py-4">
            <form action="{{ route('add_invited_paper') }}" method="post" id="addinvitedpaper">
                @csrf
                @method('post')
                <table>
                    <tr class="border border-gray-400 p-2">
                        <td class="p-2">作成するカテゴリを選択 →</td>
                        <td>
                            @foreach ($cats as $id => $name)
                                <span class="hover:bg-lime-200 p-1">
                                <input type="radio" name="catid" value="{{ $id }}" id="id_{{ $name }}" />
                                <label for="id_{{$name}}">{{ $name }}</label>
                                <span class="mx-2"></span>
                                </span>
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2">適用する「採択タグ」を選択 →</td>
                        <td>
                            <select name="accid">
                                @foreach ($accepts as $id => $name)
                                    @if (str_starts_with($name, '予備'))
                                        @continue
                                    @endif
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2">タイトルを入力 →</td>
                        <td>
                            <input type="text" name="title" size="80" />
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2">著者リストを入力 →</td>
                        <td>
                            <textarea name="authorlist" cols="80" rows="4"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="p-2">概要を入力 →</td>
                        <td>
                            <textarea type="text" name="abst" cols="80" rows="6"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <x-element.submitbutton color="orange" value="create" confirm="本当に？">
                                招待論文の作成
                            </x-element.submitbutton>
                        </td>
                    </tr>
                </table>

            </form>
        </div>


        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('role.top',['role'=>'admin']) }}" color="gray" size="sm">
                &larr; 管理者 Topに戻る
            </x-element.linkbutton>
        </div>
    </div>

</x-app-layout>
