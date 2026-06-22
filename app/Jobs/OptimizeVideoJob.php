<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OptimizeVideoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600; // 10 minutes maximum for heavy video transcode

    protected string $filePath;

    protected string $ext;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, string $ext)
    {
        $this->filePath = $filePath;
        $this->ext = $ext;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $targetPath = storage_path('app/public/'.$this->filePath);
        if (! file_exists($targetPath)) {
            Log::warning("[OptimizeVideoJob] File not found for optimization: {$targetPath}");

            return;
        }

        $ffmpegPath = '/opt/homebrew/bin/ffmpeg';
        if (! file_exists($ffmpegPath)) {
            $ffmpegPath = 'ffmpeg';
        }

        $tempDest = dirname($targetPath).'/tmp_'.uniqid('vid_', true).'.'.$this->ext;
        $escapedSource = escapeshellarg($targetPath);
        $escapedTempDest = escapeshellarg($tempDest);

        if (strtolower($this->ext) === 'webm') {
            $videoCodec = 'libvpx -crf 32 -b:v 1M -deadline realtime -cpu-used 4';
            $audioCodec = 'libopus';
        } else {
            // General mp4 compatible compression
            $videoCodec = 'libx264 -crf 28 -preset faster';
            $audioCodec = 'aac';
        }

        $command = "{$ffmpegPath} -y -i {$escapedSource} -c:v {$videoCodec} -c:a {$audioCodec} {$escapedTempDest} 2>&1";

        Log::info("[OptimizeVideoJob] Starting video compression for {$this->filePath}");

        exec($command, $output, $resultCode);

        if ($resultCode === 0 && file_exists($tempDest) && filesize($tempDest) > 0) {
            rename($tempDest, $targetPath);
            Log::info("[OptimizeVideoJob] Video compressed successfully: {$this->filePath}");
        } else {
            Log::error("[OptimizeVideoJob] Video compression failed for {$this->filePath}. Output: ".implode("\n", $output));
            if (file_exists($tempDest)) {
                @unlink($tempDest);
            }
        }
    }
}
