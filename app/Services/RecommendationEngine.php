<?php

namespace App\Services;

use App\Models\Artisan;
use Illuminate\Support\Collection;

class RecommendationEngine
{
    private TrustCalculatorService $trustCalculator;

    public function __construct(TrustCalculatorService $trustCalculator)
    {
        $this->trustCalculator = $trustCalculator;
    }

    /**
     * Search artisans with trust-aware ranking.
     */
    public function search(
        ?string $category = null,
        ?string $location = null,
        ?float $minRating = null,
        ?float $maxPrice = null,
        string $sortBy = 'trust_score',
        int $perPage = 20
    ) {
        $query = Artisan::active();

        if ($category) {
            $query->byCategory($category);
        }

        if ($location) {
            $query->byLocation($location);
        }

        if ($minRating !== null) {
            $query->where('avg_rating', '>=', $minRating);
        }

        if ($maxPrice !== null) {
            $query->where('hourly_rate', '<=', $maxPrice);
        }

        switch ($sortBy) {
            case 'ranking':
                $query->selectRaw('artisans.*, trust_score * LOG(1 + jobs_completed) as ranking_score')
                      ->orderByDesc('ranking_score');
                break;
            case 'rating':
                $query->orderByDesc('avg_rating');
                break;
            case 'price_asc':
                $query->orderBy('hourly_rate');
                break;
            case 'price_desc':
                $query->orderByDesc('hourly_rate');
                break;
            case 'trust_score':
            default:
                $query->orderByDesc('trust_score');
                break;
        }

        return $query->paginate($perPage);
    }

    /**
     * Get featured artisans (high trust scores).
     */
    public function getFeatured(int $limit = 6): Collection
    {
        return Artisan::active()
            ->where('trust_score', '>=', config('trust.trust_tiers.silver'))
            ->where('jobs_completed', '>=', 5)
            ->orderByDesc('trust_score')
            ->limit($limit)
            ->get();
    }

    /**
     * Get available service categories with artisan counts.
     */
    public function getCategories(): Collection
    {
        return Artisan::active()
            ->selectRaw('service_category, COUNT(*) as artisan_count, AVG(trust_score) as avg_trust')
            ->groupBy('service_category')
            ->orderByDesc('artisan_count')
            ->get();
    }

    /**
     * Get fraud alerts (artisans with high penalty scores).
     */
    public function getFraudAlerts(float $threshold = 0.20): Collection
    {
        return Artisan::join('trust_cache', 'artisans.id', '=', 'trust_cache.artisan_id')
            ->where('trust_cache.penalty', '>=', $threshold)
            ->select('artisans.*', 'trust_cache.penalty', 'trust_cache.rating_component', 'trust_cache.completion_component')
            ->orderByDesc('trust_cache.penalty')
            ->get();
    }
}
