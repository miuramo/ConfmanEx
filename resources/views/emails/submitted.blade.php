<x-mail::message>
<style>
    table.inner-body {
        width: 90%;
    }
</style>

{{ $owner->name }} さま（および、投稿連絡用メールアドレスに登録された共著者のみなさま）

投稿状況の確認メールです。


---
# PaperID：{{ $paperid }}

# タイトル：{{ $title }}

注：システムが抽出したタイトルに誤りがある場合は、（投稿編集画面下→書誌情報の設定）で修正してください。

<img src="{{ $datauri }}" width="800" style="border: 5px solid #edf2f7;; padding: 2px;">


---
[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>

