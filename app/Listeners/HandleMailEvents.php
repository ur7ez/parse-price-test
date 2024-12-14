<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use App\Models\PriceNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleMailEvents implements ShouldQueue
{
    protected ?string $messageId;
    protected ?int $notificationId;
    protected array $recipientEmails;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event. Works for queued and non-queued Mailable
     */
    public function handle(MessageSending|MessageSent $event): void
    {
        $this->notificationId = $event->data['notification_id'] ?? null;
        $this->recipientEmails = collect($event->message->getTo())
            ->map(fn($address) => $address->getAddress())
            ->all();

        // Skip processing if this is not a price notification email
        if (!$this->notificationId) {
            logger()->info('Skipping non-notification email', [
                'to' => $this->recipientEmails,
            ]);
            return;
        }

        // Retrieve the custom message ID from the headers
        $headers = $event->message->getHeaders();
        $this->messageId = $headers->get('Message-ID')?->getBodyAsString();

        if (!$this->messageId) {
            logger()->warning($event::class . ' event missing custom Message-ID', [
                'to' => $this->recipientEmails,
                'headers' => $headers->toString(),
            ]);
            return;
        }

        /*logger()->info($event::class . ' event triggered', [
            'custom_message_id' => $this->messageId,
            'to' => $this->recipientEmails,
        ]);*/

        if ($event instanceof MessageSending) {
            $this->handleMessageSending();
        } else {
            $this->handleMessageSent();
        }
    }

    protected function handleMessageSending(): void
    {
        PriceNotification::updateMessageIdForSubscriber($this->notificationId, $this->messageId);
    }

    protected function handleMessageSent(): void
    {
        PriceNotification::updateSentAt($this->notificationId);
    }
}
