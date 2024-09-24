@props([
    'rev_id' => 1,
])
@php
    $rev = App\Models\Review::find($rev_id);
    if ($rev != null) $token = $rev->token();
@endphp

<!-- components.review.pubshow_link  -->
@isset($rev)
<a target="_blank" href="{{ route('review.pubshow', ['review' => $rev->id, 'token'=>$token]) }}" class="text-blue-600 hover:underline">
    {{ $rev->id }}
</a>
@endisset