<?php

namespace App\Mail;

use App\Models\Bb;
use App\Models\BbMes;
use App\Models\File;
use App\Models\Paper;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BbNotify extends RetryMailable
{

    public Bb $bb;
    public BbMes $bbmes;
    public Paper $paper;
    public string $name; // 〜〜掲示板

    /**
     * Create a new message instance.
     *
     * use Illuminate\Support\Facades\Mail; Mail::to("miura@moto.qee.jp")->send(new App\Mail\Submitted("nofile.png"));
     *
     */
    public function __construct($_bb, $_bbmes)
    {
        $names = [1 => "査読議論", 2 => "メタと著者の", 3 => "出版担当と著者の"];
        $nameofmeta = Setting::getval('NAME_OF_META');
        if ($nameofmeta != null){
            $names[2] = $nameofmeta."と著者の";
        }
        $this->bb = $_bb;
        $this->bbmes = $_bbmes;
        $this->paper = $_bb->paper;
        $this->mail_to_cc = $_bb->get_mail_to_cc();
        $this->name = $names[$_bb->type];

        $this->subject = $this->name . '掲示板に投稿がありました : ' . $this->paper->id_03d();
        $this->content = new Content(
            markdown: 'emails.bbnotify',
            with: [
                'bbsub' => $this->bbmes->subject,
                'mes' => $this->bbmes->mes,
                'bburl' => $this->bb->url(),
                'name' => $this->name,
                'pid03d' => $this->paper->id_03d(),
            ],
        );
    }
    /** 
     * メール送信 （独自実装にする必要はないが、一応Cc: を除いている）
     */
    public function process_send()
    {
        if (!isset($this->mail_to_cc['to'])) {
            // 正常ならここは実行されないので、めったにないはずだが、もしToが抜けていたら、bccだった宛先に個別に送る必要がある
            if (count($this->mail_to_cc['separate_to']) == 0) {
                return;
            }
            // ループを回しても、なぜか個別に送れない(Mail:to を繰り返すと、toが追加されてしまう。)
            // ので、とりあえずbcc にする。
            $pmail = Mail::bcc($this->mail_to_cc['separate_to']);
            $pmail->to(Setting::getval("MAILFROM"));
            $pmail->queue($this);
            return;
        } else {
            // authorがいれば、to: author
            // そうでなければ、to: metareviewer (事前議論)
            // それ以外は Bcc でおくる
            $pmail = Mail::to($this->mail_to_cc['to']);
            $pmail->bcc($this->mail_to_cc['bcc']);
            $pmail->queue($this);
        }
    }

}
