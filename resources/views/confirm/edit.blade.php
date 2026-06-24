@php
    if (!isset($back_link_href)) {
        $back_link_href = route('role.top', ['role' => 'pc']);
    }
    if (!isset($back_link_label)) {
        $back_link_label = 'PC長 Topに戻る';
    }
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ $back_link_href }}" color="gray" size="sm">
                &larr; {{ $back_link_label }}
            </x-element.linkbutton>
        </div>

        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            連絡事項の編集：{{ $title }}
        </h2>

        <div class="py-4 px-4">
            @foreach ($titles as $grpid => $title)
                <x-element.linkbutton2 href="{{ route('confirm.edit', ['grp' => $grpid]) }}" color="lime" size="sm"
                    class="mr-2 mb-2">
                    {{ $title }}
                </x-element.linkbutton2>
                <span class="mx-2"></span>
            @endforeach
        </div>

    </x-slot>
    <style>
        /* CHECKBOX TOGGLE SWITCH */
        /* @apply rules for documentation, these do not work as inline style */
        .toggle-checkbox:checked {
            @apply: right-0 border-green-400;
            right: 0;
            border-color: #68D391;
        }

        .toggle-checkbox:checked+.toggle-label {
            @apply: bg-green-400;
            background-color: #68D391;
        }
    </style>
    @section('title', $title)

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}
            @if (session('altlink'))
                <span
                    class="ml-4 text-xl text-blue-100  bg-blue-700 dark:text-gray-300 hover:bg-blue-500 hover:text-white p-2 rounded-md">
                    {!! session('altlink') !!}
                </span>
            @endif
        </x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush


    <div class="py-2">
        @isset($note)
            <div class="mx-6 px-4 py-2 bg-yellow-200 dark:bg-yellow-800 dark:text-yellow-200 rounded-lg">
                {!! $note !!}
            </div>
        @endisset
        <div class="sm:mx-auto mx-6 sm:px-6 lg:px-8">
            <table class="divide-y divide-gray-400  dark:text-gray-300">
                <thead>
                    <tr>
                        @foreach ($coldetails as $nam => $typ)
                            <th class="px-2 py-2 text-sm my-0">
                                @if (isset($tableComments[$nam]))
                                    {{ $tableComments[$nam] }}
                                @endif
                                {{ str_replace('status__', '', $nam) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $d)
                        <tr>
                            @foreach ($coldetails as $nam => $typ)
                                @if ($nam == 'COPY')
                                    <td class="hover:bg-slate-200">
                                        <x-element.linkbutton2
                                            href="{{ route('confirm.edit_copy', ['copy_id' => $d->id, 'grp' => $grp]) }}"
                                            color="yellow" size="sm">
                                            Copy
                                        </x-element.linkbutton2>
                                        <div class="my-2"></div>
                                        <x-element.linkbutton2
                                            href="{{ route('confirm.edit_delete', ['del_id' => $d->id, 'grp' => $grp]) }}"
                                            color="red" size="sm">
                                            Del
                                        </x-element.linkbutton2>
                                    </td>
                                @else
                                    @if ($typ == 'tinyint')
                                        <td class="p-2 hover:text-blue-600 hover:bg-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-500 text-center"
                                            id="td__{{ $nam }}__{{ $d->id }}__{{ $typ }}">
                                            <x-toggle formid="admincrudpost"
                                                name="name_{{ $nam }}__{{ $d->id }}__{{ $typ }}"
                                                id="{{ $nam }}__{{ $d->id }}__{{ $typ }}"
                                                :checked="$d->$nam"></x-toggle>
                                        @else
                                        <td class="p-2 hover:text-blue-600 hover:bg-slate-200 clicktoedit  dark:hover:bg-slate-700 dark:hover:text-blue-500"
                                            id="{{ $nam }}__{{ $d->id }}__{{ $typ }}">
                                            {{ $d->$nam }}
                                    @endif
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="px-4">
        <x-element.linkbutton href="{{ route('confirm.renumber_name', ['grp' => $grp]) }}" color="cyan" size="sm">
            nameの連番を修正
        </x-element.linkbutton>
    </div>
    

    <div class="px-4">
        <x-element.h1>プレビュー</x-element.h1>
        <ol class="list-decimal px-8 pt-4">
            @foreach ($data as $d)
                <li>{!! $d->mes !!}</li>
            @endforeach
        </ol>
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
