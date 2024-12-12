<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = ['email', 'verification_token', 'verified_at'];

    public function urlPrices(): BelongsToMany
    {
        return $this->belongsToMany(UrlPrice::class, 'subscriptions');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function priceNotifications(): HasMany
    {
        return $this->hasMany(PriceNotification::class);
    }

    public function getEmailLogin(): string
    {
        return explode('@', $this->email)[0];
    }
}
