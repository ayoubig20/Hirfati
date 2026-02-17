<?php

namespace Tests\Unit;

use App\Models\Artisan;
use App\Models\Order;
use App\Models\Review;
use App\Models\TrustCache;
use App\Models\User;
use App\Services\TrustCalculatorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class TrustCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    private TrustCalculatorService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure config values are set to known defaults for predictable tests
        config([
            'trust.decay_half_life' => 30,
            'trust.update_smoothing' => 0.30,
            'trust.penalty_steepness' => 10.0,
            'trust.penalty_tolerance' => 0.15,
            'trust.cache_ttl' => 86400,
        ]);

        $this->service = app(TrustCalculatorService::class);
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /**
     * Create an artisan with associated User for FK constraints.
     */
    private function createArtisan(array $overrides = []): Artisan
    {
        $user = User::factory()->create();

        return Artisan::create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Artisan',
            'email' => 'artisan_' . uniqid() . '@example.com',
            'phone' => '0600000000',
            'service_category' => 'plumbing',
            'specialty' => 'General plumbing',
            'location' => 'Casablanca',
            'hourly_rate' => 100.00,
            'avg_rating' => 0.00,
            'jobs_completed' => 0,
            'trust_score' => 0.5000,
            'status' => 'active',
        ], $overrides));
    }

    /**
     * Create a review for a given artisan, optionally setting created_at to simulate age.
     */
    private function createReview(Artisan $artisan, int $rating, int $ageDays = 0, string $status = 'published'): Review
    {
        $customer = User::factory()->create();

        $review = Review::create([
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'order_id' => null,
            'rating' => $rating,
            'comment' => 'Test review',
            'status' => $status,
        ]);

        // Manually set created_at after creation since it is not mass-assignable.
        // Using a raw DB update avoids Eloquent's automatic timestamp management.
        if ($ageDays > 0) {
            $pastDate = Carbon::now()->subDays($ageDays);
            \Illuminate\Support\Facades\DB::table('reviews')
                ->where('id', $review->id)
                ->update(['created_at' => $pastDate, 'updated_at' => $pastDate]);
            $review->refresh();
        }

        return $review;
    }

    /**
     * Create an order for a given artisan.
     */
    private function createOrder(Artisan $artisan, string $status = 'completed'): Order
    {
        $customer = User::factory()->create();

        return Order::create([
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'service_id' => null,
            'status' => $status,
            'total_price' => 200.00,
            'scheduled_date' => Carbon::now()->subDays(5)->toDateString(),
            'completed_date' => $status === 'completed' ? Carbon::now()->toDateString() : null,
        ]);
    }

    // =========================================================================
    // Temporal Decay Tests
    // =========================================================================

    /** @test */
    public function temporal_weight_at_zero_days_is_one(): void
    {
        $weight = $this->service->calculateTemporalWeight(0);

        $this->assertEqualsWithDelta(1.0, $weight, 0.001);
    }

    /** @test */
    public function temporal_weight_at_half_life_is_half(): void
    {
        // With half-life = 30, weight at 30 days should be 0.5
        $weight = $this->service->calculateTemporalWeight(30);

        $this->assertEqualsWithDelta(0.5, $weight, 0.001);
    }

    /** @test */
    public function temporal_weight_at_two_half_lives_is_quarter(): void
    {
        // At 60 days (2 * half-life), weight should be 0.25
        $weight = $this->service->calculateTemporalWeight(60);

        $this->assertEqualsWithDelta(0.25, $weight, 0.001);
    }

    /** @test */
    public function temporal_weight_decreases_monotonically(): void
    {
        $previous = 1.0;
        for ($day = 1; $day <= 120; $day++) {
            $weight = $this->service->calculateTemporalWeight($day);
            $this->assertLessThan($previous, $weight, "Weight at day {$day} should be less than day " . ($day - 1));
            $previous = $weight;
        }
    }

    /** @test */
    public function temporal_weight_is_always_positive(): void
    {
        // Even at very large ages, weight should remain positive
        $weight = $this->service->calculateTemporalWeight(365);
        $this->assertGreaterThan(0.0, $weight);

        $weight = $this->service->calculateTemporalWeight(3650);
        $this->assertGreaterThan(0.0, $weight);
    }

    /** @test */
    public function temporal_weight_respects_custom_half_life(): void
    {
        // Set a different half-life and verify behavior
        config(['trust.decay_half_life' => 60]);
        $customService = new TrustCalculatorService();

        // At 60 days with half-life=60, weight should be 0.5
        $weight = $customService->calculateTemporalWeight(60);
        $this->assertEqualsWithDelta(0.5, $weight, 0.001);

        // At 30 days with half-life=60, weight should be ~0.707 (sqrt(0.5))
        $weight = $customService->calculateTemporalWeight(30);
        $this->assertEqualsWithDelta(sqrt(0.5), $weight, 0.001);
    }

    // =========================================================================
    // Rating Component Tests
    // =========================================================================

    /** @test */
    public function rating_component_with_empty_reviews_returns_default(): void
    {
        $result = $this->service->computeRatingComponent(new Collection());

        $this->assertEqualsWithDelta(0.5, $result, 0.001);
    }

    /** @test */
    public function rating_component_with_single_max_rating_today(): void
    {
        $artisan = $this->createArtisan();
        $this->createReview($artisan, 5, 0); // rating=5, today

        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // rating=5 normalized: (5-1)/4 = 1.0, weight at day 0 = 1.0
        // weighted avg = 1.0 * 1.0 / 1.0 = 1.0
        $this->assertEqualsWithDelta(1.0, $result, 0.001);
    }

    /** @test */
    public function rating_component_with_single_min_rating_today(): void
    {
        $artisan = $this->createArtisan();
        $this->createReview($artisan, 1, 0); // rating=1, today

        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // rating=1 normalized: (1-1)/4 = 0.0
        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    /** @test */
    public function rating_component_with_mid_rating_today(): void
    {
        $artisan = $this->createArtisan();
        $this->createReview($artisan, 3, 0); // rating=3, today

        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // rating=3 normalized: (3-1)/4 = 0.5
        $this->assertEqualsWithDelta(0.5, $result, 0.001);
    }

    /** @test */
    public function rating_component_weights_recent_reviews_more(): void
    {
        $artisan = $this->createArtisan();

        // Recent review (today) with rating=5 (normalized=1.0, weight~1.0)
        $this->createReview($artisan, 5, 0);
        // Old review (60 days ago) with rating=1 (normalized=0.0, weight~0.25)
        $this->createReview($artisan, 1, 60);

        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // weighted avg = (1.0*1.0 + 0.25*0.0) / (1.0 + 0.25) = 1.0 / 1.25 = 0.80
        $this->assertEqualsWithDelta(0.80, $result, 0.02);
    }

    /** @test */
    public function rating_component_with_multiple_same_age_reviews(): void
    {
        $artisan = $this->createArtisan();

        // All reviews today: ratings 1, 3, 5 -> normalized 0.0, 0.5, 1.0
        $this->createReview($artisan, 1, 0);
        $this->createReview($artisan, 3, 0);
        $this->createReview($artisan, 5, 0);

        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // All weights ~1.0, so simple average: (0.0 + 0.5 + 1.0) / 3 = 0.5
        $this->assertEqualsWithDelta(0.5, $result, 0.01);
    }

    /** @test */
    public function rating_component_ignores_non_published_reviews(): void
    {
        $artisan = $this->createArtisan();

        // Published review (rating=5)
        $this->createReview($artisan, 5, 0, 'published');
        // Pending review (rating=1) - should be excluded when we query
        $this->createReview($artisan, 1, 0, 'pending');

        // Only fetch published reviews (as the service does in updateTrustScore)
        $reviews = Review::where('artisan_id', $artisan->id)->where('status', 'published')->get();
        $result = $this->service->computeRatingComponent($reviews);

        // Only the 5-star review matters: normalized = 1.0
        $this->assertEqualsWithDelta(1.0, $result, 0.001);
    }

    // =========================================================================
    // Completion Component Tests
    // =========================================================================

    /** @test */
    public function completion_with_zero_accepted_returns_default(): void
    {
        $result = $this->service->computeCompletionComponent(0, 0);

        $this->assertEqualsWithDelta(0.5, $result, 0.001);
    }

    /** @test */
    public function completion_half_completed(): void
    {
        $result = $this->service->computeCompletionComponent(5, 10);

        $this->assertEqualsWithDelta(0.5, $result, 0.001);
    }

    /** @test */
    public function completion_all_completed(): void
    {
        $result = $this->service->computeCompletionComponent(10, 10);

        $this->assertEqualsWithDelta(1.0, $result, 0.001);
    }

    /** @test */
    public function completion_none_completed(): void
    {
        $result = $this->service->computeCompletionComponent(0, 10);

        $this->assertEqualsWithDelta(0.0, $result, 0.001);
    }

    /** @test */
    public function completion_component_is_a_ratio(): void
    {
        // 7 out of 10 = 0.7
        $result = $this->service->computeCompletionComponent(7, 10);
        $this->assertEqualsWithDelta(0.7, $result, 0.001);

        // 3 out of 4 = 0.75
        $result = $this->service->computeCompletionComponent(3, 4);
        $this->assertEqualsWithDelta(0.75, $result, 0.001);
    }

    // =========================================================================
    // Penalty Computation Tests
    // =========================================================================

    /** @test */
    public function penalty_small_discrepancy_yields_low_penalty(): void
    {
        // rating=0.8, completion=0.75 -> delta=0.05 < tolerance(0.15)
        $penalty = $this->service->computePenalty(0.8, 0.75);

        // P = 1/(1+exp(-10*(0.05-0.15))) = 1/(1+exp(1.0)) ~ 0.269
        $this->assertLessThan(0.35, $penalty);
    }

    /** @test */
    public function penalty_zero_discrepancy_is_low(): void
    {
        // delta=0, well below tolerance
        $penalty = $this->service->computePenalty(0.8, 0.8);

        // P = 1/(1+exp(-10*(0-0.15))) = 1/(1+exp(1.5)) ~ 0.182
        $this->assertLessThan(0.25, $penalty);
    }

    /** @test */
    public function penalty_at_tolerance_is_half(): void
    {
        // When discrepancy exactly equals tolerance, sigmoid midpoint -> P = 0.5
        // delta = 0.15 = tolerance
        $penalty = $this->service->computePenalty(0.8, 0.65);

        // P = 1/(1+exp(-10*(0.15-0.15))) = 1/(1+exp(0)) = 0.5
        $this->assertEqualsWithDelta(0.5, $penalty, 0.001);
    }

    /** @test */
    public function penalty_large_discrepancy_yields_high_penalty(): void
    {
        // rating=0.9, completion=0.3 -> delta=0.6, way above tolerance
        $penalty = $this->service->computePenalty(0.9, 0.3);

        // P = 1/(1+exp(-10*(0.6-0.15))) = 1/(1+exp(-4.5)) ~ 0.989
        $this->assertGreaterThan(0.9, $penalty);
    }

    /** @test */
    public function penalty_moderate_discrepancy_above_tolerance(): void
    {
        // delta=0.3 -> above tolerance by 0.15
        $penalty = $this->service->computePenalty(0.8, 0.5);

        // P = 1/(1+exp(-10*(0.3-0.15))) = 1/(1+exp(-1.5)) ~ 0.818
        $this->assertGreaterThan(0.7, $penalty);
        $this->assertLessThan(0.95, $penalty);
    }

    /** @test */
    public function penalty_is_symmetric(): void
    {
        // |S_r - S_c| is absolute, so order should not matter
        $p1 = $this->service->computePenalty(0.9, 0.5);
        $p2 = $this->service->computePenalty(0.5, 0.9);

        $this->assertEqualsWithDelta($p1, $p2, 0.001);
    }

    /** @test */
    public function penalty_respects_custom_steepness(): void
    {
        // Higher steepness makes the sigmoid sharper
        config(['trust.penalty_steepness' => 50.0]);
        $steepService = new TrustCalculatorService();

        $penaltyDefault = $this->service->computePenalty(0.8, 0.5);
        $penaltySteep = $steepService->computePenalty(0.8, 0.5);

        // With higher steepness, penalty for same discrepancy should be even higher
        // since delta=0.3 > tolerance=0.15
        $this->assertGreaterThan($penaltyDefault, $penaltySteep);
    }

    // =========================================================================
    // Trust Score Update (Integration) Tests
    // =========================================================================

    /** @test */
    public function update_trust_score_returns_expected_keys(): void
    {
        $artisan = $this->createArtisan();

        $result = $this->service->updateTrustScore($artisan->id);

        $this->assertArrayHasKey('T_new', $result);
        $this->assertArrayHasKey('S_r', $result);
        $this->assertArrayHasKey('S_c', $result);
        $this->assertArrayHasKey('P', $result);
        $this->assertArrayHasKey('T_prev', $result);
    }

    /** @test */
    public function update_trust_score_with_no_reviews_and_no_orders(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        $result = $this->service->updateTrustScore($artisan->id);

        // S_r = 0.5 (no reviews default)
        $this->assertEqualsWithDelta(0.5, $result['S_r'], 0.001);
        // S_c = 0.5 (no orders default)
        $this->assertEqualsWithDelta(0.5, $result['S_c'], 0.001);
        // Penalty: delta=|0.5-0.5|=0 -> low penalty
        $this->assertLessThan(0.25, $result['P']);
        // T_prev = 0.5
        $this->assertEqualsWithDelta(0.5, $result['T_prev'], 0.001);
        // T_new is clipped to [0,1]
        $this->assertGreaterThanOrEqual(0.0, $result['T_new']);
        $this->assertLessThanOrEqual(1.0, $result['T_new']);
    }

    /** @test */
    public function update_trust_score_with_perfect_reviews_and_full_completion(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // 5 perfect reviews, all today
        for ($i = 0; $i < 5; $i++) {
            $this->createReview($artisan, 5, 0);
        }

        // 5 completed orders
        for ($i = 0; $i < 5; $i++) {
            $this->createOrder($artisan, 'completed');
        }

        $result = $this->service->updateTrustScore($artisan->id);

        // S_r should be ~1.0 (all 5-star reviews)
        $this->assertEqualsWithDelta(1.0, $result['S_r'], 0.01);
        // S_c should be 1.0 (5/5)
        $this->assertEqualsWithDelta(1.0, $result['S_c'], 0.01);
        // Penalty should be low (small discrepancy: |1.0 - 1.0| = 0)
        $this->assertLessThan(0.25, $result['P']);
        // T_new should be higher than T_prev (0.5)
        $this->assertGreaterThan(0.5, $result['T_new']);
    }

    /** @test */
    public function update_trust_score_persists_to_artisan_model(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        $this->createReview($artisan, 5, 0);
        $this->createOrder($artisan, 'completed');

        $result = $this->service->updateTrustScore($artisan->id);

        $artisan->refresh();
        $this->assertEqualsWithDelta($result['T_new'], (float) $artisan->trust_score, 0.0001);
    }

    /** @test */
    public function update_trust_score_creates_trust_cache(): void
    {
        $artisan = $this->createArtisan();

        $this->service->updateTrustScore($artisan->id);

        $cache = TrustCache::where('artisan_id', $artisan->id)->first();
        $this->assertNotNull($cache);
        $this->assertEquals($artisan->id, $cache->artisan_id);
    }

    /** @test */
    public function update_trust_score_updates_existing_trust_cache(): void
    {
        $artisan = $this->createArtisan();

        // First calculation
        $this->service->updateTrustScore($artisan->id);
        $firstCache = TrustCache::where('artisan_id', $artisan->id)->first();
        $firstScore = (float) $firstCache->trust_score;

        // Add some reviews and orders, then recalculate
        for ($i = 0; $i < 3; $i++) {
            $this->createReview($artisan, 5, 0);
            $this->createOrder($artisan, 'completed');
        }

        $this->service->updateTrustScore($artisan->id);
        $secondCache = TrustCache::where('artisan_id', $artisan->id)->first();

        // Trust cache should be updated (not a second row)
        $this->assertEquals(1, TrustCache::where('artisan_id', $artisan->id)->count());
    }

    /** @test */
    public function update_trust_score_updates_avg_rating_and_jobs_completed(): void
    {
        $artisan = $this->createArtisan();

        $this->createReview($artisan, 4, 0);
        $this->createReview($artisan, 5, 0);

        $this->createOrder($artisan, 'completed');
        $this->createOrder($artisan, 'completed');
        $this->createOrder($artisan, 'pending');

        $this->service->updateTrustScore($artisan->id);

        $artisan->refresh();
        // avg_rating of published reviews: (4+5)/2 = 4.5
        $this->assertEqualsWithDelta(4.5, (float) $artisan->avg_rating, 0.01);
        // jobs_completed = number of orders with status=completed = 2
        $this->assertEquals(2, $artisan->jobs_completed);
    }

    // =========================================================================
    // Trust Score Convergence Test
    // =========================================================================

    /** @test */
    public function trust_score_converges_after_multiple_updates(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // Give consistent good reviews and full completion
        for ($i = 0; $i < 5; $i++) {
            $this->createReview($artisan, 4, 0);
            $this->createOrder($artisan, 'completed');
        }

        $scores = [];

        // Run multiple updates and record scores
        for ($i = 0; $i < 15; $i++) {
            $result = $this->service->updateTrustScore($artisan->id);
            $scores[] = $result['T_new'];
        }

        // After enough updates, the score should stabilize (convergence)
        // The difference between the last two scores should be very small
        $lastDiff = abs($scores[count($scores) - 1] - $scores[count($scores) - 2]);
        $this->assertLessThan(0.01, $lastDiff, 'Trust score should converge after repeated updates');

        // Also verify the first few updates showed larger changes
        $firstDiff = abs($scores[1] - $scores[0]);
        // The first difference should be at least as large as the last (or both very small)
        $this->assertGreaterThanOrEqual($lastDiff, $firstDiff + 0.0001,
            'Earlier updates should show more change than later ones, indicating convergence');
    }

    /** @test */
    public function trust_score_is_always_bounded_zero_to_one(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.0]);

        // Even with terrible reviews, trust stays in [0,1]
        for ($i = 0; $i < 3; $i++) {
            $this->createReview($artisan, 1, 0);
        }

        for ($round = 0; $round < 5; $round++) {
            $result = $this->service->updateTrustScore($artisan->id);
            $this->assertGreaterThanOrEqual(0.0, $result['T_new']);
            $this->assertLessThanOrEqual(1.0, $result['T_new']);
        }
    }

    /** @test */
    public function trust_score_bounded_when_starting_high(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 1.0]);

        // Artisan with already-max trust and great reviews
        for ($i = 0; $i < 3; $i++) {
            $this->createReview($artisan, 5, 0);
            $this->createOrder($artisan, 'completed');
        }

        $result = $this->service->updateTrustScore($artisan->id);
        $this->assertLessThanOrEqual(1.0, $result['T_new']);
        $this->assertGreaterThanOrEqual(0.0, $result['T_new']);
    }

    // =========================================================================
    // Fraud Detection: High Ratings + Low Completion = High Penalty
    // =========================================================================

    /** @test */
    public function fraud_scenario_high_ratings_low_completion_high_penalty(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.8000]);

        // Suspicious pattern: very high ratings
        for ($i = 0; $i < 10; $i++) {
            $this->createReview($artisan, 5, 0);
        }

        // But very low completion rate: only 1 completed out of 10
        $this->createOrder($artisan, 'completed');
        for ($i = 0; $i < 9; $i++) {
            $this->createOrder($artisan, 'cancelled_by_artisan');
        }

        $result = $this->service->updateTrustScore($artisan->id);

        // S_r should be ~1.0 (all 5-star)
        $this->assertGreaterThan(0.9, $result['S_r']);
        // S_c should be 0.1 (1/10)
        $this->assertEqualsWithDelta(0.1, $result['S_c'], 0.01);
        // Discrepancy ~0.9, way above tolerance -> penalty should be very high
        $this->assertGreaterThan(0.9, $result['P'], 'Penalty should be very high for rating-completion discrepancy');

        // Trust score should drop because T_new = (1-0.3)*0.8 + 0.3*(1.0 - ~1.0)
        // ~ 0.56 + ~0 = ~0.56, which is lower than T_prev=0.8
        $this->assertLessThan($result['T_prev'], $result['T_new'],
            'Trust should decrease when penalty is high due to fraud-like pattern');
    }

    /** @test */
    public function fraud_pattern_lowers_trust_significantly_over_time(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.8000]);

        // Suspicious: 10 perfect reviews but only 2 out of 10 orders completed
        for ($i = 0; $i < 10; $i++) {
            $this->createReview($artisan, 5, 0);
        }
        for ($i = 0; $i < 2; $i++) {
            $this->createOrder($artisan, 'completed');
        }
        for ($i = 0; $i < 8; $i++) {
            $this->createOrder($artisan, 'cancelled_by_artisan');
        }

        // Run multiple trust updates
        $initialTrust = 0.8;
        for ($round = 0; $round < 10; $round++) {
            $result = $this->service->updateTrustScore($artisan->id);
        }

        // After repeated updates, trust should have dropped significantly from 0.8
        $this->assertLessThan($initialTrust - 0.1, $result['T_new'],
            'Fraud-like pattern should significantly reduce trust score over time');
    }

    /** @test */
    public function legitimate_artisan_with_aligned_scores_maintains_trust(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // Perfect 5-star reviews and 100% completion: S_r ~ 1.0, S_c = 1.0
        // This gives near-zero discrepancy, so the penalty is very low.
        for ($i = 0; $i < 10; $i++) {
            $this->createReview($artisan, 5, 0);
            $this->createOrder($artisan, 'completed');
        }

        // Run several updates
        for ($round = 0; $round < 10; $round++) {
            $result = $this->service->updateTrustScore($artisan->id);
        }

        // S_r ~ 1.0, S_c = 1.0, delta ~ 0, penalty ~ 0.18 (below tolerance)
        // Trust converges to about (1.0 - 0.18) = 0.82
        $this->assertGreaterThan(0.5, $result['T_new'],
            'A legitimate artisan with perfect reviews and completion should maintain high trust');
        $this->assertLessThan(0.3, $result['P'],
            'Penalty should be low when rating and completion are both high');
    }

    // =========================================================================
    // Edge Cases
    // =========================================================================

    /** @test */
    public function update_trust_score_with_only_old_reviews(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // Reviews from 90 days ago (3 half-lives, weight ~0.125)
        for ($i = 0; $i < 3; $i++) {
            $this->createReview($artisan, 5, 90);
        }

        $result = $this->service->updateTrustScore($artisan->id);

        // S_r should still be 1.0 since all reviews are 5-star (same weight)
        $this->assertEqualsWithDelta(1.0, $result['S_r'], 0.01);
    }

    /** @test */
    public function update_trust_score_mixed_old_and_new_reviews(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // Recent good review (rating=5, normalized=1.0, weight~1.0)
        $this->createReview($artisan, 5, 0);
        // Old bad review (rating=1, normalized=0.0, weight~0.25 at 60 days)
        $this->createReview($artisan, 1, 60);

        $this->createOrder($artisan, 'completed');

        $result = $this->service->updateTrustScore($artisan->id);

        // S_r = (1.0*1.0 + 0.25*0.0) / (1.0 + 0.25) = 0.80
        // The recent 5-star review dominates the old 1-star review
        $this->assertGreaterThan(0.7, $result['S_r'],
            'Rating component should weight recent good reviews more heavily');
    }

    /** @test */
    public function smoothing_factor_affects_update_magnitude(): void
    {
        // With low smoothing, changes are smaller
        config(['trust.update_smoothing' => 0.10]);
        $slowService = new TrustCalculatorService();

        // With high smoothing, changes are larger
        config(['trust.update_smoothing' => 0.90]);
        $fastService = new TrustCalculatorService();

        // Reset to default
        config(['trust.update_smoothing' => 0.30]);

        // Create scenario with definite change
        $artisan1 = $this->createArtisan(['trust_score' => 0.5000]);
        $artisan2 = $this->createArtisan(['trust_score' => 0.5000]);

        for ($i = 0; $i < 5; $i++) {
            $this->createReview($artisan1, 5, 0);
            $this->createReview($artisan2, 5, 0);
            $this->createOrder($artisan1, 'completed');
            $this->createOrder($artisan2, 'completed');
        }

        $resultSlow = $slowService->updateTrustScore($artisan1->id);
        $resultFast = $fastService->updateTrustScore($artisan2->id);

        $changeSlow = abs($resultSlow['T_new'] - $resultSlow['T_prev']);
        $changeFast = abs($resultFast['T_new'] - $resultFast['T_prev']);

        $this->assertGreaterThan($changeSlow, $changeFast,
            'Higher smoothing factor should produce larger trust score changes');
    }
}
