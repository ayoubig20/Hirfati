<?php

namespace Database\Seeders;

use App\Models\Artisan;
use App\Models\User;
use App\Services\TrustCalculatorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Execution order:
     * 1. ArtisanSeeder - Creates 50 artisans (with user accounts), 20 customer users, and services
     * 2. OrderSeeder   - Creates 200+ orders with varied statuses and completion rates
     * 3. ReviewSeeder  - Creates 500+ reviews distributed over the past 90 days
     *
     * After all seeders run, the TrustCalculatorService recalculates trust scores
     * for every active artisan, applying temporal decay, completion rate analysis,
     * and the sigmoid fraud penalty.
     */
    public function run(): void
    {
        $this->command->info('=== Hirfati Artisan Trust Platform - Database Seeding ===');
        $this->command->newLine();

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@hirfati.ma'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );
        $this->command->info("Admin user created: admin@hirfati.ma / password");

        $this->call([
            ArtisanSeeder::class,
            OrderSeeder::class,
            ReviewSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('=== Recalculating Trust Scores for All Artisans ===');

        $trustService = app(TrustCalculatorService::class);
        $results = $trustService->batchRecalculate();

        $this->command->info('Trust scores recalculated for ' . count($results) . ' artisans.');
        $this->command->newLine();

        // Display a summary table of trust score results
        $this->command->info('Trust Score Summary:');
        $this->command->line(str_pad('ID', 5) . str_pad('Name', 30) . str_pad('T_score', 10) . str_pad('S_r', 10) . str_pad('S_c', 10) . str_pad('Penalty', 10) . 'Tier');
        $this->command->line(str_repeat('-', 85));

        $artisans = Artisan::orderBy('trust_score', 'desc')->get();

        foreach ($artisans as $artisan) {
            $result = $results[$artisan->id] ?? null;
            if (! $result) {
                continue;
            }

            $tier = $artisan->fresh()->trust_tier;
            $line = str_pad((string) $artisan->id, 5)
                . str_pad(mb_substr($artisan->name, 0, 28), 30)
                . str_pad(number_format($result['T_new'], 4), 10)
                . str_pad(number_format($result['S_r'], 4), 10)
                . str_pad(number_format($result['S_c'], 4), 10)
                . str_pad(number_format($result['P'], 4), 10)
                . strtoupper($tier);

            $this->command->line("  {$line}");
        }

        $this->command->newLine();

        // Highlight fraudulent artisans
        $this->command->warn('Fraudulent artisan analysis (high rating, low completion, high penalty):');
        $flagged = Artisan::whereHas('trustCache', function ($q) {
            $q->where('penalty', '>', 0.5);
        })->with('trustCache')->get();

        if ($flagged->isEmpty()) {
            $this->command->line('  No artisans flagged with penalty > 0.5');
        } else {
            foreach ($flagged as $artisan) {
                $cache = $artisan->trustCache;
                $this->command->line(
                    "  #{$artisan->id} {$artisan->name}: "
                    . "trust={$artisan->trust_score}, "
                    . "rating_comp=" . number_format((float) $cache->rating_component, 4) . ', '
                    . "completion_comp=" . number_format((float) $cache->completion_component, 4) . ', '
                    . "penalty=" . number_format((float) $cache->penalty, 4) . ', '
                    . "reviews={$cache->review_count}, orders={$cache->order_count}"
                );
            }
        }

        $this->command->newLine();
        $this->command->info('=== Seeding Complete ===');
    }
}
