<?php

namespace App\Mail;

use App\Models\File;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DisableEmail extends RetryMailable
{

    public Paper $paper;

    public string $invalid_email;

    /**
     * Create a new message instance.
     *
     * use Illuminate\Support\Facades\Mail; Mail::to("miura@moto.qee.jp")->send(new App\Mail\Submitted("nofile.png"));
     *
     */
    public function __construct($_paper, $_em)
    {
        $this->paper = $_paper;
        $this->mail_to_cc = $_paper->get_mail_to_cc();
        $this->invalid_email = $_em;
        $this->subject = '投稿連絡用メールアドレスの自動削除 PaperID : ' . $this->paper->id_03d();
        $owner = User::find($this->paper->owner);
        $this->content = new Content(
            markdown: 'emails.disableemail',
            with: [
                'title' => $this->paper->title,
                'owner' => $owner,
                'invalid_email' => $this->invalid_email,
            ],
        );
    }
}
