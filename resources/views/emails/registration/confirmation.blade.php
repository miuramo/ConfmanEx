<x-mail::message>
# [{{ $conftitle}}] 参加登録が完了しました

{{ $regist->user->name }}様

この度は、{{ $conftitle}} に参加登録いただき、誠にありがとうございます。

以下の内容で参加登録が完了いたしましたので、ご確認ください。

---
## 申込内容の概要
@foreach($regist->toArray() as $key => $value)
- {{ $key}}
    - {{ $value ?? '(未回答)' }}
@endforeach

---

## 申込内容の詳細

@foreach($regist->enq_enqitmdesc_value() as $key => $value)
- {{ $key }}
  - {{ $value ?? '(未回答)' }}
@endforeach

---

ログインして、登録内容をご確認いただけます。

<x-mail::button :url="route('regist.show', ['regist' => $regist->id])">
登録内容の確認
</x-mail::button>

どうぞよろしくお願いいたします。

---

[{{ config('app.name') }} for {{$conftitle}}]({{ env('APP_URL') }})

</x-mail::message>