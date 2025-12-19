<?php

namespace App\Console\Commands;

use App\Models\Reminder;
use App\Jobs\SendReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send scheduled WhatsApp reminders that are due';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for due reminders...');

        // Get all pending reminders that are due (within 5-minute window)
        $dueReminders = Reminder::pending()
            ->due()
            ->with(['enrollment.event', 'schedule'])
            ->get();

        if ($dueReminders->isEmpty()) {
            $this->info('No reminders due at this time.');
            return self::SUCCESS;
        }

        $this->info("Found {$dueReminders->count()} reminder(s) to send.");

        $successCount = 0;
        $errorCount = 0;

        foreach ($dueReminders as $reminder) {
            try {
                // Dispatch job to send reminder
                SendReminder::dispatch($reminder);

                $this->line("✓ Queued: {$reminder->reminder_type} for {$reminder->phone_number}");
                $successCount++;

                Log::info('Reminder queued for sending', [
                    'reminder_id' => $reminder->id,
                    'type' => $reminder->reminder_type,
                    'scheduled_for' => $reminder->scheduled_for,
                ]);

            } catch (\Exception $e) {
                $this->error("✗ Failed to queue reminder #{$reminder->id}: {$e->getMessage()}");
                $errorCount++;

                Log::error('Failed to queue reminder', [
                    'reminder_id' => $reminder->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info("Summary:");
        $this->info("  Queued: {$successCount}");
        if ($errorCount > 0) {
            $this->warn("  Failed: {$errorCount}");
        }

        return self::SUCCESS;
    }
}
