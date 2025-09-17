<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup {--days=7 : Number of days to keep notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old notifications older than specified days (default: 7 days)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');

        $this->info("Cleaning up notifications older than {$days} days...");

        $notificationService = new NotificationService();

        // Use the cleanup method from NotificationService
        $deletedCount = $notificationService->cleanupOldNotifications();

        $this->info("Successfully deleted {$deletedCount} old notifications.");

        return Command::SUCCESS;
    }
}
