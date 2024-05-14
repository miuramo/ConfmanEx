<x-mail::message>

{{$name}}掲示板 ({{$pid03d}}) に投稿がありました。

# {{$bbsub}}

<x-mail::panel>
{!! nl2br($mes) !!}
</x-mail::panel>

<x-mail::button :url="$bburl" color="success">
掲示板をひらく
</x-mail::button>

---

[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

