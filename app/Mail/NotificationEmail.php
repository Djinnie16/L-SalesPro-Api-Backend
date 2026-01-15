<?php

namespace App\Mail;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Notification $notification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'LeysCo SalesPro: ' . $this->notification->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notification,
                'appName' => config('app.name', 'LeysCo SalesPro'),
                'appUrl' => config('app.url', 'http://localhost'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}