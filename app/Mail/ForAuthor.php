<?php

namespace App\Mail;

use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ForAuthor extends RetryMailable
{

    public MailTemplate $template;
    public array $replacetxt;

    /**
     * Create a new message instance.
     */
    public function __construct(Paper|User $_paper, MailTemplate $_temp)
    {
        $this->template = $_temp;
        $this->replacetxt = $_temp->getreplacetxt($_paper);
        $this->mail_to_cc = $_paper->get_mail_to_cc();
        if (strlen($_temp->cc) > 0) {
            $ary = explode(",", $_temp->cc);
            foreach ($ary as $em) {
                // if ($em == $this->mail_to_cc['to']) continue;
                $this->mail_to_cc['cc'][] = trim($em);
            }
        }
        if (strlen($_temp->bcc) > 0) {
            $ary = explode(",", $_temp->bcc);
            foreach ($ary as $em) {
                // if ($em == $this->mail_to_cc['to']) continue;
                $this->mail_to_cc['bcc'][] = trim($em);
            }
        }
        $backup_bcc = env("MAIL_BCC_ADDRESS", null);
        if ($backup_bcc != null) {
            $this->mail_to_cc['bcc'][] = $backup_bcc;
        }
        $this->subject = $this->template->make_subject($this->replacetxt);
        $this->content = new Content(
            markdown: 'emails.forauthor',
            with: [
                'body' => $this->template->make_body($this->replacetxt),
            ],
        );
    }

}
