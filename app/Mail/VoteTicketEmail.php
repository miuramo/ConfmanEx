<?php

namespace App\Mail;

use App\Models\File;
use App\Models\Paper;
use App\Models\User;
use App\Models\Vote;
use App\Models\VoteTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VoteTicketEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $mail_to_cc;
    public VoteTicket $voteTicket;
    public string $conftitle;

    public $tries = 5;
    public $backoff = 10;
    public $timeout = 60;

    public bool $failed = false;

    /**
     * Create a new message instance.
     *
     * use Illuminate\Support\Facades\Mail; Mail::to("miura@moto.qee.jp")->send(new App\Mail\Submitted("nofile.png"));
     *
     */
    public function __construct(VoteTicket $vT)
    {
        $this->voteTicket = $vT;
        $this->mail_to_cc = ['to' => $vT->email];
        $this->conftitle = \App\Models\Setting::getval('CONFTITLE');
        $this->subject = "【{$this->conftitle}】". ' 投票URLのお知らせ';
    }
    /**
     * メール送信
     */
    public function process_send()
    {
        $pmail = Mail::to($this->mail_to_cc['to']);
        if (isset($this->mail_to_cc['cc'])) {
            $pmail->cc($this->mail_to_cc['cc']);
        }
        $pmail->queue($this);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->failed) {
            return new Envelope(
                subject: "★★メール送信失敗？★★ " . $this->subject,
            );
        }
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vote_ticket',
            with: [
                'ticket' => $this->voteTicket,
                'conftitle' => $this->conftitle,
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

    public function failed(\Exception $exception)
    {
        info('Submitted:メール送信に失敗しました: ' . $exception->getMessage());
        $this->failed = true;
        Mail::to(env("MAIL_BCC_ADDRESS", null))->queue($this);
    }
}
