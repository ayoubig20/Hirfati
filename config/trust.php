<?php

/*
 | Trust Algorithm Hyperparameters
 |
 | decay_half_life    : Days after which a review's weight is halved (Eq. 3.3)
 | update_smoothing   : λ controls how fast trust responds to new data (Eq. 3.10)
 | penalty_steepness  : κ controls sigmoid sharpness of fraud penalty (Eq. 3.8)
 | penalty_tolerance  : δ is the discrepancy threshold before penalty activates (Eq. 3.7)
 | cache_ttl          : Seconds to cache trust scores in Redis (default: 24h)
 */

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
