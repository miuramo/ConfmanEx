<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            アンケート回答の選択的削除

        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @php
        $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    @endphp
    <div class="px-6 py-2 flex-grow">
        <form action="{{ route('enq.resetenqans') }}" method="post" id="enq_resetenqans">
            @csrf
            @method('post')
            <div>
                @foreach ($cats as $catid => $catname)
                    <input type="checkbox" name="targetcat{{ $catid }}" value="{{ $catid }}"
                        id="label{{ $catid }}" @if ($catid == 1) checked="checked" @endif>
                    <label for="label{{ $catid }}" class="dark:text-gray-300">{{ $catname }}</label>
                    &nbsp;
                @endforeach
            </div>
            <x-element.submitbutton value="view" color="red">↑選択カテゴリに関するアンケート回答の削除
            </x-element.submitbutton>
        </form>
    </div>

    <div class="py-2 px-6">
        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('enq.index') }}" color="gray" size="sm">
                &larr; アンケート一覧 に戻る
            </x-element.linkbutton>
        </div>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
