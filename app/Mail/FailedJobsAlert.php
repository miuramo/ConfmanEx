<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FailedJobsAlert extends RetryMailable
{

    public int $count;

    /**
     * Create a new message instance.
     */
    public function __construct(int $count)
    {
        $this->count = $count;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【警告】'.env('APP_NAME').'で失敗したジョブがあります',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.failed_jobs_alert',
            with: [
                'count' => $this->count,
            ],
        );
    }
}
