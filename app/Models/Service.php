<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'artisan_id',
        'category',
        'name',
        'description',
        'base_price',
        'duration_estimate',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'duration_estimate' => 'integer',
    ];

    public function artisan(): BelongsTo
    {
        return $this->belongsTo(Artisan::class);
    }
}
