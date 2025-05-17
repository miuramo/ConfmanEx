<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('admin.crud') }}" color="gray" size="sm">
                &larr; Crud Tables に戻る
            </x-element.linkbutton>
        </div>

        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crud Table') }} {{ $tableName }} {{ count($data) }}/{{ $numdata }}
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
            <table class="divide-y divide-gray-400  dark:text-gray-300">
                <thead>
                    <tr>
                        <th>Chk
                        </th>

                        @foreach ($coldetails as $nam => $typ)
                            <th class="px-2 py-0 text-sm my-0">{{ $nam }} ({{ $typ }})</th>
                        @endforeach
                    </tr>
                    <form action="{{ route('admin.crud') }}?table={{ $tableName }}" method="post"
                        id="admincrudwhere">
                        @csrf
                        @method('post')
                        <tr class="m-0">
                            @foreach ($coldetails as $nam => $typ)
                                <th><input id="whereby{{ $nam }}" type="text"
                                        class="whereBy text-sm bg-slate-100 font-thin mr-2 p-0 h-5 w-full"
                                        name="whereBy__{{ $nam }}"
                                        @isset($whereBy[$nam])
                                        value= {{ $whereBy[$nam] }}
                                        @endisset>
                                </th>
                            @endforeach
                        </tr>
                    </form>
                </thead>
                <tbody>
                    @foreach ($data as $d)
                        <tr>
                            <td>
                                <input type="checkbox" class="chkbox" name="did[]" form="chkdelete"
                                    value="{{ $d->id }}">
                            </td>

                            @foreach ($coldetails as $nam => $typ)
                                <td class="px-2 hover:text-blue-600 hover:bg-slate-200 clicktoedit  dark:hover:bg-slate-700 dark:hover:text-blue-500"
                                    id="{{ $nam }}__{{ $d->id }}__{{ $typ }}">
                                    @if ($nam == 'id')
                                        <a
                                            href="{{ route('admin.crud', ['table' => $tableName, 'row' => $d->id]) }}">{{ $d->$nam }}</a>
                                    @else
                                        {{ $d->$nam }}
                                    @endif
                                </td>
                            @endforeach
                            {{-- <td>
                                <a href="{{ route('admin.crud') }}?table={{ $table->name }}"> {{ $table->name }}</a>
                            </td> --}}
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                <x-element.linkbutton href="{{ route('admin.crudnew', ['table' => $tableName]) }}" color="yellow"
                    size="sm">
                    あたらしい行を追加
                </x-element.linkbutton>
            </div>

            <div class="mt-4">
                <form action="{{ route('admin.crudchkdelete') }}" method="post" id="chkdelete">
                    @csrf
                    @method('post')
                    <input type="hidden" name="table" value="{{ $tableName }}">
                    <x-element.submitbutton type="submit" color="purple" size="sm" confirm="本当にまとめて削除する？">
                        選択した行を削除
                    </x-element.submitbutton>
                </form>
            </div>

            <div class="mt-4">
                <x-element.linkbutton href="{{ route('admin.crud') }}" color="gray" size="sm">
                    &larr; Crud Tables に戻る
                </x-element.linkbutton>
            </div>

            <div class="mt-4">
                <x-element.linkbutton href="{{ route('admin.crudtruncate', ['table' => $tableName]) }}" color="red"
                    size="sm" confirm="本当に、すべての行を削除(truncate)しますか?">
                    すべての行を削除(truncate)
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
    </script>

</x-app-layout>
