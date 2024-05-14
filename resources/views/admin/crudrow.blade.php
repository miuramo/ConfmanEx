<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.crud', ['table' => $tableName]) }}" color="gray" size="sm">
                &larr; {{ $tableName }} に戻る
            </x-element.linkbutton>
            <x-element.linkbutton href="{{ route('admin.crud') }}" color="gray" size="sm">
                &larr; Crud Tables に戻る
            </x-element.linkbutton>
        </div>

        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit') }} id={{ $data[0]->id }} of {{ $tableName }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-4">
        <div class="sm:mx-auto mx-6 sm:px-6 lg:px-8">
            @foreach ($data as $d)
                <table class="divide-y divide-gray-400  dark:text-gray-300">
                    <thead>
                        <tr>
                            @foreach (['field', 'data', 'type'] as $th)
                                <th class="bg-gray-300 dark:bg-gray-600">
                                    {{ $th }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    {{-- @foreach ($coldetails as $nam => $typ)
                            <th class="px-2 py-0 text-sm my-0">{{ $nam }} ({{ $typ }})</th>
                        @endforeach --}}
                    <tbody>
                        @foreach ($coldetails as $nam => $typ)
                            <tr class="">
                                <td class="border-b border-gray-400 px-4 py-2 text-center">{{ $nam }}</td>

                                <td class="border-b border-gray-400 px-4 py-2 hover:text-blue-600 hover:bg-slate-200 clicktoedit"
                                    id="{{ $nam }}__{{ $d->id }}__{{ $typ }}"
                                    data-orig="{{ $d->$nam }}">
                                    {!! nl2br($d->$nam) !!}
                                </td>

                                <td class="border-b border-gray-400 px-4 py-2 text-center">{{ $coldetails[$nam] }}</td>
                                {{-- <td>
                                <a href="{{ route('admin.crud') }}?table={{ $table->name }}"> {{ $table->name }}</a>
                            </td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @if (strlen($row) > 0)
                <div class="mt-4">
                    <x-element.linkbutton href="{{ route('admin.crudcopy', ['table' => $tableName, 'row' => $row]) }}"
                        color="yellow" size="sm">
                        この行 (id={{ $row }}) をコピーして編集
                    </x-element.linkbutton>
                    <span class="px-20"></span>
                    <x-element.linkbutton href="{{ route('admin.cruddelete', ['table' => $tableName, 'row' => $row]) }}"
                        color="red" size="sm" confirm="本当に削除する？">
                        この行 (id={{ $row }}) を削除
                    </x-element.linkbutton>
                </div>
            @endif

            <div class="mt-4">
                <x-element.linkbutton href="{{ route('admin.crud', ['table' => $tableName]) }}" color="gray"
                    size="sm">
                    &larr; {{ $tableName }} に戻る
                </x-element.linkbutton>
                <x-element.linkbutton href="{{ route('admin.crud') }}" color="gray" size="sm">
                    &larr; Crud Tables に戻る
                </x-element.linkbutton>

            </div>

        </div>


    </div>
    <form action="{{ route('admin.crudpost') }}" method="post" id="admincrudpost">
        @csrf
        @method('post')
    </form>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/crud_table.js"></script>
        <script src="/js/crud_where.js"></script>
    @endpush

    <script>
        var table = "{{ $tableName }}";
        var origData = {};
        var mode_br = true; // 改行反映する
        var sizecols = 80; // 横幅
    </script>

</x-app-layout>
