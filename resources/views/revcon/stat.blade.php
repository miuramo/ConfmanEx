<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('Bidding Stat') }}

        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $bidbgs = App\Models\Bidding::pluck("bgcolor", "id")->toArray();
        $bidbgs[6] = 'black';
        $heads = ['利害by著者'=>1, '利害by査読者'=>2, '困難'=>3, '可能'=>4, '希望'=>5,'pid'=>6, 'title'=>6];
    @endphp
    <div class="py-4 px-6  dark:text-gray-400">
        @foreach ($cats as $cid => $cname)
            <x-element.h1>{{ $cname }} </x-element.h1>
            <div class="m-2 p-2">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            @foreach ($heads as $h=>$num)
                                <th class="p-1 bg-slate-300 text-{{$bidbgs[$num]}}-500">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($papers_in_cat[$cid] as $pid => $pname)
                            <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                                @foreach ([1, 2, 3, 4, 5] as $num)
                                    <td class="p-1 text-center text-{{$bidbgs[$num]}}-500">
                                        @isset($counts[$cid][$pid][$num])
                                            {{ $counts[$cid][$pid][$num] }}
                                        @else
                                            0
                                        @endisset
                                    </td>
                                @endforeach
                                <td class="p-1">{{ sprintf("%03d",$pid) }}
                                </td>
                                <td class="p-1 text-sm">{{ $pname }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>


</x-app-layout>
