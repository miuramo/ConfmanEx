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

class RegistrationConfirmation extends RetryMailable
{

    public Regist $regist;
    public $conftitle;

    /**
     * Create a new message instance.
     */
    public function __construct(Regist $regist)
    {
        $this->regist = $regist;
        $this->conftitle = Setting::getval('CONFTITLE');
        $this->subject = "【{$this->conftitle}】参加登録が完了しました";
        $this->content = new Content(
            markdown: 'emails.registration.confirmation',
            with: [
                'regist' => $this->regist,
                'conftitle' => $this->conftitle,
            ],
        );
    }
}
