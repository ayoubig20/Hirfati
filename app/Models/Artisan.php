<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Artisan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'service_category',
        'specialty',
        'location',
        'hourly_rate',
        'avg_rating',
        'jobs_completed',
        'trust_score',
        'status',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'avg_rating' => 'decimal:2',
        'trust_score' => 'decimal:4',
        'jobs_completed' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function trustCache(): HasOne
    {
        return $this->hasOne(TrustCache::class);
    }

    public function getTrustTierAttribute(): string
    {
        $tiers = config('trust.trust_tiers');
        if ($this->trust_score >= $tiers['gold']) {
            return 'gold';
        }
        if ($this->trust_score >= $tiers['silver']) {
            return 'silver';
        }
        if ($this->trust_score >= $tiers['bronze']) {
            return 'bronze';
        }

        return 'none';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('service_category', $category);
    }

    public function scopeByLocation($query, string $location)
    {
        return $query->where('location', $location);
    }
}
