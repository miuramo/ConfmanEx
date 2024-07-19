@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pub']) }}" color="gray" size="sm">
                &larr; 出版 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('別カテゴリでの採否を追加する') }}
        </h2>
    </x-slot>

    <div class="px-4 py-4">
        <form action="{{ route('pub.addsubmit') }}" method="post" id="addsubmit">
            @csrf
            @method('post')

            <div class="mx-4">

                当初
                <select id="catid" name="catid">
                    @foreach ($cats as $n => $cat)
                        <option value="{{ $n }}" @if (isset($old['catid']) && $old['catid'] == $n) selected @endif>
                            {{ $cat }}</option>
                    @endforeach
                </select>
                で投稿され、<br>
                <select id="accid" name="accid" value="{{ $old['accid'] }}">
                    @foreach ($accepts as $n => $acc)
                        <option value="{{ $n }}" @if (isset($old['accid']) && $old['accid'] == $n) selected @endif>
                            {{ $acc }}</option>
                    @endforeach
                </select>
                の判定がついた論文について、
            </div>
            <div class="mx-4 my-4">
                <x-element.submitbutton value="preview" color="lime">該当する論文タイトルを確認する
                </x-element.submitbutton>
            </div>
            <div class="mx-4">
                上記の条件に該当する論文について、
                <select id="newcatid" name="newcatid">
                    @foreach ($cats as $n => $cat)
                        <option value="{{ $n }}" @if (isset($old['newcatid']) && $old['newcatid'] == $n) selected @endif>
                            {{ $cat }}</option>
                    @endforeach
                </select>
                のカテゴリにおいて、<br>判定を
                <select id="newaccid" name="newaccid">
                    @foreach ($accepts as $n => $acc)
                        <option value="{{ $n }}" @if (isset($old['newaccid']) && $old['newaccid'] == $n) selected @endif>
                            {{ $acc }}</option>
                    @endforeach
                </select>
                とした
                <x-element.submitbutton value="addsubmit" color="orange">別カテゴリ採否を追加する
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    @if (count($papers) > 0)
        <div class="mx-4">

            <x-element.h1>該当論文は {{ count($papers) }}件あります。
            </x-element.h1>
            <div class="mx-4">
                @foreach ($papers as $pid => $title)
                    <x-element.paperid :paper_id="$pid" size=1></x-element.paperid>
                    {{ $title }} <br>
                @endforeach
            </div>
        </div>
    @else
        <div class="mx-4 p-4 bg-yellow-100">該当する論文はありません。</div>
    @endif



    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @isset($cols)
        <div class="mx-4">
            <x-element.h1>現在の採択タグ状況</x-element.h1>
            <table class="divide-y divide-gray-200 mx-2">
                <thead>
                    <tr class="bg-slate-300">
                        <th class="p-1">
                            cat
                        </th>
                        <th class="p-1">
                            acc_id
                        </th>
                        <th class="p-1">
                            acc_name
                        </th>
                        <th class="p-1">
                            acc_judge
                        </th>
                        <th class="p-1">
                            papers
                        </th>
                        <th class="p-1">
                            count
                        </th>
                    </tr>
                </thead>
                @php
                    $fs = ['category_id', 'accept_id', 'name', 'judge'];
                @endphp
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($cols as $col)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                            @foreach ($fs as $f)
                                <td class="p-1 text-center">{{ $col->{$f} }}</td>
                            @endforeach
                            <td>
                                @if (isset($pids[$col->category_id][$col->accept_id]))
                                    @foreach ($pids[$col->category_id][$col->accept_id] as $pid)
                                        <span class="bg-green-200 px-1 hover:bg-yellow-100">{{ $pid }}</span>
                                    @endforeach
                                @endif
                            </td>
                            <td class="p-1 text-center">
                                {{ count($pids[$col->category_id][$col->accept_id]) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endisset

    {{-- @push('localjs')
        <script src="/js/jquery.min.js"></script>
    @endpush --}}

</x-app-layout>
