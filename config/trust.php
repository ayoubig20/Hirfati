<?php

return [
    'decay_half_life' => env('TRUST_DECAY_HALF_LIFE', 30),
    'update_smoothing' => env('TRUST_UPDATE_SMOOTHING', 0.30),
    'penalty_steepness' => env('TRUST_PENALTY_STEEPNESS', 10.0),
    'penalty_tolerance' => env('TRUST_PENALTY_TOLERANCE', 0.15),
    'cache_ttl' => env('TRUST_CACHE_TTL', 86400),

    'trust_tiers' => [
        'gold' => 0.85,
        'silver' => 0.70,
        'bronze' => 0.50,
    ],
];
