<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Crud Tables') }}
        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <table class="divide-y divide-gray-400  dark:text-gray-300">
                <thead>
                    <tr>
                        <th class="px-2">cnt</th>
                        <th class="px-2">table name</th>
                        <th class="px-2">(row mode)</th>
                        <th class="px-2">(ajax mode)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tables as $table)
                        <tr>
                            <td class="px-2 text-right">
                                {{ $tableDataCounts[$table] }}
                            </td>
                            <td class="px-2 text-center">
                                <a href="{{ route('admin.crud') }}?table={{ $table }}" class="hover:underline hover:text-blue-500"> {{ $table }} </a>
                            </td>
                            <td class="px-2 text-center">
                                <a href="{{ route('admin.crud') }}?table={{ $table }}&row" class="hover:underline hover:text-blue-500"> {{ $table }} </a>
                            </td>
                            <td class="px-2 text-center">
                                <a href="{{ route('admin.crudajax') }}?table={{ $table }}" class="hover:underline hover:text-blue-500"> {{ $table }} </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
