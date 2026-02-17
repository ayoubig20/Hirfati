<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Valid order statuses.
     */
    private const STATUSES = [
        'pending',
        'accepted',
        'in_progress',
        'completed',
        'cancelled_by_customer',
        'cancelled_by_artisan',
    ];

    /**
     * Indices of fraudulent artisans (must match ArtisanSeeder::FRAUDULENT_INDICES).
     * These are 0-based indices into the artisan creation order, so artisan IDs = index + 1.
     */
    private const FRAUDULENT_INDICES = [7, 19, 31, 42];

    public function run(): void
    {
        $artisans = Artisan::with('services')->get();

        // Get customer IDs: users who are NOT artisan users.
        // Artisan user_ids are linked via the artisans table.
        $artisanUserIds = Artisan::pluck('user_id')->filter()->toArray();
        $customerIds = User::whereNotIn('id', $artisanUserIds)->pluck('id')->toArray();

        if (empty($customerIds)) {
            $this->command->error('No customer users found. Run ArtisanSeeder first.');
            return;
        }

        $this->command->info('Seeding orders for ' . $artisans->count() . ' artisans...');

        $totalOrders = 0;
        $now = Carbon::now();

        foreach ($artisans as $index => $artisan) {
            $services = $artisan->services;
            if ($services->isEmpty()) {
                continue;
            }

            // Determine if this artisan is fraudulent (0-based index)
            $isFraudulent = in_array($index, self::FRAUDULENT_INDICES);

            // Each artisan gets between 3 and 8 orders (more for some)
            // Fraudulent artisans get more orders to make the pattern clearer
            $orderCount = $isFraudulent ? rand(8, 14) : rand(3, 8);

            for ($j = 0; $j < $orderCount; $j++) {
                $service = $services->random();
                $customerId = $customerIds[array_rand($customerIds)];

                // Distribute orders over the past 90 days
                $daysAgo = rand(1, 90);
                $createdAt = $now->copy()->subDays($daysAgo)->subHours(rand(0, 23))->subMinutes(rand(0, 59));

                if ($isFraudulent) {
                    // Fraudulent pattern: mostly cancelled_by_artisan, very few completed
                    $status = $this->pickFraudulentStatus($j, $orderCount);
                } else {
                    // Normal artisans: mostly completed, some cancellations
                    $status = $this->pickNormalStatus();
                }

                $scheduledDate = $createdAt->copy()->addDays(rand(1, 14));
                $completedDate = null;
                if ($status === 'completed') {
                    $completedDate = $scheduledDate->copy()->addDays(rand(0, 3));
                }

                $totalPrice = $service->base_price
                    ? (float) $service->base_price + rand(-50, 200)
                    : rand(100, 1000);
                $totalPrice = max(50, $totalPrice);

                Order::create([
                    'artisan_id' => $artisan->id,
                    'customer_id' => $customerId,
                    'service_id' => $service->id,
                    'status' => $status,
                    'total_price' => round($totalPrice, 2),
                    'scheduled_date' => $scheduledDate->toDateString(),
                    'completed_date' => $completedDate?->toDateString(),
                    'created_at' => $createdAt,
                    'updated_at' => $completedDate ?? $createdAt,
                ]);

                $totalOrders++;
            }
        }

        $this->command->info("Order seeding complete: {$totalOrders} orders created.");
        $this->printOrderStats();
    }

    /**
     * Pick a status for a fraudulent artisan's order.
     * Pattern: High cancellation rate by artisan - roughly 60-70% cancelled_by_artisan,
     * a few completed (to have some reviews), and a few others.
     */
    private function pickFraudulentStatus(int $orderIndex, int $totalOrders): string
    {
        // Ensure at least 2 completed orders so reviews can exist
        if ($orderIndex < 2) {
            return 'completed';
        }

        // The rest are heavily weighted towards cancelled_by_artisan
        $roll = rand(1, 100);
        if ($roll <= 65) {
            return 'cancelled_by_artisan';
        }
        if ($roll <= 75) {
            return 'completed';
        }
        if ($roll <= 85) {
            return 'pending';
        }
        if ($roll <= 92) {
            return 'cancelled_by_customer';
        }

        return 'in_progress';
    }

    /**
     * Pick a status for a normal (legitimate) artisan's order.
     * Pattern: ~60-70% completed, small percentage of cancellations.
     */
    private function pickNormalStatus(): string
    {
        $roll = rand(1, 100);

        if ($roll <= 65) {
            return 'completed';
        }
        if ($roll <= 75) {
            return 'in_progress';
        }
        if ($roll <= 82) {
            return 'accepted';
        }
        if ($roll <= 89) {
            return 'pending';
        }
        if ($roll <= 95) {
            return 'cancelled_by_customer';
        }

        // Only ~5% cancelled by artisan for legitimate artisans
        return 'cancelled_by_artisan';
    }

    /**
     * Print summary statistics about generated orders.
     */
    private function printOrderStats(): void
    {
        $total = Order::count();
        $statuses = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->command->info('Order distribution:');
        foreach (self::STATUSES as $status) {
            $count = $statuses[$status] ?? 0;
            $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
            $this->command->line("  {$status}: {$count} ({$pct}%)");
        }
    }
}
