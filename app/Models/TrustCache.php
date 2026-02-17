<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrustCache extends Model
{
    protected $table = 'trust_cache';

    protected $fillable = [
        'artisan_id',
        'trust_score',
        'rating_component',
        'completion_component',
        'penalty',
        'review_count',
        'order_count',
    ];

    protected $casts = [
        'trust_score' => 'decimal:4',
        'rating_component' => 'decimal:4',
        'completion_component' => 'decimal:4',
        'penalty' => 'decimal:4',
        'review_count' => 'integer',
        'order_count' => 'integer',
    ];

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }
}
