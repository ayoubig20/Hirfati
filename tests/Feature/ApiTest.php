<?php

namespace Tests\Feature;

use App\Models\Artisan;
use App\Models\Order;
use App\Models\Review;
use App\Models\TrustCache;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    private function createUser(array $overrides = []): User
    {
        return User::factory()->create($overrides);
    }

    private function createArtisan(array $overrides = []): Artisan
    {
        $user = $this->createUser();

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

    private function createOrder(Artisan $artisan, User $customer, array $overrides = []): Order
    {
        return Order::create(array_merge([
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'service_id' => null,
            'status' => 'pending',
            'total_price' => 200.00,
            'scheduled_date' => Carbon::now()->addDays(3)->toDateString(),
        ], $overrides));
    }

    private function createReview(Artisan $artisan, User $customer, ?Order $order = null, array $overrides = []): Review
    {
        return Review::create(array_merge([
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'order_id' => $order?->id,
            'rating' => 4,
            'comment' => 'Good work!',
            'status' => 'published',
        ], $overrides));
    }

    // =========================================================================
    // POST /api/artisans - Create Artisan
    // =========================================================================

    /** @test */
    public function can_create_artisan(): void
    {
        $payload = [
            'name' => 'Ahmed Benali',
            'email' => 'ahmed.benali@example.com',
            'phone' => '0612345678',
            'service_category' => 'electrical',
            'specialty' => 'Home wiring',
            'location' => 'Rabat',
            'hourly_rate' => 150.00,
        ];

        $response = $this->postJson('/api/artisans', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'service_category' => 'electrical',
                'location' => 'Rabat',
            ]);

        $this->assertDatabaseHas('artisans', [
            'email' => 'ahmed.benali@example.com',
            'name' => 'Ahmed Benali',
        ]);
    }

    /** @test */
    public function create_artisan_validates_required_fields(): void
    {
        $response = $this->postJson('/api/artisans', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'service_category', 'location']);
    }

    /** @test */
    public function create_artisan_validates_unique_email(): void
    {
        $artisan = $this->createArtisan(['email' => 'duplicate@example.com']);

        $response = $this->postJson('/api/artisans', [
            'name' => 'Another Artisan',
            'email' => 'duplicate@example.com',
            'service_category' => 'plumbing',
            'location' => 'Fes',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // =========================================================================
    // GET /api/artisans/search - Search Artisans
    // =========================================================================

    /** @test */
    public function can_search_artisans(): void
    {
        // Create some active artisans
        $this->createArtisan(['name' => 'Plumber One', 'service_category' => 'plumbing', 'location' => 'Casablanca']);
        $this->createArtisan(['name' => 'Plumber Two', 'service_category' => 'plumbing', 'location' => 'Casablanca']);
        $this->createArtisan(['name' => 'Electrician', 'service_category' => 'electrical', 'location' => 'Rabat']);

        $response = $this->getJson('/api/artisans/search?category=plumbing&location=Casablanca');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'current_page',
                'per_page',
                'total',
            ]);

        // Should return only the 2 plumbers in Casablanca
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function search_returns_paginated_results(): void
    {
        // Create 25 artisans
        for ($i = 0; $i < 25; $i++) {
            $this->createArtisan(['name' => "Artisan {$i}", 'service_category' => 'plumbing']);
        }

        $response = $this->getJson('/api/artisans/search?category=plumbing&per_page=10');

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('per_page'));
        $this->assertEquals(25, $response->json('total'));
        $this->assertCount(10, $response->json('data'));
    }

    /** @test */
    public function search_without_filters_returns_all_active(): void
    {
        $this->createArtisan(['status' => 'active']);
        $this->createArtisan(['status' => 'active']);
        $this->createArtisan(['status' => 'inactive']);

        $response = $this->getJson('/api/artisans/search');

        $response->assertStatus(200);
        // Only active artisans are returned
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function search_validates_sort_by_parameter(): void
    {
        $response = $this->getJson('/api/artisans/search?sort_by=invalid_field');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sort_by']);
    }

    // =========================================================================
    // GET /api/artisans/{id} - Show Artisan
    // =========================================================================

    /** @test */
    public function can_show_artisan_details(): void
    {
        $artisan = $this->createArtisan(['name' => 'Detailed Artisan']);

        $response = $this->getJson("/api/artisans/{$artisan->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Detailed Artisan'])
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'service_category',
                'location',
                'trust_score',
                'status',
                'reviews_count',
                'orders_count',
            ]);
    }

    /** @test */
    public function show_artisan_returns_404_for_nonexistent(): void
    {
        $response = $this->getJson('/api/artisans/99999');

        $response->assertStatus(404);
    }

    // =========================================================================
    // PUT /api/artisans/{id} - Update Artisan
    // =========================================================================

    /** @test */
    public function can_update_artisan(): void
    {
        $artisan = $this->createArtisan(['name' => 'Old Name', 'location' => 'Casablanca']);

        $response = $this->putJson("/api/artisans/{$artisan->id}", [
            'name' => 'New Name',
            'location' => 'Marrakech',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'New Name',
                'location' => 'Marrakech',
            ]);

        $this->assertDatabaseHas('artisans', [
            'id' => $artisan->id,
            'name' => 'New Name',
            'location' => 'Marrakech',
        ]);
    }

    /** @test */
    public function can_update_artisan_status(): void
    {
        $artisan = $this->createArtisan(['status' => 'active']);

        $response = $this->putJson("/api/artisans/{$artisan->id}", [
            'status' => 'suspended',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'suspended']);
    }

    /** @test */
    public function update_artisan_validates_status_enum(): void
    {
        $artisan = $this->createArtisan();

        $response = $this->putJson("/api/artisans/{$artisan->id}", [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function update_artisan_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/artisans/99999', [
            'name' => 'Whatever',
        ]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // POST /api/orders - Create Order
    // =========================================================================

    /** @test */
    public function can_create_order(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();

        $payload = [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'total_price' => 500.00,
            'scheduled_date' => Carbon::now()->addDays(7)->toDateString(),
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'artisan_id' => $artisan->id,
                'customer_id' => $customer->id,
            ]);

        $this->assertDatabaseHas('orders', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
        ]);
    }

    /** @test */
    public function create_order_validates_required_fields(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artisan_id', 'customer_id']);
    }

    /** @test */
    public function create_order_validates_artisan_exists(): void
    {
        $customer = $this->createUser();

        $response = $this->postJson('/api/orders', [
            'artisan_id' => 99999,
            'customer_id' => $customer->id,
            'total_price' => 100.00,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artisan_id']);
    }

    /** @test */
    public function create_order_defaults_to_pending_status(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();

        $response = $this->postJson('/api/orders', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'artisan_id' => $artisan->id,
            'status' => 'pending',
        ]);
    }

    // =========================================================================
    // PUT /api/orders/{id}/status - Update Order Status
    // =========================================================================

    /** @test */
    public function can_update_order_status(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();
        $order = $this->createOrder($artisan, $customer);

        $response = $this->putJson("/api/orders/{$order->id}/status", [
            'status' => 'accepted',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'accepted']);
    }

    /** @test */
    public function completing_order_sets_completed_date(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();
        $order = $this->createOrder($artisan, $customer);

        $response = $this->putJson("/api/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);

        $order->refresh();
        $this->assertNotNull($order->completed_date);
    }

    /** @test */
    public function update_order_status_validates_enum(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();
        $order = $this->createOrder($artisan, $customer);

        $response = $this->putJson("/api/orders/{$order->id}/status", [
            'status' => 'invalid',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    /** @test */
    public function update_order_status_triggers_trust_recalculation_on_completion(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);
        $customer = $this->createUser();
        $order = $this->createOrder($artisan, $customer);

        // Add a review so trust calculation has data
        $this->createReview($artisan, $customer, $order, ['rating' => 5]);

        $response = $this->putJson("/api/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);

        // Since queue is sync, trust recalculation should have run
        // The trust_cache should exist now
        $this->assertDatabaseHas('trust_cache', [
            'artisan_id' => $artisan->id,
        ]);
    }

    /** @test */
    public function update_order_status_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/orders/99999/status', [
            'status' => 'completed',
        ]);

        $response->assertStatus(404);
    }

    // =========================================================================
    // POST /api/reviews - Submit Review
    // =========================================================================

    /** @test */
    public function can_submit_review(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();
        $order = $this->createOrder($artisan, $customer, ['status' => 'completed']);

        $payload = [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Excellent plumbing work, very professional!',
        ];

        $response = $this->postJson('/api/reviews', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'rating' => 5,
                'comment' => 'Excellent plumbing work, very professional!',
            ]);

        $this->assertDatabaseHas('reviews', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function submit_review_validates_required_fields(): void
    {
        $response = $this->postJson('/api/reviews', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['artisan_id', 'customer_id', 'rating']);
    }

    /** @test */
    public function submit_review_validates_rating_range(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();

        // Rating too low
        $response = $this->postJson('/api/reviews', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'rating' => 0,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);

        // Rating too high
        $response = $this->postJson('/api/reviews', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'rating' => 6,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    /** @test */
    public function submit_review_triggers_trust_recalculation(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);
        $customer = $this->createUser();

        $response = $this->postJson('/api/reviews', [
            'artisan_id' => $artisan->id,
            'customer_id' => $customer->id,
            'rating' => 5,
            'comment' => 'Great service',
        ]);

        $response->assertStatus(201);

        // Since queue is sync, trust recalculation should have run
        $this->assertDatabaseHas('trust_cache', [
            'artisan_id' => $artisan->id,
        ]);
    }

    // =========================================================================
    // GET /api/artisans/{id}/reviews - Get Artisan Reviews
    // =========================================================================

    /** @test */
    public function can_get_artisan_reviews(): void
    {
        $artisan = $this->createArtisan();
        $customer1 = $this->createUser();
        $customer2 = $this->createUser();

        $this->createReview($artisan, $customer1, null, ['rating' => 5, 'status' => 'published']);
        $this->createReview($artisan, $customer2, null, ['rating' => 4, 'status' => 'published']);
        // Pending review should not appear
        $this->createReview($artisan, $customer1, null, ['rating' => 1, 'status' => 'pending']);

        $response = $this->getJson("/api/artisans/{$artisan->id}/reviews");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'artisan_id', 'rating', 'comment', 'status'],
                ],
                'current_page',
                'per_page',
                'total',
            ]);

        // Only 2 published reviews should be returned
        $this->assertEquals(2, $response->json('total'));
    }

    /** @test */
    public function get_artisan_reviews_returns_paginated_results(): void
    {
        $artisan = $this->createArtisan();

        for ($i = 0; $i < 25; $i++) {
            $customer = $this->createUser();
            $this->createReview($artisan, $customer, null, ['rating' => rand(1, 5)]);
        }

        $response = $this->getJson("/api/artisans/{$artisan->id}/reviews?per_page=10");

        $response->assertStatus(200);
        $this->assertEquals(10, $response->json('per_page'));
        $this->assertEquals(25, $response->json('total'));
        $this->assertCount(10, $response->json('data'));
    }

    // =========================================================================
    // POST /api/trust/calculate/{id} - Recalculate Trust
    // =========================================================================

    /** @test */
    public function can_recalculate_trust_score(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);
        $customer = $this->createUser();

        // Add some reviews and orders
        $this->createReview($artisan, $customer, null, ['rating' => 5]);
        $order = $this->createOrder($artisan, $customer, ['status' => 'completed']);

        $response = $this->postJson("/api/trust/calculate/{$artisan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'artisan_id',
                'trust_score',
                'rating_component',
                'completion_component',
                'penalty',
                'previous_score',
            ])
            ->assertJsonFragment([
                'artisan_id' => $artisan->id,
            ]);
    }

    /** @test */
    public function recalculate_trust_returns_numeric_components(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        $response = $this->postJson("/api/trust/calculate/{$artisan->id}");

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertIsNumeric($data['trust_score']);
        $this->assertIsNumeric($data['rating_component']);
        $this->assertIsNumeric($data['completion_component']);
        $this->assertIsNumeric($data['penalty']);
        $this->assertIsNumeric($data['previous_score']);
    }

    /** @test */
    public function recalculate_trust_returns_404_for_nonexistent_artisan(): void
    {
        $response = $this->postJson('/api/trust/calculate/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function recalculate_trust_updates_artisan_trust_score(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);
        $customer = $this->createUser();

        // Add high reviews
        for ($i = 0; $i < 5; $i++) {
            $this->createReview($artisan, $customer, null, ['rating' => 5]);
            $this->createOrder($artisan, $customer, ['status' => 'completed']);
        }

        $response = $this->postJson("/api/trust/calculate/{$artisan->id}");
        $response->assertStatus(200);

        $artisan->refresh();
        $this->assertNotEquals(0.5000, (float) $artisan->trust_score,
            'Trust score should have been updated after recalculation');
    }

    // =========================================================================
    // GET /api/trust/stats/{id} - Get Trust Stats
    // =========================================================================

    /** @test */
    public function can_get_trust_stats(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);
        $customer = $this->createUser();

        // Add reviews and orders so there is data
        $this->createReview($artisan, $customer, null, ['rating' => 4]);
        $this->createOrder($artisan, $customer, ['status' => 'completed']);

        // First calculate so cache exists
        $this->postJson("/api/trust/calculate/{$artisan->id}");

        $response = $this->getJson("/api/trust/stats/{$artisan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'artisan_id',
                'trust_score',
                'rating_component',
                'completion_component',
                'penalty',
                'review_count',
                'order_count',
                'tier',
                'last_calculated',
            ])
            ->assertJsonFragment([
                'artisan_id' => $artisan->id,
            ]);
    }

    /** @test */
    public function trust_stats_auto_calculates_when_no_cache(): void
    {
        $artisan = $this->createArtisan(['trust_score' => 0.5000]);

        // No prior calculation - stats endpoint should trigger one
        $response = $this->getJson("/api/trust/stats/{$artisan->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'artisan_id',
                'trust_score',
                'tier',
            ]);

        // Trust cache should now exist
        $this->assertDatabaseHas('trust_cache', [
            'artisan_id' => $artisan->id,
        ]);
    }

    /** @test */
    public function trust_stats_returns_correct_tier(): void
    {
        // Gold tier: trust_score >= 0.85
        $goldArtisan = $this->createArtisan(['trust_score' => 0.9000]);

        // Create trust cache directly for known values
        TrustCache::create([
            'artisan_id' => $goldArtisan->id,
            'trust_score' => 0.9000,
            'rating_component' => 0.9500,
            'completion_component' => 0.9500,
            'penalty' => 0.0500,
            'review_count' => 10,
            'order_count' => 10,
        ]);

        $response = $this->getJson("/api/trust/stats/{$goldArtisan->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['tier' => 'gold']);
    }

    /** @test */
    public function trust_stats_returns_404_for_nonexistent_artisan(): void
    {
        $response = $this->getJson('/api/trust/stats/99999');

        $response->assertStatus(404);
    }

    /** @test */
    public function trust_stats_shows_review_and_order_counts(): void
    {
        $artisan = $this->createArtisan();
        $customer = $this->createUser();

        for ($i = 0; $i < 3; $i++) {
            $this->createReview($artisan, $customer, null, ['rating' => 4]);
        }
        for ($i = 0; $i < 5; $i++) {
            $this->createOrder($artisan, $customer, ['status' => 'completed']);
        }

        // Calculate trust first
        $this->postJson("/api/trust/calculate/{$artisan->id}");

        $response = $this->getJson("/api/trust/stats/{$artisan->id}");

        $response->assertStatus(200);
        $this->assertEquals(3, $response->json('review_count'));
        $this->assertEquals(5, $response->json('order_count'));
    }

    // =========================================================================
    // Cross-endpoint Integration Tests
    // =========================================================================

    /** @test */
    public function full_workflow_create_artisan_order_review_and_calculate_trust(): void
    {
        // 1. Create an artisan via API
        $createResponse = $this->postJson('/api/artisans', [
            'name' => 'Workflow Artisan',
            'email' => 'workflow@example.com',
            'service_category' => 'carpentry',
            'location' => 'Tangier',
            'hourly_rate' => 120.00,
        ]);
        $createResponse->assertStatus(201);
        $artisanId = $createResponse->json('id');

        // 2. Create a customer
        $customer = $this->createUser();

        // 3. Create an order via API
        $orderResponse = $this->postJson('/api/orders', [
            'artisan_id' => $artisanId,
            'customer_id' => $customer->id,
            'total_price' => 360.00,
            'scheduled_date' => Carbon::now()->addDays(5)->toDateString(),
        ]);
        $orderResponse->assertStatus(201);
        $orderId = $orderResponse->json('id');

        // 4. Complete the order
        $statusResponse = $this->putJson("/api/orders/{$orderId}/status", [
            'status' => 'completed',
        ]);
        $statusResponse->assertStatus(200)
            ->assertJsonFragment(['status' => 'completed']);

        // 5. Submit a review
        $reviewResponse = $this->postJson('/api/reviews', [
            'artisan_id' => $artisanId,
            'customer_id' => $customer->id,
            'order_id' => $orderId,
            'rating' => 5,
            'comment' => 'Outstanding carpentry work!',
        ]);
        $reviewResponse->assertStatus(201);

        // 6. Recalculate trust explicitly
        $trustResponse = $this->postJson("/api/trust/calculate/{$artisanId}");
        $trustResponse->assertStatus(200);
        $trustData = $trustResponse->json();

        $this->assertIsNumeric($trustData['trust_score']);
        $this->assertGreaterThan(0, $trustData['trust_score']);

        // 7. Verify trust stats
        $statsResponse = $this->getJson("/api/trust/stats/{$artisanId}");
        $statsResponse->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $statsResponse->json('review_count'));
        $this->assertGreaterThanOrEqual(1, $statsResponse->json('order_count'));

        // 8. Verify artisan can be retrieved with updated data
        $showResponse = $this->getJson("/api/artisans/{$artisanId}");
        $showResponse->assertStatus(200)
            ->assertJsonFragment(['name' => 'Workflow Artisan']);
    }

    /** @test */
    public function artisan_appears_in_search_after_creation(): void
    {
        $this->postJson('/api/artisans', [
            'name' => 'Searchable Artisan',
            'email' => 'searchable@example.com',
            'service_category' => 'masonry',
            'location' => 'Agadir',
            'hourly_rate' => 80.00,
        ])->assertStatus(201);

        $response = $this->getJson('/api/artisans/search?category=masonry&location=Agadir');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, $response->json('total'));

        $names = collect($response->json('data'))->pluck('name')->toArray();
        $this->assertContains('Searchable Artisan', $names);
    }
}
