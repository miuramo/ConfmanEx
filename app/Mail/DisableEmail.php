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

class DisableEmail extends Mailable
{
    use Queueable, SerializesModels;

    public Paper $paper;
    public string $invalid_email;

    public $tries = 5;
    public $backoff = 10;
    public $timeout = 60;

    
    /**
     * Create a new message instance.
     *
     * use Illuminate\Support\Facades\Mail; Mail::to("miura@moto.qee.jp")->send(new App\Mail\Submitted("nofile.png"));
     *
     */
    public function __construct($_paper, $_em)
    {
        $this->paper = $_paper;
        $this->invalid_email = $_em;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '投稿連絡用メールアドレスの自動削除 PaperID : ' . $this->paper->id_03d(),
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
        if ($pdffile == null) {
            $imagePath = storage_path(File::apf() .'/nofile.png'); //public_path('files/nofile.png');
            $imageDataURI = $this->convertImageToDataURI($imagePath);
        } else {
            $imagePath = $pdffile->getPdfHeadPath();
            while (!file_exists($imagePath)) {
                sleep(2);
            }
            $imageDataURI = $this->convertImageToDataURI($imagePath);
        }
        return new Content(
            markdown: 'emails.disableemail',
            with: [
                'datauri' => $imageDataURI,
                'title' => $this->paper->title,
                'owner' => $owner,
                'invalid_email' => $this->invalid_email,
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
        return [
            // Attachment::fromPath(storage_path('app/public/files/nofile.png')),
        ];
    }
}
