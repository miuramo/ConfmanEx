@props([
    'sub' => null,
])

<!-- components.review.commentpaper_link  -->
<a class="hover:underline" href="{{ route('review.commentpaper', ['cat'=>$sub->category_id, 'paper' => $sub->paper, 'token' => $sub->token() ]) }}" target="_blank">
    {{ $sub->paper->title }}
</a>
