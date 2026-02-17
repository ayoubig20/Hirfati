<?php

namespace App\Services;

use App\Models\Artisan;
use App\Models\Review;
use App\Models\TrustCache;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TrustCalculatorService
{
    private float $decayHalfLife;
    private float $updateSmoothing;
    private float $penaltySteepness;
    private float $penaltyTolerance;
    private int $cacheTtl;

    public function __construct()
    {
        $this->decayHalfLife = (float) config('trust.decay_half_life', 30);
        $this->updateSmoothing = (float) config('trust.update_smoothing', 0.30);
        $this->penaltySteepness = (float) config('trust.penalty_steepness', 10.0);
        $this->penaltyTolerance = (float) config('trust.penalty_tolerance', 0.15);
        $this->cacheTtl = (int) config('trust.cache_ttl', 86400);
    }

    /**
     * Calculate exponential decay weight for a review based on age.
     * w(Δt) = exp(-λ * Δt) where λ = ln(2) / t_half (Equation 3.3)
     */
    public function calculateTemporalWeight(int $ageDays): float
    {
        $lambda = log(2) / $this->decayHalfLife;

        return exp(-$lambda * $ageDays);
    }

    /**
     * Compute weighted average of ratings with temporal decay.
     * S_r = Σ(w_j * r_j) / Σ(w_j) (Equation 3.5)
     */
    public function computeRatingComponent(Collection $reviews): float
    {
        if ($reviews->isEmpty()) {
            return 0.5;
        }

        $weightedSum = 0.0;
        $weightSum = 0.0;

        foreach ($reviews as $review) {
            $ageDays = $this->getDaysSince($review->created_at);
            $weight = $this->calculateTemporalWeight($ageDays);
            $normalizedRating = ($review->rating - 1) / 4.0; // [1,5] → [0,1]

            $weightedSum += $weight * $normalizedRating;
            $weightSum += $weight;
        }

        return $weightSum > 0 ? $weightedSum / $weightSum : 0.5;
    }

    /**
     * Calculate completion rate.
     * S_c = completed_orders / accepted_orders (Equation 3.6)
     */
    public function computeCompletionComponent(int $completed, int $accepted): float
    {
        if ($accepted === 0) {
            return 0.5;
        }

        return $completed / $accepted;
    }

    /**
     * Sigmoid penalty for rating-completion discrepancy.
     * Δ = |S_r - S_c|
     * P = 1 / (1 + exp(-κ(Δ - δ))) (Equations 3.7-3.8)
     */
    public function computePenalty(float $ratingComp, float $completionComp): float
    {
        $delta = abs($ratingComp - $completionComp);
        $exponent = -$this->penaltySteepness * ($delta - $this->penaltyTolerance);

        return 1.0 / (1.0 + exp($exponent));
    }

    /**
     * Update trust score using exponential smoothing.
     * T_new = (1-λ)T_prev + λ(S_r - P) (Equation 3.10)
     *
     * S_c influences ONLY through penalty P, NOT directly averaged.
     */
    public function updateTrustScore(int $artisanId): array
    {
        $artisan = Artisan::findOrFail($artisanId);

        $reviews = Review::where('artisan_id', $artisanId)
            ->where('status', 'published')
            ->orderBy('created_at', 'desc')
            ->get();

        $orders = DB::table('orders')
            ->where('artisan_id', $artisanId)
            ->selectRaw("
                COUNT(*) as accepted,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
            ")
            ->first();

        $accepted = (int) ($orders->accepted ?? 0);
        $completed = (int) ($orders->completed ?? 0);

        $S_r = $this->computeRatingComponent($reviews);
        $S_c = $this->computeCompletionComponent($completed, $accepted);
        $P = $this->computePenalty($S_r, $S_c);

        $T_prev = (float) $artisan->trust_score;
        $adjustedScore = $S_r - $P;
        $T_new = (1 - $this->updateSmoothing) * $T_prev
               + $this->updateSmoothing * $adjustedScore;

        $T_new = max(0.0, min(1.0, $T_new));

        $artisan->update(['trust_score' => $T_new]);

        // Update avg_rating and jobs_completed
        $avgRating = $reviews->isEmpty() ? 0 : $reviews->avg('rating');
        $artisan->update([
            'avg_rating' => round($avgRating, 2),
            'jobs_completed' => $completed,
        ]);

        TrustCache::updateOrCreate(
            ['artisan_id' => $artisanId],
            [
                'trust_score' => $T_new,
                'rating_component' => $S_r,
                'completion_component' => $S_c,
                'penalty' => $P,
                'review_count' => $reviews->count(),
                'order_count' => $accepted,
            ]
        );

        // Invalidate cache
        Cache::forget("trust_score_{$artisanId}");

        return [
            'T_new' => $T_new,
            'S_r' => $S_r,
            'S_c' => $S_c,
            'P' => $P,
            'T_prev' => $T_prev,
        ];
    }

    /**
     * Get cached trust score or recalculate.
     */
    public function getCachedTrustScore(int $artisanId): float
    {
        return Cache::remember(
            "trust_score_{$artisanId}",
            $this->cacheTtl,
            function () use ($artisanId) {
                $cache = TrustCache::where('artisan_id', $artisanId)->first();
                if ($cache) {
                    return (float) $cache->trust_score;
                }
                $result = $this->updateTrustScore($artisanId);

                return $result['T_new'];
            }
        );
    }

    /**
     * Rank artisans by trust-aware score.
     * Score(s) = T_dyn * log(1 + N_c) * η(s) (Equation 3.12)
     */
    public function rankArtisans(string $category, string $location, int $limit = 10): Collection
    {
        return Artisan::where('service_category', $category)
            ->where('location', $location)
            ->where('status', 'active')
            ->selectRaw('artisans.*, trust_score * LOG(1 + jobs_completed) as ranking_score')
            ->orderByDesc('ranking_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Batch recalculate trust scores for all active artisans.
     */
    public function batchRecalculate(): array
    {
        $artisans = Artisan::where('status', 'active')->pluck('id');
        $results = [];

        foreach ($artisans as $artisanId) {
            $results[$artisanId] = $this->updateTrustScore($artisanId);
        }

        return $results;
    }

    private function getDaysSince($date): int
    {
        return (int) Carbon::parse($date)->diffInDays(Carbon::now());
    }
}
