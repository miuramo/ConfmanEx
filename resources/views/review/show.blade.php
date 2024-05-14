<x-app-layout>
    @php
        $catspans = App\Models\Category::spans();
    @endphp

    <x-slot name="header">
        <div class="mb-4">
            <x-element.linkbutton href="{{ route('review.index') }}" color="gray" size="sm">
                &larr; 担当査読一覧に戻る
            </x-element.linkbutton>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読（参照）') }}

            <x-element.paperid size=2 :paper_id="$review->paper->id">
            </x-element.paperid>

            &nbsp; {!! $catspans[$review->paper->category_id] !!}
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">

        <table class="table-auto">
            <tbody>
                @foreach ($viewpoints as $vpt)
                    @php
                        // $formid = "revform{$vpt->id}";
                        $current = isset($scores[$vpt->id]) ? $scores[$vpt->id]->valuestr : null;
                    @endphp
                    <div class="mx-10">
                        <x-enquete.itmview :itm="$vpt" :current="$current" :loop="$loop">
                        </x-enquete.itmview>
                    </div>
                @endforeach
            </tbody>
        </table>

        <div class="mb-4 my-10">
            <x-element.linkbutton href="{{ route('review.index') }}" color="gray" size="sm">
                &larr; 担当査読一覧に戻る
            </x-element.linkbutton>
        </div>

    </div>

    @push('localjs')
        <script src="/js/jquery.min.js"></script>
        <script src="/js/form_changed.js"></script>
    @endpush

</x-app-layout>
