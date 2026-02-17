<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\Order;
use App\Models\Review;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Positive review comment templates.
     */
    private const POSITIVE_COMMENTS = [
        'Excellent work! Very professional and timely.',
        'Great artisan, highly recommend. Clean and efficient work.',
        'Very satisfied with the result. Will hire again.',
        'Outstanding quality of work. Worth every dirham.',
        'Professional, punctual, and the work exceeded my expectations.',
        'Did an amazing job. Very skilled and knowledgeable.',
        'Top-notch service. The artisan was very respectful and clean.',
        'Fantastic experience from start to finish. Very reliable.',
        'The best artisan I have worked with in the city. Truly skilled.',
        'Incredibly detailed work. Very happy with the outcome.',
        'Fast, efficient, and the quality is superb. Recommended.',
        'Very honest pricing and excellent craftsmanship.',
        'He completed the job ahead of schedule. Very impressed.',
        'Amazing attention to detail. Will definitely call again.',
        'Could not be happier with the result. True professional.',
    ];

    /**
     * Neutral review comment templates.
     */
    private const NEUTRAL_COMMENTS = [
        'Work was decent, nothing exceptional but got the job done.',
        'Average service. Took a bit longer than expected.',
        'The work is okay. Some minor issues but acceptable.',
        'Fair quality for the price. Could improve communication.',
        'Satisfactory work overall. Room for improvement in timeliness.',
        'Did what was asked. Nothing more, nothing less.',
        'Reasonable work quality. Communication could be better.',
        'The job was completed but took longer than the estimate.',
    ];

    /**
     * Negative review comment templates.
     */
    private const NEGATIVE_COMMENTS = [
        'Disappointing quality. Had to call someone else to fix issues.',
        'Very late and the work was below expectations.',
        'Poor communication and the result was not what we agreed on.',
        'Would not recommend. Left a mess and the work is shoddy.',
        'Unprofessional behavior. The work needs to be redone.',
        'Overcharged and underdelivered. Very unsatisfied.',
    ];

    /**
     * Suspiciously glowing review comments for fraudulent artisans (fake reviews).
     */
    private const FAKE_POSITIVE_COMMENTS = [
        'ABSOLUTELY THE BEST! 10/10 would recommend to everyone!!!',
        'Perfect perfect perfect! Best artisan in all of Morocco!',
        'I cannot believe how amazing this work is. Simply the best.',
        'Never seen such incredible talent. A true master artisan!',
        'Beyond perfect. This artisan is a gift to the profession.',
        'Magnificent work!!! Everyone should hire this artisan!!!',
        'Flawless execution. Best experience I have ever had.',
        'Five stars is not enough. This artisan deserves ten stars!',
        'Unbelievable quality at an incredible price. Number one!',
        'The most talented artisan I have ever encountered. A genius!',
    ];

    /**
     * Indices of fraudulent artisans (must match ArtisanSeeder::FRAUDULENT_INDICES).
     */
    private const FRAUDULENT_INDICES = [7, 19, 31, 42];

    public function run(): void
    {
        $artisans = Artisan::all();
        $now = Carbon::now();

        $this->command->info('Seeding reviews for ' . $artisans->count() . ' artisans...');

        $totalReviews = 0;

        foreach ($artisans as $index => $artisan) {
            $isFraudulent = in_array($index, self::FRAUDULENT_INDICES);

            // Get completed orders for this artisan (reviews are tied to completed orders)
            $completedOrders = Order::where('artisan_id', $artisan->id)
                ->where('status', 'completed')
                ->get();

            if ($isFraudulent) {
                // Fraudulent artisans get inflated reviews:
                // - Reviews on their completed orders (all 5 stars with fake comments)
                // - Additional fake reviews not tied to orders (to inflate count)
                $totalReviews += $this->seedFraudulentReviews($artisan, $completedOrders, $now);
            } else {
                // Normal artisans: reviews on completed orders with realistic distribution
                $totalReviews += $this->seedNormalReviews($artisan, $completedOrders, $now);
            }
        }

        // If we haven't reached 500+ reviews, generate additional reviews for random artisans
        $remaining = max(0, 510 - $totalReviews);
        if ($remaining > 0) {
            $this->command->info("Generating {$remaining} additional reviews to reach 500+ target...");
            $totalReviews += $this->seedAdditionalReviews($artisans, $remaining, $now);
        }

        $this->command->info("Review seeding complete: {$totalReviews} reviews created.");
        $this->printReviewStats();
    }

    /**
     * Seed reviews for a normal (legitimate) artisan.
     * Returns the number of reviews created.
     */
    private function seedNormalReviews(Artisan $artisan, $completedOrders, Carbon $now): int
    {
        $count = 0;

        foreach ($completedOrders as $order) {
            // ~85% of completed orders get a review
            if (rand(1, 100) > 85) {
                continue;
            }

            $rating = $this->generateNormalRating();
            $comment = $this->pickComment($rating);

            // Review created 1-7 days after order completion
            $orderCompletedAt = $order->completed_date
                ? Carbon::parse($order->completed_date)
                : Carbon::parse($order->created_at)->addDays(rand(3, 10));

            $reviewCreatedAt = $orderCompletedAt->copy()->addDays(rand(1, 7))->addHours(rand(0, 23));

            // Ensure review is within past 90 days
            $ninetyDaysAgo = $now->copy()->subDays(90);
            if ($reviewCreatedAt->lt($ninetyDaysAgo)) {
                $reviewCreatedAt = $ninetyDaysAgo->copy()->addDays(rand(0, 10));
            }
            if ($reviewCreatedAt->gt($now)) {
                $reviewCreatedAt = $now->copy()->subDays(rand(1, 5));
            }

            Review::create([
                'artisan_id' => $artisan->id,
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'rating' => $rating,
                'comment' => $comment,
                'status' => 'published',
                'created_at' => $reviewCreatedAt,
                'updated_at' => $reviewCreatedAt,
            ]);

            $count++;
        }

        return $count;
    }

    /**
     * Seed inflated/fake reviews for a fraudulent artisan.
     * These artisans get many 5-star reviews with suspiciously positive comments.
     * Returns the number of reviews created.
     */
    private function seedFraudulentReviews(Artisan $artisan, $completedOrders, Carbon $now): int
    {
        $count = 0;

        // Get all customer IDs for fake reviews
        $artisanUserIds = Artisan::pluck('user_id')->filter()->toArray();
        $customerIds = \App\Models\User::whereNotIn('id', $artisanUserIds)->pluck('id')->toArray();

        // First, add reviews for actual completed orders (all 5 stars)
        foreach ($completedOrders as $order) {
            $reviewCreatedAt = $this->randomDateInPast90Days($now);

            Review::create([
                'artisan_id' => $artisan->id,
                'customer_id' => $order->customer_id,
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => self::FAKE_POSITIVE_COMMENTS[array_rand(self::FAKE_POSITIVE_COMMENTS)],
                'status' => 'published',
                'created_at' => $reviewCreatedAt,
                'updated_at' => $reviewCreatedAt,
            ]);
            $count++;
        }

        // Then add additional fake reviews (not tied to orders) to inflate the rating
        // Fraudulent artisans get 15-25 extra fake reviews
        $fakeCount = rand(15, 25);
        for ($i = 0; $i < $fakeCount; $i++) {
            $customerId = $customerIds[array_rand($customerIds)];
            $reviewCreatedAt = $this->randomDateInPast90Days($now);

            // Overwhelmingly 5 stars, occasional 4 to seem less suspicious
            $rating = rand(1, 100) <= 85 ? 5 : 4;

            Review::create([
                'artisan_id' => $artisan->id,
                'customer_id' => $customerId,
                'order_id' => null,
                'rating' => $rating,
                'comment' => self::FAKE_POSITIVE_COMMENTS[array_rand(self::FAKE_POSITIVE_COMMENTS)],
                'status' => 'published',
                'created_at' => $reviewCreatedAt,
                'updated_at' => $reviewCreatedAt,
            ]);
            $count++;
        }

        $this->command->line("  Fraudulent artisan #{$artisan->id} ({$artisan->name}): {$count} inflated reviews");

        return $count;
    }

    /**
     * Generate additional reviews to meet the 500+ target.
     * These go to non-fraudulent artisans.
     */
    private function seedAdditionalReviews($artisans, int $count, Carbon $now): int
    {
        $created = 0;

        // Filter out fraudulent artisans
        $legitimateArtisans = $artisans->filter(function ($artisan, $index) {
            return ! in_array($index, self::FRAUDULENT_INDICES);
        });

        $artisanUserIds = Artisan::pluck('user_id')->filter()->toArray();
        $customerIds = \App\Models\User::whereNotIn('id', $artisanUserIds)->pluck('id')->toArray();

        if ($legitimateArtisans->isEmpty() || empty($customerIds)) {
            return 0;
        }

        for ($i = 0; $i < $count; $i++) {
            $artisan = $legitimateArtisans->random();
            $customerId = $customerIds[array_rand($customerIds)];

            // Try to find a completed order for this artisan that doesn't have a review yet
            $orderWithoutReview = Order::where('artisan_id', $artisan->id)
                ->where('status', 'completed')
                ->whereDoesntHave('review')
                ->first();

            $rating = $this->generateNormalRating();
            $comment = $this->pickComment($rating);
            $reviewCreatedAt = $this->randomDateInPast90Days($now);

            Review::create([
                'artisan_id' => $artisan->id,
                'customer_id' => $customerId,
                'order_id' => $orderWithoutReview?->id,
                'rating' => $rating,
                'comment' => $comment,
                'status' => 'published',
                'created_at' => $reviewCreatedAt,
                'updated_at' => $reviewCreatedAt,
            ]);

            $created++;
        }

        return $created;
    }

    /**
     * Generate a realistic rating distribution for normal artisans.
     * Skewed towards positive: ~40% 5-star, ~25% 4-star, ~15% 3-star, ~12% 2-star, ~8% 1-star.
     */
    private function generateNormalRating(): int
    {
        $roll = rand(1, 100);

        if ($roll <= 40) {
            return 5;
        }
        if ($roll <= 65) {
            return 4;
        }
        if ($roll <= 80) {
            return 3;
        }
        if ($roll <= 92) {
            return 2;
        }

        return 1;
    }

    /**
     * Pick an appropriate comment based on the rating.
     */
    private function pickComment(int $rating): string
    {
        if ($rating >= 4) {
            return self::POSITIVE_COMMENTS[array_rand(self::POSITIVE_COMMENTS)];
        }

        if ($rating === 3) {
            return self::NEUTRAL_COMMENTS[array_rand(self::NEUTRAL_COMMENTS)];
        }

        return self::NEGATIVE_COMMENTS[array_rand(self::NEGATIVE_COMMENTS)];
    }

    /**
     * Generate a random datetime within the past 90 days.
     */
    private function randomDateInPast90Days(Carbon $now): Carbon
    {
        return $now->copy()
            ->subDays(rand(1, 90))
            ->subHours(rand(0, 23))
            ->subMinutes(rand(0, 59));
    }

    /**
     * Print summary statistics about generated reviews.
     */
    private function printReviewStats(): void
    {
        $total = Review::count();
        $ratings = Review::selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->pluck('count', 'rating')
            ->toArray();

        $avgRating = Review::avg('rating');

        $this->command->info("Review distribution (total: {$total}, avg: " . round($avgRating, 2) . ')');
        for ($r = 5; $r >= 1; $r--) {
            $count = $ratings[$r] ?? 0;
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $bar = str_repeat('*', (int) round($pct / 2));
            $this->command->line("  {$r}-star: {$count} ({$pct}%) {$bar}");
        }

        // Show review distribution over time (by week)
        $this->command->info('Review distribution by week:');
        $now = Carbon::now();
        for ($week = 0; $week < 13; $week++) {
            $weekStart = $now->copy()->subWeeks($week + 1);
            $weekEnd = $now->copy()->subWeeks($week);
            $weekCount = Review::whereBetween('created_at', [$weekStart, $weekEnd])->count();
            $this->command->line("  Week -{$week}: {$weekCount} reviews");
        }
    }
}
