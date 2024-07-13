<x-app-layout>
    <!-- review.pccomment -->
    @php
        $catspans = App\Models\Category::spans();
        $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
        $cats = App\Models\Category::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    @endphp
    @section('title', $cats[$cat_id].' スコア')
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:bg-slate-800 dark:text-slate-400">

            {{ __('査読結果スコアとコメント') }} &nbsp;

            {!! $catspans[$cat_id] !!}

        </h2>
    </x-slot>

    <div class="py-2 px-6">
            <x-review.pccommentmap :subs="$subs" :cat_id="$cat_id" :scoreonly="$scoreonly">
            </x-review.pccommentmap>
    </div>

</x-app-layout>
