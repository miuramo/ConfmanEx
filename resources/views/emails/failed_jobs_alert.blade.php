<x-mail::message>
<style>
    table.inner-body {
        width: 90%;
    }
</style>

警告：{{ $count }} 件のジョブが失敗しています。


[{{ config('app.name') }}]({{ env('APP_URL') }})


</x-mail::message>


