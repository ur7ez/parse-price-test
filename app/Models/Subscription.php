<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;
    protected $fillable = [
        'url_price_id',
        'subscriber_id',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function urlPrice(): BelongsTo
    {
        return $this->belongsTo(UrlPrice::class);
    }
}
