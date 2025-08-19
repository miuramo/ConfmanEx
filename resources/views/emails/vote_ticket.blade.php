<x-mail::message>
{{ $body }}

---
# 投票トークン設定URL （トークンURL）

[{{ $voteTicket->url() }}]({{ $voteTicket->url() }})

---

[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

