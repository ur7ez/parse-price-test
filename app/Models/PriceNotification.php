<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceNotification extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $fillable = ['subscriber_id', 'data', 'queued_at', 'message_id', 'sent_at'];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /**
     * Update the `message_id` and `queued_at` for the price notification for a subscriber.
     * @param int $notificationId
     * @param string $messageId Mailable transport message_id
     * @return void
     */
    public static function updateMessageIdForSubscriber(int $notificationId, string $messageId): void
    {
        static::where('id', $notificationId)
            ->update(['message_id' => $messageId, 'queued_at' => now()]);
    }

    /**
     * Update the `sent_at` timestamp for a specific `message_id`.
     */
    public static function updateSentAt(int $notificationId): void
    {
        static::where('id', $notificationId)->update(['sent_at' => now()]);
    }
}
