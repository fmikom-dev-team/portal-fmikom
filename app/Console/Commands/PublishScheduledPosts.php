<?php

namespace App\Console\Commands;

use App\Models\Portal\PortalPost;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'portal:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish scheduled portal posts that reached their publication time';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = PortalPost::where('status', PortalPost::STATUS_SCHEDULED)
            ->where('published_at', '<=', now())
            ->update(['status' => PortalPost::STATUS_PUBLISHED]);

        $this->info("Successfully published {$count} scheduled posts.");
    }
}
