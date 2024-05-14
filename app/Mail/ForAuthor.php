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

class ForAuthor extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public MailTemplate $template;
    public array $replacetxt;
    public array $mail_to_cc;

    /**
     * Create a new message instance.
     */
    public function __construct(Paper|User $_paper, MailTemplate $_temp)
    {
        $this->template = $_temp;
        $this->replacetxt = $_temp->getreplacetxt($_paper);
        $this->mail_to_cc = $_paper->get_mail_to_cc();
    }

    /**
     * メール送信
     */
    public function process_send()
    {
        $pmail = Mail::to($this->mail_to_cc['to']);
        $pmail->cc($this->mail_to_cc['cc']);
        $pmail->send($this);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template->make_subject($this->replacetxt),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.forauthor',
            with: [
                'body' => $this->template->make_body($this->replacetxt),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
