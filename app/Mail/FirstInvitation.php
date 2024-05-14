<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

// class FirstInvitation extends Mailable implements ShouldQueue
// {
//     use Queueable, SerializesModels;

//     protected string $invto;
//     /**
//      * Create a new message instance.
//      */
//     public function __construct(string $em)
//     {
//         $this->invto = $em;
//     }

//     public function build()
//     {
//         return $this->markdown('mail.first_invitation')->with("to",$this->invto);
//     }

//     /**
//      * Get the message envelope.
//      */
//     public function envelope(): Envelope
//     {
//         return new Envelope(
//             subject: "メールアドレスを認証してください",
//         );
//     }

//     /**
//      * Get the message content definition.
//      */
//     public function content(): Content
//     {
//         return new Content(
//             view: 'mail.first_invitation',
//         );
//     }

//     /**
//      * Get the attachments for the message.
//      *
//      * @return array<int, \Illuminate\Mail\Mailables\Attachment>
//      */
//     public function attachments(): array
//     {
//         return [];
//     }
// }
//
