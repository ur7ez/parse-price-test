<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class StoreMessageIdForNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Track the message_id for queued mail (before it's sent) and update `price_notifications` table for subscriber
     */
    public function handle(MessageSending $event): void
    {
        $messageId = $event->message->getId();
        $recipientEmail = $event->message->getTo() ? array_key_first($event->message->getTo()) : null;

        if ($messageId && $recipientEmail) {
            // Update the price_notifications table for subscriber
            DB::table('price_notifications')
                ->where('subscriber_id', function ($query) use ($recipientEmail) {
                    $query->select('id')
                        ->from('subscribers')
                        ->where('email', $recipientEmail);
                })
                ->whereNull('sent_at')
                ->latest('queued_at')
                ->update(['message_id' => $messageId]);
        }
    }
}
