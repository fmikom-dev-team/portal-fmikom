<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PruneOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:prune {--days=90 : Number of days of notifications to keep}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically delete notifications older than 90 days (3 months)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $deletedCount = DB::table('notifications')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Successfully pruned {$deletedCount} notifications older than {$days} days.");
        Log::info("Auto-pruned {$deletedCount} notifications older than {$days} days.");

        return Command::SUCCESS;
    }
}
