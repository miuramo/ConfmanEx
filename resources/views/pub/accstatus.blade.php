@php
    $cats = App\Models\Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
    $catcolors = App\Models\Category::select('id', 'name')->get()->pluck('bgcolor', 'id')->toArray();
@endphp
<x-app-layout>

    <x-slot name="header">
        <div class="mb-4">
        </div>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('採択状況の確認') }}
        </h2>
    </x-slot>

    @php
        $catspans = App\Models\Category::spans();
    @endphp
    <div class="px-4 py-4">
        <table class="border-pink-400 border-2">
            <tr class="bg-pink-300">
                @php
                    $hs = ['投稿時カテゴリ', '採否判定カテゴリ', '判定_accID', '件数', 'PaperIDs'];
                @endphp
                @foreach ($hs as $h)
                    <th class="px-2 py-1">{{ $h }}</th>
                @endforeach
            </tr>
            @foreach ($stats as $st)
                <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-pink-50' : 'bg-gray-50' }}">
                    <td class="px-2 py-1 text-center">
                        @if ($st->origcat != $st->hanteicat)
                            {!! $catspans[$st->origcat] !!}
                        @else
                            →
                        @endif
                    </td>
                    <td class="px-2 py-1 text-center">
                        {!! $catspans[$st->hanteicat] !!}
                    </td>
                    <td class="px-2 py-1 text-center">
                        @if ($acc_judges[$st->accept_id] > 0)
                            <span class="text-blue-600 font-bold">{{ $accepts[$st->accept_id] }}</span>
                        @else
                            <span class="text-slate-600">{{ $accepts[$st->accept_id] }}</span>
                        @endif
                        <span class="mx-1"></span>
                        <sub>{{ $st->accept_id }}</sub>
                    </td>
                    <td class="px-2 py-1 text-center">
                        <label
                            for="o{{ $st->origcat }}h{{ $st->hanteicat }}a{{ $st->accept_id }}">{{ $st->cnt }}</label>
                        <span class="mx-1"></span>
                        <input type="checkbox" class="text-pink-600 text-sm sumcheckbox"
                            name="o{$st->origcat}h{$st->hanteicat}a{$st->accept_id}"
                            id="o{{ $st->origcat }}h{{ $st->hanteicat }}a{{ $st->accept_id }}"
                            value="{{ $st->cnt }}">
                    </td>
                    <td class="px-2 py-1 w-96">
                        {{ implode(', ', $paperlist[$st->origcat][$st->hanteicat][$st->accept_id]) }}
                    </td>
                </tr>
            @endforeach
        </table>

        <x-element.h1>チェックした件数の合計： <span id="total">0</span></x-element.h1>

        <x-element.button value="チェックを全てつける" onclick="checkAll(true)" color="lime" size="sm" />
        <span class="mx-2"></span>
        <x-element.button value="チェックを全て外す" onclick="checkAll(false)" color="orange" size="sm" />

    </div>


    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    <script>
        // JavaScript 部分
        document.addEventListener("DOMContentLoaded", () => {
            const checkboxes = document.querySelectorAll(".sumcheckbox");
            const totalElement = document.getElementById("total");

            const calculateTotal = () => {
                // console.log("calculateTotal");
                let total = 0;
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked) {
                        total += parseInt(checkbox.value, 10); // 値を数値に変換して加算
                    }
                });
                totalElement.textContent = total;
            };

            // 各チェックボックスのクリックイベントにリスナーを追加
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener("change", calculateTotal);
            });
        });

        function checkAll(chk) {
            const checkboxes = document.querySelectorAll(".sumcheckbox");
            checkboxes.forEach(checkbox => {
                checkbox.checked = chk;
            });

            const totalElement = document.getElementById("total");
            let total = 0;
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    total += parseInt(checkbox.value, 10); // 値を数値に変換して加算
                }
            });
            totalElement.textContent = total;
        }
    </script>
    @push('localjs')
        <script src="/js/chk_all.js"></script>
    @endpush


</x-app-layout>
