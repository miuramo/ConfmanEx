@props([
    'cat' => null,
    'subs' => [],
])
@php
    $rigais = App\Models\RevConflict::arr_pu_rigai();
    $accepts = App\Models\Accept::select('name', 'id')->get()->pluck('name', 'id')->toArray();
    $cat_id = $cat->id;
@endphp<!-- components.review.resultmap -->
<table class="min-w-full divide-y divide-gray-200 mb-2 sortable" id="sortable">
    <thead>
        <tr>
            @foreach (['chk', 'pid', 'title', 'accept', 'avg score', 'stddev', 'num finish', 'num assign', 'i'] as $h)
                <th
                    class="p-1 bg-slate-300 dark:bg-slate-400
                @if ($h === 'chk') unsortable @endif
                ">
                    {{ $h }} </th>
            @endforeach
        </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
        @php
            $count = 1;
        @endphp
        @foreach ($subs as $sub)
            @isset($sub->paper)
                @php
                    if ($sub->category_id != $sub->paper->category_id) {
                        continue;
                    }
                @endphp
                @isset($sub->paper->pdf_file)
                    <tr class="{{ $count % 2 === 0 ? 'bg-slate-200 dark:bg-slate-300' : 'bg-white dark:bg-slate-500' }}">
                        <td class="p-1 text-center">
                            <input type="checkbox" name="s_{{ $sub->id }}" value="on">
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->paper->id_03d() }}
                        </td>
                        <td class="p-1">
                            @php
                                // タイトルにリンクをつけるかどうか
                                $enableTitleLink = App\Models\Category::isShowReview($cat_id);
                            @endphp
                            @if ($enableTitleLink && isset($rigais[$sub->paper->id][auth()->id()]) && $rigais[$sub->paper->id][auth()->id()] > 2)
                                <x-review.commentpaper_link :sub="$sub"></x-element.commentpaper_link>
                                @else
                                    <span class="text-gray-400">
                                        {{ $sub->paper->title }}
                                    </span>
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            {{ $accepts[$sub->accept_id] }}
                        </td>
                        <td class="p-1 text-center">
                            @if ($sub->score)
                                {{ sprintf('%4.2f', $sub->score) }}
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            @if ($sub->stddevscore)
                                {{ sprintf('%4.2f', $sub->stddevscore) }}
                            @endif
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->reviews->where('status', 2)->count() }}
                        </td>
                        <td class="p-1 text-center">
                            {{ $sub->reviews->count() }}
                        </td>
                        <td class="p-1 text-center text-gray-400">
                            {{ $count }}
                        </td>
                        @php
                            $count++;
                        @endphp

                    </tr>
                @endisset
            @endisset
        @endforeach
    </tbody>

</table>
