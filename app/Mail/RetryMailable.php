<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RetryMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $mail_to_cc;

    public $tries = 10;
    public $backoff = 10;
    public $timeout = 60;

    public bool $failed = false;
    public $errormessage = "";

    public $subject;
    public Content $content;

    /**
     * Create a new message instance.
     *
     */
    public function __construct()
    {
    }
    /**
     * メール送信
     */
    public function process_send(){
        $pmail = Mail::to($this->mail_to_cc['to']);
        if (count($this->mail_to_cc['cc']) > 0) $pmail->cc($this->mail_to_cc['cc']);
        if (isset($this->mail_to_cc['bcc']) && count($this->mail_to_cc['bcc']) > 0) $pmail->bcc($this->mail_to_cc['bcc']);
        info($this->mail_to_cc);
        $pmail->queue($this);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->failed){
            return new Envelope(
                subject: "★★メール送信失敗の可能性★★ ".$this->subject . " ".$this->errormessage,
            );
        }
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function convertImageToDataURI($filePath)
    {
        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType = mime_content_type($filePath);
        return "data:{$mimeType};base64,{$imageData}";
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return $this->content;
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
        // ローカルログ（必ず残す）
        Log::error('メール送信に最終失敗しました: ' . $exception->getMessage(), [
            'to' => $this->mail_to_cc['to'] ?? null,
            'cc' => $this->mail_to_cc['cc'] ?? [],
            'subject' => $this->subject,
            'tries' => $this->tries,
        ]);
        $this->failed = true;
        $this->errormessage = "\n\n".$exception->getMessage();

        // Mail::to(env("MAIL_BCC_ADDRESS", null))->queue($this);
    }
}
