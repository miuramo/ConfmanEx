<!-- components.enquete.resetenqans -->
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

    {{-- 横軸 "catidid_desc", "enqid_name" --}}
    <div class="mx-2 my-4">
        <form action="{{ route('enq.resetenqans') }}" method="post" id="map_enq_cat">
            @csrf
            @method('post')

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="p-1 bg-slate-200"> enq \ cat </th>
                        @foreach ($cats as $cid => $cn)
                            <th class="p-1 bg-slate-300">{{ $cn }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($enqs as $enqid => $enqname)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                            <td class="p-1 text-right">({{ $enqid }}) {{ $enqname }}
                            </td>

                            @foreach ($cats as $cid => $cn)
                                <td class="p-1 text-center">
                                    <input type="checkbox" id="id_{{ $enqid }}_{{ $cid }}"
                                        name="map_{{ $enqid }}_{{ $cid }}"
                                        @isset($cnts[$enqid][$cid])
                                class="hasenq bg-yellow-100"
                            @endisset>
                                    @isset($cnts[$enqid][$cid])
                                    <span class="mx-1"></span>
                                        {{ $cnts[$enqid][$cid] }}
                                    @endisset
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center my-4">
                削除したいアンケート回答のみに、チェックをいれてください。
            </div>
            <div class="text-center my-2">
                <span class="p-2 bg-yellow-200">
                    注：確認ダイアログはでません。Paperがロックされていても削除します。
                </span>
            </div>
            <div class="text-center my-4">
                <x-element.button onclick="check_all(true);" value="全候補にチェックをいれる" size="md" color="yellow" />
                <span class="mx-1"></span>
                <x-element.button onclick="check_all(false);" value="全候補のチェックをはずす" size="md" color="lime" />
                <span class="mx-1"></span>
                <x-element.submitbutton color="red" value="deleteselected">
                    ↑チェックしたアンケート回答を即座に削除
                </x-element.submitbutton> <span class="mx-2"></span>
                
            </div>
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
    @endpush
    <script>
        function check_all(b) {
            $('.hasenq').prop('checked', b);
        }
    </script>
</x-app-layout>
