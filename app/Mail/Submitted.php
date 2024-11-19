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
use Illuminate\Support\Facades\Mail;

class Submitted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Paper $paper;
    public array $mail_to_cc;

    public $tries = 5;
    public $backoff = 10;
    public $timeout = 60;

    /**
     * Create a new message instance.
     *
     * use Illuminate\Support\Facades\Mail; Mail::to("miura@moto.qee.jp")->send(new App\Mail\Submitted("nofile.png"));
     *
     */
    public function __construct($_paper)
    {
        $this->paper = $_paper;
        $this->mail_to_cc = $_paper->get_mail_to_cc();
        //TODO: paperの情報をつかって書き込む
        // $this->imagePath = $imagePath;
    }
    /**
     * メール送信
     */
    public function process_send(){
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
            subject: '投稿完了通知メール PaperID : '.$this->paper->id_03d(),
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
        $pdffile = File::find($this->paper->pdf_file_id);
        $owner = User::find($this->paper->owner);
        $imagePath = $pdffile->getPdfHeadPath();
        while (!file_exists($imagePath)) {
            sleep(2);
        }
        // $imagePath = storage_path('app/public/files/nofile.png'); //public_path('files/nofile.png');
        // $imageDataURI = $this->convertImageToDataURI($imagePath);
        return new Content(
            markdown: 'emails.submitted',
            with: [
                // 'datauri' => $imageDataURI,
                // 'imagePath' => $imagePath,
                'title' => $this->paper->title,
                'paperid' => $this->paper->id_03d(),
                'owner' => $owner,
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
        $pdffile = File::find($this->paper->pdf_file_id);
        $imagePath = $pdffile->getPdfHeadPath();
        return [
            Attachment::fromPath($imagePath)->as("titleimage.png"),
            // ->withMime('image/png'),
        ];
    }
}
