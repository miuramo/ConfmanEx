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

            {{ __('査読（編集）') }}

            <x-element.paperid size=2 :paper_id="$review->paper->id">
            </x-element.paperid>

            &nbsp; {!! $catspans[$review->paper->category_id] !!}
        </h2>
    </x-slot>
    @push('localcss')
        <link rel="stylesheet" href="{{ asset('/css/localflash.css') }}">
    @endpush

    <div class="py-2 px-6">
        <x-element.h1>コメント欄を空にすると <span class="text-red-600 font-extrabold">(未入力)</span>
            となります。未入力が一つでもあると査読未完了として扱われます。未入力を避けるため、一言でもよいのでなにか書いてください。</x-element.h1>

        <table class="table-auto">
            <tbody>
                @foreach ($viewpoints as $vpt)
                    <form action="{{ route('review.update', ['review' => $review->id]) }}" method="post"
                        id="revform{{ $vpt->id }}">
                        @csrf
                        @method('put')
                        <input type="hidden" name="paper_id" value="{{ $review->paper->id }}">
                        <input type="hidden" name="review_id" value="{{ $review->id }}">
                        <input type="hidden" name="viewpoint_id" value="{{ $vpt->id }}">
                        @php
                            $formid = "revform{$vpt->id}";
                            $current = isset($scores[$vpt->id]) ? $scores[$vpt->id]->valuestr : null;
                        @endphp
                        <div class="mx-10">
                            <x-enquete.itmedit :itm="$vpt" :formid="$formid" :current="$current" :loop="$loop">
                            </x-enquete.itmedit>
                        </div>
                    </form>
                @endforeach
            </tbody>
        </table>

        <x-element.h1>投稿情報 <span class="mx-3"></span>
            <x-element.paperid size=2 :paper_id="$review->paper->id">
            </x-element.paperid>
            &nbsp; {!! $catspans[$review->paper->category_id] !!}
        </x-element.h1>
        <div class="mx-6 mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
            <div class="w-full">
                <x-file.paperheadimg :paper="$review->paper">
                </x-file.paperheadimg>
            </div>
            <div class="text-sm mt-2 ml-2">
                {{-- まず、showonreviewerindex アンケートをあつめる。 --}}
                <x-enquete.Rev_enqview :rev="$review">
                </x-enquete.Rev_enqview>
            </div>
        </div>

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
