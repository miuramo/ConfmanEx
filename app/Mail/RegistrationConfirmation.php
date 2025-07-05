<?php

namespace App\Mail;

use App\Models\Regist;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Regist $regist;
    public $conftitle;

    /**
     * Create a new message instance.
     */
    public function __construct(Regist $regist)
    {
        $this->regist = $regist;
        $this->conftitle = Setting::getval('CONFTITLE');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "【{$this->conftitle}】参加登録が完了しました",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.registration.confirmation',
            with: [
                'regist' => $this->regist,
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
}
