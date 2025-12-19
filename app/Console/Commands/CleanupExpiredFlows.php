<?php

namespace App\Console\Commands;

use App\Services\WhatsApp\FlowManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredFlows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flows:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired conversation flows';

    /**
     * Execute the console command.
     */
    public function handle(FlowManager $flowManager): int
    {
        $this->info('Cleaning up expired conversation flows...');

        try {
            $deletedCount = $flowManager->cleanupExpiredFlows();

            if ($deletedCount > 0) {
                $this->info("✓ Cleaned up {$deletedCount} expired conversation flow(s).");

                Log::info('Expired flows cleaned up', [
                    'count' => $deletedCount,
                ]);
            } else {
                $this->info('No expired flows found.');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("✗ Failed to clean up flows: {$e->getMessage()}");

            Log::error('Flow cleanup failed', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
