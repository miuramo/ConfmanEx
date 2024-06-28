<x-app-layout>
    <!-- revcon.revstatus -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読進捗 Status') }}

        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $aismeta = ['一般', 'メタ'];
        $astatus = ['' => '未着手', '0' => '未入力', '1' => '途中（未完了あり）', '2' => '完了'];
        $cstatus = ['' => 'red', '0' => 'orange', '1' => 'lime', '2' => 'cyan'];
        $catspans = App\Models\Category::spans();

    @endphp
    <div class="py-3 px-2  dark:text-gray-400">
        <x-element.h1>査読状況</x-element.h1>

        <table class="ml-2 min-w divide-y divide-gray-200">
            <thead>
                <tr>
                    @foreach (['カテゴリ', '種別', '状況', '数', '％'] as $h)
                        <th class="px-2 pt-2 bg-lime-200">{{ $h }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($cmsc as $n => $msc)
                    <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-lime-50' : 'bg-white' }}">
                        {{-- <tr class="bg-{{$cstatus[$msc->status]}}-100"> --}}

                        <td class="px-1 text-center">{{ $cats[$msc->category_id] }}
                        </td>
                        <td class="px-1 text-center">{{ $aismeta[$msc->ismeta] }}
                        </td>
                        <td class="px-1 text-center bg-{{ $cstatus[$msc->status] }}-100">{{ $astatus[$msc->status] }}
                        </td>
                        <td class="px-1 text-right bg-{{ $cstatus[$msc->status] }}-100">{{ $msc->count }}
                        </td>
                        <td class="px-1 text-right bg-{{ $cstatus[$msc->status] }}-100">
                            {{ sprintf('%4.2f', ($msc->count * 100) / $sum_cm[$msc->category_id][$msc->ismeta]) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-3 px-2  dark:text-gray-400">
        <x-element.h1>査読者ごとの状況</x-element.h1>
        <table class="ml-2 min-w divide-y divide-gray-200">
            <thead>
                <tr>
                    <th class="p-1 bg-lime-200">氏名
                    </th>
                    @foreach ($astatus as $key => $val)
                        <th class="p-1 px-2 bg-{{ $cstatus[$key] }}-300">{{ $val }}
                        </th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($revusers as $uid => $uname)
                    <tr class="{{ $loop->iteration % 2 === 1 ? 'bg-lime-50' : 'bg-white' }}">
                        <td class="px-1 text-center">{{ $uname }}
                        </td>
                        @foreach ($astatus as $key => $val)
                            <td class="px-1 text-center">
                                @isset($usary[$uid][$key])
                                    {{ $usary[$uid][$key] }}
                                @else
                                    0
                                @endisset
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="py-3 px-2  dark:text-gray-400">
        <x-element.h1>Zip 未ダウンロード</x-element.h1>
        @foreach ($cats as $catid => $cname)
            <div class="mx-2 my-4">
                {!! $catspans[$catid] !!}
                @foreach ($nd[$catid] as $u)
                    {{ $u['name'] }}
                @endforeach
            </div>
        @endforeach
    </div>


</x-app-layout>
