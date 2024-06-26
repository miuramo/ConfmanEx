<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Confirm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'grp',
        'valid',
        'mes',
    ];
    /**
     * 免責事項
     */
    public static function seeder_policy()
    {
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy1"
            ],
            [
                "mes" => "本投稿システムでは、投稿管理の都合上、入力された情報（論文PDFや画像、動画など）を加工したり、情報を抽出することがあります。"
                    . "具体的には、論文PDFからのテキスト抽出、ページ画像の作成、画像解像度の調整をすることがあります。"
                    . "<b>投稿用の論文PDFには、かならずテキスト情報が抽出できるものをご準備ください。</b>",
                "valid" => true,
            ]
        );
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy2"
            ],
            [
                "mes" => "入力された情報およびそれらを投稿システムが加工した情報は基本的に本会議（シンポジウム／ワークショップ）の運営目的のみ利用します。"
                    . "ただし、本会議を開催する学会や運営委員会が判断した場合は、論文誌への投稿推奨や学会運営業務に用いることがあります。",
                "valid" => true,
            ]
        );
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy3"
            ],
            [
                "mes" => "投稿者への連絡は主にメールで行います。"
                    . "メールが受信できない状況によって生じる不利益（採択の取り消しや取り下げ）は、すべて投稿者が負うものとします。"
                    . "メール送信エラーが複数回発生した投稿者アカウントについては無断で削除することがあります。",
                "valid" => true,
            ]
        );
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy4"
            ],
            [
                "mes" => "投稿する論文PDFや画像、動画、動画内で使用する楽曲等については、第三者の著作権を侵害しないよう注意してください。"
                    . "第三者の著作権その他の権利及び利益の侵害問題を生じさせた場合、当該論文等の著作者が一切の責任を負うものとします。",
                "valid" => true,
            ]
        );
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy5"
            ],
            [
                "mes" => "公正な査読のため、論文PDFにはかならず共著者をふくむ著者全員の氏名と所属を記してください。"
                    . "<b>投稿する時点で投稿者は共著者全員に確認して了解をとり、著者全員を確定してください。</b>"
                    . "著者が確定していなかった場合（採択通知後に著者の追加や変更を行う必要がある場合）は、採択を取り消すことがあります。",
                "valid" => true,
            ]
        );
        Confirm::firstOrCreate(
            [
                "grp" => 0,
                "name" => "policy6"
            ],
            [
                "mes" => "確認事項に追加や変更が発生した場合、ログイン後の画面にて再度確認をしていただく場合があります。あらかじめご了承ください。",
                "valid" => true,
            ]
        );
    }
}
