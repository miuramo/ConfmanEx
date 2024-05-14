<x-app-layout>
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    @endphp
        @section('title', $cats[$cat_id].' 結果')

    <x-slot name="header">
        {{-- <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'reviewer']) }}" color="gray" size="sm">
                &larr; 査読者Topに戻る
            </x-element.linkbutton>
        </div> --}}
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読結果') }} &nbsp;

            {!! $catspans[$cat_id] !!}

        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">
        <form action="{{ route('review.resultpost', ['cat' => $cat]) }}" method="post" id="reviewresult">
            @csrf
            @method('post')

            <x-review.resultmap :subs="$subs">
            </x-review.resultmap>

            @can('role', 'pc')
            <div class="py-4">
            <x-element.button onclick="CheckAll('reviewresult')" color="lime" value="すべてチェック">
            </x-element.button>
            &nbsp;
            <x-element.button onclick="UnCheckAll('reviewresult')" color="orange" value="すべてチェック解除">
            </x-element.button>
            @endcan
        </div>
        @can('role', 'pc')
        <select id="uprev" name="uprev">
                @foreach ($accepts as $n => $acc)
                    <option value="{{ $n }}">{{ $acc }}</option>
                @endforeach
            </select>
            <x-element.submitbutton value="chk" color="yellow">チェックした査読結果を更新
            </x-element.submitbutton>
            <div class="py-4">
                <x-element.submitbutton value="excel" color="teal">査読結果をExcel Download
                </x-element.submitbutton>
            </div>
@endcan
        </form>

    </div>
    <script>
        function CheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = true;
                }
            }
        }

        function UnCheckAll(formname) {
            for (var i = 0; i < document.forms[formname].elements.length; i++) {
                if (document.forms[formname].elements[i].type != "radio") {
                    document.forms[formname].elements[i].checked = false;
                }
            }
        }
    </script>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
