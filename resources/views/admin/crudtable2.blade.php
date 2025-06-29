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
            {{ $title }}
        </h2>
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
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-4">
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
                                    <td>
                                        @isset($enq_id)
                                            <x-element.linkbutton2
                                                href="{{ route('enq.enqitmsetting', ['copy_id' => $d->id, 'enq_id' => $enq_id, 'enq_name' => $enq_name]) }}"
                                                color="yellow" size="sm">
                                                Copy
                                            </x-element.linkbutton2>
                                            <div class="my-2"></div>
                                            <x-element.linkbutton2
                                                href="{{ route('enq.enqitmsetting', ['del_id' => $d->id, 'enq_id' => $enq_id, 'enq_name' => $enq_name]) }}"
                                                color="red" size="sm">
                                                Del
                                            </x-element.linkbutton2>
                                        @else
                                            <x-element.linkbutton2
                                                href="{{ route('viewpoint.itmsetting', ['copy_id' => $d->id, 'cat_id' => $cat_id, 'cat_name' => $cat_name]) }}"
                                                color="yellow" size="sm">
                                                Copy
                                            </x-element.linkbutton2>
                                            <div class="my-2"></div>
                                            <x-element.linkbutton2
                                                href="{{ route('viewpoint.itmsetting', ['del_id' => $d->id, 'cat_id' => $cat_id, 'cat_name' => $cat_name]) }}"
                                                color="red" size="sm">
                                                Del
                                            </x-element.linkbutton2>
                                        @endisset
                                    </td>
                                @else
                                    @if ($typ == 'tinyint')
                                        <td class="p-2 hover:text-blue-600 hover:bg-slate-200 dark:hover:bg-slate-700 dark:hover:text-blue-500 text-center"
                                            id="td__{{ $nam }}__{{ $d->id }}__{{ $typ }}">
                                            <x-toggle formid="admincrudpost" name="name_{{ $nam }}__{{ $d->id }}__{{ $typ }}" id="{{ $nam }}__{{ $d->id }}__{{ $typ }}"
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
