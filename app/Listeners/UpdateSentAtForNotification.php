<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class UpdateSentAtForNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Update `price_notifications` sent_at timestamp by its `message_id`
     */
    public function handle(MessageSent $event): void
    {
        $messageId = $event->message->getId();

        if ($messageId) {
            DB::table('price_notifications')
                ->where('message_id', $messageId)
                ->update(['sent_at' => now()]);
        }
    }
}
