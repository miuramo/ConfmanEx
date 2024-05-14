<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\MailTemplate::factory()->create([
            'to' => 'accept(1)',
            'subject' => 'PaperID : [:PID:] の論文は[:ACCNAME:]となりました',
            'body' => "# あなたが[:CATNAME:] に投稿された、PaperID : [:PID:] の論文は[:ACCNAME:] として採択されました。

            ## タイトル：[:TITLE:]

            おめでとうございます。",
        ]);
        \App\Models\MailTemplate::factory()->create([
            'to' => 'reject(1)',
            'subject' => 'PaperID : [:PID:] の採否結果',
            'body' => "# あなたが[:CATNAME:] に投稿された、PaperID : [:PID:] の論文は残念ながら[:ACCNAME:] となりました。

            ## タイトル：[:TITLE:]

            査読コメントは投稿システムにログインしてご確認ください。",
        ]);
        \App\Models\MailTemplate::factory()->create([
            'to' => 'roleid(4,5)',
            'subject' => '査読開始',
            'body' => "[:AFFIL:] [:NAME:] 様\n\n査読を開始いたしました。\n\n担当論文は投稿システムにログインしてご確認ください。",
        ]);
        \App\Models\MailTemplate::factory()->create([
            'to' => 'norev()',
            'subject' => '査読未完了？',
            'body' => "[:AFFIL:] [:NAME:] 様\n\n以前お願いしておりました査読について、まだ未完了のようです。\n\nお忙しいところすみませんが、至急投稿システムにログインしてご確認ください。",
        ]);
    }
}
