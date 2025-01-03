<x-app-layout>
    <!-- revcon.index -->
    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('role.top', ['role' => 'admin']) }}" color="gray" size="sm">
                &larr; 管理者 Topに戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('Fill Bidding') }}

        </h2>
    </x-slot>

    @if (session('feedback.success'))
        <x-alert.success>{{ session('feedback.success') }}</x-alert.success>
    @endif
    @if (session('feedback.error'))
        <x-alert.error>{{ session('feedback.error') }}</x-alert.error>
    @endif

    @php
        $cats = App\Models\Category::pluck('name', 'id')->toArray();
        $roles = App\Models\Role::whereIn('name', ['pc', 'metareviewer', 'reviewer'])
            ->pluck('name', 'desc')
            ->toArray();
    @endphp
    <div class="py-4 px-6  dark:text-gray-400">
        <form action="{{ route('revcon.fill_biddingpost') }}" method="post">
            @csrf
            <div class="m-2 p-2">
                <select name="catid">
                    @foreach ($cats as $cid => $cname)
                        <option value="{{ $cid }}">{{ $cname }}</option>
                    @endforeach
                </select>

                <select name="rolename">
                    @foreach ($roles as $rdesc => $rname)
                        <option value="{{ $rname }}">{{ $rdesc }}</option>
                    @endforeach
                </select>
                <x-element.submitbutton color="blue" size="sm"
                    value="fill" >欠けているBiddingを埋める</x-element.submitbutton>
            </div>
        </form>
    </div>


</x-app-layout>
