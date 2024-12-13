<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class PriceChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public array $priceChanges, public string $subscriberLogin, public int $notificationId)
    {

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OLX Price Changed',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.price_changed',
            with: [
                'priceChanges' => $this->priceChanges,
                'subscriberLogin' => $this->subscriberLogin,
                'notification_id' => $this->notificationId, // Pass the notification ID
            ]
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

    /**
     * @return Headers
     */
    public function headers(): Headers
    {
        $customMessageId = Str::uuid() . '@' . config('mail.domain', 'example.com');
        return new Headers(
            messageId: $customMessageId,
            // text: ['X-Custom-Message-ID' => $customMessageId],
        );
    }
}
