<!-- components.enquete.index -->
<x-app-layout>
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('アンケート管理権限の設定') }}

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

    {{-- 横軸 "roleid_desc","enqid_name" --}}
    <div class="mx-2 my-4">
        <form action="{{ route('enq.maptoroles') }}" method="post" id="maptoroles">
            @csrf
            @method('post')

            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th> enq \ role </th>
                        @foreach ($roleid_desc as $rid => $desc)
                            <th class="p-1 bg-slate-300">{{ $desc }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($enqid_name as $enqid => $enqname)
                        <tr class="{{ $loop->iteration % 2 === 0 ? 'bg-slate-200' : 'bg-white' }}">
                            <td class="p-1 text-right">{{ $enqname }}
                            </td>

                            @foreach ($roleid_desc as $rid => $desc)
                                <td class="p-1 text-center">
                                    <input type="checkbox" id="id_{{ $enqid }}_{{ $rid }}"
                                        name="map_{{ $enqid }}_{{ $rid }}"
                                        @isset($enq_role_map[$enqid][$rid])
                            checked
                        @endisset>
                                </td>
                            @endforeach

                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="text-center my-4">
                <x-element.submitbutton color="blue" value="mapupdate">
                    チェックしたEnq-Roleについて管理権限を設定
                </x-element.submitbutton>
            </div>
        </form>
    </div>

    <div class="py-2 px-6">
        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'pc']) }}" color="gray" size="sm">
                &larr; PC長 Topに戻る
            </x-element.linkbutton>
        </div>
    </div>
    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed_revconflict.js"></script>
    @endpush

</x-app-layout>
