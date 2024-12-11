<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UrlPrice extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'is_valid', 'price', 'ad_data', 'parsed_at',];

    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'subscriptions');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Scope to filter valid URLs for parsing.
     * From `url_prices` table select only records where either:
        (a) `is_valid` is true AND `parsed_at` field has a time older than some threshold, OR
        (b) `parsed_at` field is null (add never parsed urls).
        Then do inner join on `subscribers` table (via M:M relation with `subscriptions` table), and select only records that have verified subscribers (i.e. with `subscribers`.`verified_at` not null).
     * @param $query
     * @return mixed
     */
    public function scopeValidUrlsOnly($query)
    {
        $maxAge = config('parser.max_url_age', 2); // Default to 2 hours if not set
        $thresholdTime = Carbon::now()->subHours($maxAge);

        return $query
            ->where(function ($q) use ($thresholdTime) {
                $q->where('is_valid', true)
                    ->where('parsed_at', '<', $thresholdTime)
                    ->orWhereNull('parsed_at');
            })
            ->whereHas('subscribers', function ($q) {
                $q->whereNotNull('verified_at');
            });
    }

    public function history(): HasMany
    {
        return $this->hasMany(UrlPricesHistory::class);
    }
}
