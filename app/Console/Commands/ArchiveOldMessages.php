<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ArchiveOldMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pagi:chat:archive {--days=90 : Umur maksimal pesan chat aktif dalam hari}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pindahkan pesan chat lama dari Hot Storage (pagi_messages) ke Cold Storage (pagi_messages_archive)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Memulai pengarsipan pesan chat Pagi sebelum tanggal: {$cutoffDate->toDateTimeString()}");

        // Hitung total pesan yang memenuhi syarat
        $totalMessages = DB::table('pagi_messages')
            ->where('created_at', '<', $cutoffDate)
            ->count();

        if ($totalMessages === 0) {
            $this->info('Tidak ada pesan lama yang perlu diarsipkan.');

            return Command::SUCCESS;
        }

        $this->info("Menemukan {$totalMessages} pesan untuk dipindahkan...");

        // Jalankan chunked transfer untuk menghindari penguncian tabel database yang lama
        $chunkSize = 1000;
        $archivedCount = 0;

        DB::table('pagi_messages')
            ->where('created_at', '<', $cutoffDate)
            ->orderBy('id')
            ->chunk($chunkSize, function ($messages) use (&$archivedCount) {
                DB::transaction(function () use ($messages, &$archivedCount) {
                    $insertData = [];
                    $idsToDelete = [];

                    foreach ($messages as $msg) {
                        $insertData[] = [
                            'id' => $msg->id,
                            'conversation_id' => $msg->conversation_id,
                            'sender_id' => $msg->sender_id,
                            'receiver_id' => $msg->receiver_id,
                            'parent_id' => $msg->parent_id,
                            'body' => $msg->body,
                            'reactions' => $msg->reactions,
                            'read_at' => $msg->read_at,
                            'created_at' => $msg->created_at,
                            'updated_at' => $msg->updated_at,
                            'archived_at' => Carbon::now(),
                        ];
                        $idsToDelete[] = $msg->id;
                    }

                    // Bulk insert ke Cold Storage Archive
                    DB::table('pagi_messages_archive')->insert($insertData);

                    // Bulk delete dari Hot Storage Active
                    DB::table('pagi_messages')->whereIn('id', $idsToDelete)->delete();

                    $archivedCount += count($idsToDelete);
                });

                $this->info("Berhasil mengarsipkan {$archivedCount} pesan...");
            });

        $this->info("Proses pengarsipan chat berhasil diselesaikan. Total {$archivedCount} pesan dipindahkan.");

        return Command::SUCCESS;
    }
}
