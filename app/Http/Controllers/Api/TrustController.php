<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TrustCache;
use App\Services\RecommendationEngine;
use App\Services\TrustCalculatorService;
use Illuminate\Http\JsonResponse;

class TrustController extends Controller
{
    public function __construct(
        private TrustCalculatorService $trustCalculator,
        private RecommendationEngine $recommendation,
    ) {}

    public function calculate(int $artisanId): JsonResponse
    {
        $result = $this->trustCalculator->updateTrustScore($artisanId);

        return response()->json([
            'artisan_id' => $artisanId,
            'trust_score' => $result['T_new'],
            'rating_component' => $result['S_r'],
            'completion_component' => $result['S_c'],
            'penalty' => $result['P'],
            'previous_score' => $result['T_prev'],
        ]);
    }

    public function stats(int $artisanId): JsonResponse
    {
        $cache = TrustCache::where('artisan_id', $artisanId)->first();

        if (! $cache) {
            $result = $this->trustCalculator->updateTrustScore($artisanId);
            $cache = TrustCache::where('artisan_id', $artisanId)->first();
        }

        $tiers = config('trust.trust_tiers');
        $trustScore = (float) $cache->trust_score;

        $tier = 'none';
        if ($trustScore >= $tiers['gold']) {
            $tier = 'gold';
        } elseif ($trustScore >= $tiers['silver']) {
            $tier = 'silver';
        } elseif ($trustScore >= $tiers['bronze']) {
            $tier = 'bronze';
        }

        return response()->json([
            'artisan_id' => $artisanId,
            'trust_score' => $trustScore,
            'rating_component' => (float) $cache->rating_component,
            'completion_component' => (float) $cache->completion_component,
            'penalty' => (float) $cache->penalty,
            'review_count' => $cache->review_count,
            'order_count' => $cache->order_count,
            'tier' => $tier,
            'last_calculated' => $cache->updated_at,
        ]);
    }

    public function fraudAlerts(): JsonResponse
    {
        $alerts = $this->recommendation->getFraudAlerts();

        return response()->json($alerts);
    }
}
