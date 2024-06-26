@props([
    'cats' => [],
])

@php
    $cur_left = [];
    $cnames = [];
    foreach ($cats as $c) {
        if ($c->isOpen() && $c->upperlimit > 0) {
            $count = App\Models\Paper::where('category_id', $c->id)
                ->where('deleted', 0)
                ->count();
            $cur_left[$c->id] = 90 - $count;
            $cnames[$c->id] = $c->name;
        }
    }
@endphp

<x-element.h1>
    【投稿件数の制限について】
    @foreach ($cur_left as $cid => $left)
        「{{ $cnames[$cid] }}」の投稿は一人一件に制限されています。残り {{ $left }} 枠です。
    @endforeach
</x-element.h1>
