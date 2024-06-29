<x-mail::message>
<style>
    table.inner-body {
        width: 90%;
    }
</style>

{{ $owner->name }} さま（および、投稿連絡用メールアドレスに登録された共著者のみなさま）

以下のアドレスが送信エラーとなっていたため、投稿連絡用メールアドレスから除外しました。

{{ $invalid_email }}

必要があれば共著者間で連携をとっていただき、代替のメールアドレスをご登録ください。


---


# タイトル：{{ $title }}

注：システムが抽出したタイトルに誤りがある場合は、（投稿編集画面下→書誌情報の設定）で修正してください。

<img src="{{ $datauri }}" width="800" style="border: 5px solid #edf2f7;; padding: 2px;">


---
[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

