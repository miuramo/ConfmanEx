@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Paper Status') }}
        </h2>
    </x-slot>

    @php
        $fs = ['category_id', 'valid', 'locked', 'cnt'];
    @endphp
    <div class="px-4 py-4">
        <div class="py-2">
            凡例： <span class="bg-orange-200 px-1 hover:bg-yellow-100">アンロック状態 PaperID</span>
            <span class="bg-green-200 px-1 hover:bg-yellow-100">ロック状態 PaperID</span>
            <span class="mx-3">cntは件数(count)</span>
            <span class="mx-3">ロックすると、著者による「著者名と所属」「書誌情報」の変更ができなくなります。</span>
        </div>
        <table class="divide-y divide-gray-200">
            <thead>
                <tr>
                    @foreach ($fs as $h)
                        <th class="p-1 bg-slate-300">{{ $h }}</th>
                    @endforeach
                    <th class="p-1 bg-slate-300">pid </th>
                </tr>

            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($cols as $col)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                        @foreach ($fs as $f)
                            <td class="p-1 text-center">{{ $col->{$f} }}</td>
                        @endforeach
                        <td>
                            @if (isset($pids[$col->category_id][$col->valid][$col->locked]))
                                @foreach ($pids[$col->category_id][$col->valid][$col->locked] as $pid)
                                    @if ($col->locked)
                                        <span class="bg-green-200 px-1 hover:bg-yellow-100">{{ $pid }}</span>
                                    @else
                                        <span class="bg-orange-200 px-1 hover:bg-yellow-100">{{ $pid }}</span>
                                    @endif
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-2">
        <form action="{{ route('paper.adminlock') }}" method="post" id="paper_adminlock">
            @csrf
            @method('post')
            @foreach ($cats as $catid => $catname)
                <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                    id="label{{ $catid }}" class="text-{{ $catcolors[$catid] }}-200"
                    @isset($targets[$catid])
            checked="checked"
            @endisset> <label
                    for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
            @endforeach
            <x-element.submitbutton value="lock" color="green">ロックする
            </x-element.submitbutton>
            <x-element.submitbutton value="unlock" color="orange">アンロックする
            </x-element.submitbutton>
            <x-element.gendospan>操作対象は、deleted_at is null のみです。</x-element.gendospan>
        </form>
    </div>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


</x-app-layout>
