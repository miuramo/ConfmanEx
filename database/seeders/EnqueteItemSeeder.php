<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EnqueteItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 1,
            'name' => 'happyo',
            'desc' => '発表カテゴリ',
            'content' => "発表カテゴリを1つ選択してください。\n:selection : 研究発表 : 実践報告",
            'contentafter' => '必ず1つだけ選んでください。',
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 1,
            'orderint' => 2,
            'name' => 'theme',
            'desc' => '主要テーマ',
            'content' => "発表内容の趣旨として発表者が最も重要だと考えるテーマを1つ選んでください。\n:selection : 情報教育の事例研究 : 教育や学習過程の分析・評価 : 教育・学習の情報化 : 教育学習支援システム : プログラミング教育 : 教育方法・授業設計 : その他",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 2,
            'orderint' => 1,
            'name' => 'demoifaccepted',
            'desc' => '採択時にデモ発表希望',
            'content' => "採択時にデモ発表希望する？\n:selection : はい : いいえ",
            'contentafter' => '「はい」にしたら、デモ機材調査への回答が必須となりますので、このページを再読み込みしてください。',
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 3,
            'orderint' => 3,
            'name' => 'power',
            'desc' => '使用電力量(W)',
            'content' => "使用電力量(W)を整数で入力してください。\n:number : 0 : 9999: 200",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 4,
            'orderint' => 1,
            'name' => 'section',
            'desc' => '組織名',
            'content' => "学部学科、研究室名、部署名など。\n大学名や会社名などの「所属」はページ右上Menu→登録情報で設定してください。\n:text : 60 : 学科名、研究室名、部署名など",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 4,
            'orderint' => 2,
            'name' => 'telnum',
            'desc' => '電話番号',
            'content' => "半角数字とハイフンのみ使用できます。\n:text : 30 : (例)012-345-6789",
            'pregrule' => "/^([0-9-]{10,13})$/",
            'pregerrmes' => "電話番号を正しく入力してください。",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 4,
            'orderint' => 3,
            'name' => 'zipnum',
            'desc' => '郵便番号',
            'content' => "半角数字とハイフンのみ使用できます。\n:text : 30 : (例)012-3456",
            'pregrule' => "/^(\d{3}[-]\d{4}|\d{7})$/",
            'pregerrmes' => "郵便番号を正しく入力してください。",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 4,
            'orderint' => 4,
            'name' => 'postal',
            'desc' => '住所',
            'content' => "都道府県、市区町村、番地、建物名。\n:textarea : 50 : 3 : ",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 4,
            'orderint' => 5,
            'name' => 'isstudent',
            'desc' => '種別（一般 / 学生）',
            'content' => "主たる身分に基づいて選択してください。\n:selection : 一般 : 学生",
            // 'contentafter' => '必ず1つだけ選んでください。',
        ]);

        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 1,
            'name' => 'kubun',
            'desc' => '参加区分',
            'content' => "\n:selection : 一般、もしくは発表のない学生で、ソフトウェア科学会・協賛学会会員"
            . " : 一般、もしくは発表のない学生で、非会員"
            . " : 発表のある学生で、ソフトウェア科学会・協賛学会会員"
            . " : 発表のある学生で、非会員"
            . " : 学生ボランティア"
            . " : プレス"
            . " : スポンサー（スポンサーの方は最大４名までお一人４万円で登録可能です。５人目以降の方は一般でご登録ください。）"
            . " : 招待デモ発表者"
            . " : 招待講演者"
            ,
            // 'contentafter' => '必ず1つだけ選んでください。',
        ]);

        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 2,
            'name' => 'gakkai',
            'desc' => '学会',
            'content' => "\n:selection : 日本ソフトウェア科学会"
            . " : 画像電子学会ビジュアルコンピューティング研究会"
            . " : 芸術科学会"
            . " : 計測自動制御学会システムインテグレーション部門"
            . " : 情報処理学会エンタテインメントコンピューティング研究会"
            . " : 情報処理学会音楽情報科学研究会"
            . " : 情報処理学会音声言語情報処理研究会"
            . " : 情報処理学会コンピュータグラフィックスとビジュアル情報学研究会"
            . " : 情報処理学会デジタルコンテンツクリエーション研究会"
            . " : 情報処理学会ヒューマンコンピュータインタラクション研究会"
            . " : 情報処理学会プログラミング研究会"
            . " : 情報処理学会ユビキタスコンピューティングシステム研究会"
            . " : 情報処理学会ジュニア会員"
            . " : 電子情報通信学会ヒューマンコミュニケーショングループ"
            . " : 日本バーチャルリアリティ学会"
            . " : ヒューマンインタフェース学会"
            . " : その他（上記リストにないが、WISSのトップページに協賛学会として記載があるもの）"
            . " : 非会員"
            ,
            // 'contentafter' => '必ず1つだけ選んでください。',
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 3,
            'name' => 'othergakkai',
            'desc' => '「その他」の学会',
            'content' => "上で「その他」を選んだ場合はこちらに学会名を入力してください。\n:text : 60 : ",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 4,
            'name' => 'kaiinid',
            'desc' => '上記で入力した学会の会員番号',
            'content' => "「非会員」をえらんだ場合は（未入力）のままにしてください。\n:text : 20 : ",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 5,
            'name' => 'roomshare',
            'desc' => '相部屋（家族参加等）',
            'content' => "\n:selection "
            . " : 相部屋利用なし"
            . " : 2名一室（WISS参加者かつ同居家族）"
            . " : 3名一室（WISS参加者かつ同居家族）"
            . " : 4名一室（WISS参加者かつ同居家族）"
            ,
            'contentafter' => "\n2名一室以上を選択した場合は，下の備考欄に「同室に泊まる人の情報」等を記載してください。",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 6,
            'name' => 'receipt',
            'desc' => '領収書の発行',
            'content' => "学生の方は、指導教員に確認してください。\n:selection "
            . " : 「会議費」と「宿泊・食事費」の２枚に分ける"
            . " : 一括で１つ"
            . " : 不要"
            ,
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 7,
            'name' => 'receiptto',
            'desc' => '領収書の宛名',
            'content' => "(未入力) の場合は「ご所属」宛てになります。\n:text : 60 : ",
        ]);

        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 8,
            'name' => 'bikou',
            'desc' => '備考欄',
            'content' => "特別な部屋割り、食品アレルギー、領収書に関するご要望など。\n:textarea : 60 : 3 : ",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 5,
            'orderint' => 9,
            'name' => 'zenpaku',
            'desc' => '前泊',
            'content' => "\n:selection "
            . " : 委員・学生ボランティアとして前泊しない"
            . " : 委員・学生ボランティアとして前泊する"
            ,
            'contentafter' => "\nWISSの委員・学生ボランティアとして前泊する場合は後者を選択してください。",
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 6,
            'orderint' => 1,
            'name' => 'bus1',
            'desc' => '初日の送迎バス乗車の予定',
            'content' => "乗車を確約する「予約」ではありません。\n:selection "
            . " : 以下のバスを利用しない"
            . " : 1便"
            . " : 2便"
            . " : 3便"
            . " : 未定"
            ,
        ]);
        \App\Models\EnqueteItem::factory()->create([
            'enquete_id' => 6,
            'orderint' => 2,
            'name' => 'bus2',
            'desc' => '最終日の送迎バス乗車の予定',
            'content' => "乗車を確約する「予約」ではありません。\n:selection "
            . " : 以下のバスを利用しない"
            . " : 1便"
            . " : 2便"
            . " : 3便"
            . " : 未定"
            ,
        ]);

    }
}
