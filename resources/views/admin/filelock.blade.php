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
            {{ __('File Status') }}
        </h2>
    </x-slot>

    @php
        $fs = [
            'category_id' => 'カテゴリ',
            'valid' => 'valid',
            'deleted' => 'deleted',
            'pending' => 'pending',
            'locked' => 'locked',
            'cnt' => 'cnt',
        ];
        // サプリメントファイルのmimeタイプを集める
        $mimes = App\Models\File::select('mime')->distinct()->orderBy('mime')->get();
    @endphp
    <div class="px-4 py-4">
        <div class="py-2 dark:text-gray-400">
            凡例： <span class="bg-orange-200 px-1 hover:bg-yellow-100">アンロック状態 PaperID (FileId)</span>
            <span class="bg-green-200 px-1 hover:bg-yellow-100">ロック状態 PaperID (FileId)</span>
            <span class="mx-3">cntは件数(count)</span>
            <span class="mx-3">ファイルをロックすると、著者があたらしいファイルをアップロードしたときにPendingの状態となり、差替えができなくなります。</span>
        </div>
        <table class="divide-y divide-gray-200">
            <thead>
                <tr>
                    @foreach ($fs as $f => $h)
                        <th class="p-1 bg-slate-300">{{ $h }}</th>
                    @endforeach
                    <th class="p-1 bg-slate-300">pid (fid mime)</th>
                </tr>

            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($cols as $col)
                    <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white dark:bg-slate-400' }}">
                        @foreach ($fs as $f => $h)
                            @if ($f == 'category_id' && is_numeric($col->{$f}))
                                <td class="p-1 text-center">{{ $cats[$col->{$f}] }}</td>
                            @else
                                <td class="p-1 text-center">{{ $col->{$f} }}</td>
                            @endif
                        @endforeach
                        <td>
                            @if (isset($pids[$col->category_id][$col->valid][$col->deleted][$col->pending][$col->locked]))
                                @foreach ($pids[$col->category_id][$col->valid][$col->deleted][$col->pending][$col->locked] as $pid)
                                    <a href="{{ route('file.showhash', ['file' => $fileids[$pid], 'hash' => substr($filekeys[$pid], 0, 8)]) }}"
                                        target="_blank">
                                        @if ($col->locked)
                                            <span
                                                class="bg-green-200 px-1 hover:bg-yellow-100" >{{ $pid }}</span>
                                        @else
                                            <span
                                                class="bg-orange-200 px-1 hover:bg-yellow-100" title="{{$timestamps[$pid]}}">{{ $pid }}</span>
                                        @endif
                                    </a>
                                @endforeach
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        {{-- <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @foreach ($roles as $role)
                <span>
                    <x-element.linkbutton href="{{ route('role.top', ['role' => $role->name]) }}" color="cyan">
                        {{ $role->desc }}
                    </x-element.linkbutton>
                </span>
            @endforeach
        </div> --}}
    </div>
    <div class="px-6 py-2">
        <!-- FileController.adminlock filelock.blade.php -->
        <form action="{{ route('file.adminlock') }}" method="post" id="file_adminlock">
            @csrf
            @method('post')
            <div class="my-2">
                @foreach ($cats as $catid => $catname)
                    <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                        id="label{{ $catid }}" class="text-{{ $catcolors[$catid] }}-200"
                        @isset($targets[$catid])
            checked="checked"
            @endisset> <label
                        for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
                    <span class="mx-2"></span>
                @endforeach
            </div>
            <div class="my-2 border-slate-400 border-2 bg-slate-200 p-2 dark:bg-gray-500">
                <input type="checkbox" name="targetmainpdf" value="1" checked="checked" id="labelmain">
                <label for="labelmain" class="dark:text-gray-300">メインの論文PDFファイルをロック／アンロック対象とする</label><span
                    class="mx-1"></span>
            </div>

            <div class="mx-8 my-2 border-slate-400 border-2 bg-slate-200 p-2 dark:bg-gray-500">
                サプリメントファイルをロック／アンロック対象に含めるときは、以下にチェックをいれてください。<br>
                @foreach ($mimes as $nn => $mime)
                    <input type="checkbox" name="targetmime{{ $nn }}" value="{{ $mime['mime'] }}"
                        id="labelmime{{ $nn }}">
                    <label for="labelmime{{ $nn }}"
                        class="dark:text-gray-300">{{ $mime['mime'] }}</label><span class="mx-2"></span>
                @endforeach

            </div>
            <div class="my-2">

                @foreach (['すべて' => 'all', '採択のみ' => 'accepted', '不採択のみ' => 'rejected'] as $lbl => $val)
                    <input type="radio" name="targetaccept" id="id_{{ $val }}" value="{{ $val }}"
                        @if ($val == 'all') checked="checked" @endif>
                    <label for="id_{{ $val }}" class="dark:text-gray-400">{{ $lbl }}</label>
                @endforeach
                <span class="mx-2"></span>
                <x-element.submitbutton value="lock" color="green">ロックする
                </x-element.submitbutton>
                <span class="mx-1"></span>
                <x-element.submitbutton value="unlock" color="orange">アンロックする
                </x-element.submitbutton>
                <x-element.gendospan>操作対象は、deleted=0 かつ pending=0 のみです。</x-element.gendospan>
            </div>
        </form>
    </div>
    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif


</x-app-layout>
