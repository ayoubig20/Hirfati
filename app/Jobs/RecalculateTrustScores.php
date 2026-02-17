<?php

namespace App\Jobs;

use App\Services\TrustCalculatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RecalculateTrustScores implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ?int $artisanId;

    public function __construct(?int $artisanId = null)
    {
        $this->artisanId = $artisanId;
    }

    public function handle(TrustCalculatorService $calculator): void
    {
        if ($this->artisanId) {
            $result = $calculator->updateTrustScore($this->artisanId);
            Log::info("Trust score recalculated for artisan {$this->artisanId}", $result);
        } else {
            $results = $calculator->batchRecalculate();
            Log::info('Batch trust recalculation completed', [
                'artisans_processed' => count($results),
            ]);
        }
    }
}
