<x-mail::message>
{{$conftitle}} 参加者のかたへ

{{$conftitle}} における、あなた専用の投票URLをおしらせします。

投稿システムにログインした状態で、以下のURLにアクセスした場合、ログイン中のあなたのユーザアカウントに投票トークンが自動的に設定されます。

投稿システムのアカウントを作成していない場合や、ログアウトした状態で以下のURLにアクセスすると、ブラウザのCookieに投票トークンが設定されます。

---
# 投票URL

[{{ $voteTicket->url() }}]({{ $voteTicket->url() }})

---

[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

