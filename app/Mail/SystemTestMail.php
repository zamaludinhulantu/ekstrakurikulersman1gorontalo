<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SystemTestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $messageText,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Uji Konfigurasi Email Sistem',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.system-test',
            with: [
                'messageText' => $this->messageText,
            ],
        );
    }
}
