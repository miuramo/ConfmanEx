<?php

namespace App\Jobs;

use App\Mail\RetryMailable;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;

class SendMailJob implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue;

    public $tries = 10;
    public $backoff = 10;
    public $timeout = 60;

    protected RetryMailable $mailable;

    public function __construct(RetryMailable $mailable)
    {
        $this->mailable = $mailable;
    }

    public function handle()
    {
        try {
            $pmail = Mail::to($this->mailable->mail_to_cc['to']);
            if (count($this->mailable->mail_to_cc['cc']) > 0) $pmail->cc($this->mailable->mail_to_cc['cc']);
            if (isset($this->mailable->mail_to_cc['bcc']) && count($this->mailable->mail_to_cc['bcc']) > 0) $pmail->bcc($this->mailable->mail_to_cc['bcc']);

            $pmail->sendNow($this->mailable);
        } catch (UnexpectedResponseException $e){
            // info("UnexpectedResponseException caught in SendMailJob: " . $e->getMessage());
            throw $e;
        } catch (TransportExceptionInterface $e) {
            if ($this->attempts() < $this->tries) {
                throw $e;
            } else {
                info("TransportExceptionInterface final failure in SendMailJob: " . $e->getMessage());
                $this->mailable->failed = true;
                $this->mailable->errormessage = $e->getMessage();
            }
        }
    }

    public function failed(\Throwable $e)
    {
        // tries を使い切った後
        logger()->error('Mail finally failed', [
            'message' => $e->getMessage(),
        ]);
    }
}
