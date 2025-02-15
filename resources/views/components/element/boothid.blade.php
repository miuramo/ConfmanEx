@props([
    'size' => 'xl',
    'paper' => 0,
])
@php
    $show_booth = App\Models\Category::select('status__show_booth', 'id')
        ->get()
        ->pluck('status__show_booth', 'id')
        ->toArray();

    $boothary = [];
    foreach ($paper->submits as $sub) {
        if ($show_booth[$sub->category_id]) {
            $boothary[] = $sub->booth;
        }
    }
    $booth = implode(' / ', $boothary);
    $font = "text-{$size} px-2 py-2";
@endphp
<!-- components.element.boothid -->
{{-- <div class="my-4"> --}}
<span class="mx-2 my-4 text-gray-50 bg-pink-600 {{ $font }} rounded-xl font-extrabold dark:bg-gray-400">発表番号 :
    {{ $booth }}</span>
{{-- </div> --}}