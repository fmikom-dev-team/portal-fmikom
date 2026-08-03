<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'log:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all Laravel log files in storage/logs directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath.'/*.log');

        $count = 0;
        foreach ($files as $file) {
            if (File::isFile($file)) {
                // Truncate or delete log files
                File::put($file, '');
                $count++;
            }
        }

        $this->info("Successfully cleared {$count} Laravel log file(s).");

        return self::SUCCESS;
    }
}
