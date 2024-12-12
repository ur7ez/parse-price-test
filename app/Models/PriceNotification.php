<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceNotification extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = ['subscriber_id', 'notification_content', 'queued_at', 'message_id', 'sent_at'];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
