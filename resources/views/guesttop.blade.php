<x-app-layout>
    <!-- guesttop -->
    @php
        $conf = App\Models\Setting::where('name', 'CONFTITLE')->first();
        $confurl = App\Models\Setting::where('name', 'CONF_URL')->where('valid', true)->first();
        $cfpurl = App\Models\Setting::where('name', 'CFP_URL')->where('valid', true)->first();
    @endphp
    <div
        class="w-4/5 mt-14 ml-6 px-7 py-5  bg-slate-50 text-4xl font-extrabold tracking-wide text-gray-600 drop-shadow-xl dark:bg-slate-700 dark:text-slate-400">
        {{ env('APP_NAME') }}
        @isset($conf->value)
            @isset($confurl)
            for
            <a href="{{ $confurl->value }}" class="hover:underline hover:text-blue-500" target="_blank">{{ $conf->value }}</a>
            @else
                for {{ $conf->value }}
            @endisset
        @endisset
    </div>

    @isset($cfpurl)
        <div class="my-4 mx-8 px-6">
            <a href="{{ $cfpurl->value }}" class="hover:underline hover:text-blue-500 dark:text-gray-300 dark:hover:text-blue-500 text-2xl p-2" target="_blank">論文募集 / Call for Paper</a>
        </div>
    @endisset

    <div class="my-6 mx-6 p-6 bg-slate-200 dark:bg-slate-700 dark:text-slate-400">
        以下の事項について、すべて了解いただける場合のみ、本投稿システムを使用してください。
        <ol class="list-decimal px-8 pt-4">
            <li> 本投稿システムでは、投稿管理の都合上、入力された情報（論文PDFや画像、動画など）を加工したり、情報を抽出することがあります。具体的には、論文PDFからのテキスト抽出、ページ画像の作成、画像解像度の調整をすることがあります。
                <b>投稿用の論文PDFには、かならずテキスト情報が抽出できるものをご準備ください。</b>
            </li>
            <li> 入力された情報およびそれらを投稿システムが加工した情報は基本的に本会議（シンポジウム／ワークショップ）の運営目的のみ利用します。ただし、本会議を開催する学会や運営委員会が判断した場合は、論文誌への投稿推奨や学会運営業務に用いることがあります。
            </li>
            <li> 投稿者への連絡は主にメールで行います。メールが受信できない状況によって生じる不利益（採択の取り消しや取り下げ）は、すべて投稿者が負うものとします。メール送信エラーが複数回発生した投稿者アカウントについては無断で削除することがあります。
            </li>
            <li> 投稿する論文PDFや画像、動画、動画内で使用する楽曲等については、第三者の著作権を侵害しないよう注意してください。第三者の著作権その他の権利及び利益の侵害問題を生じさせた場合、当該論文等の著作者が一切の責任を負うものとします。
            </li>
            <li>
                {{-- （ダブルブラインドではない場合） --}}
                公正な査読のため、論文PDFにはかならず共著者をふくむ著者全員の氏名と所属を記してください。<b>投稿する時点で投稿者は共著者全員に確認して了解をとり、著者全員を確定してください。</b>
                著者が確定していなかった場合（採択通知後に著者の追加や変更を行う必要がある場合）は、採択を取り消すことがあります。</li>
            <li> 確認事項に追加や変更が発生した場合、ログイン後の画面にて再度確認をしていただく場合があります。あらかじめご了承ください。</li>
        </ol>
    </div>

    <div class="my-10 text-center text-gray-300 dark:text-gray-500">
        Powered by <a href="https://github.com/miuramo/ConfmanEx/" target="_blank" class="hover:underline">ConfmanEx</a> <br>
        Copyright &copy; 2024 <a href="https://istlab.info/" target="_blank" class="hover:underline">Motoki Miura</a>
    </div>

</x-app-layout>
