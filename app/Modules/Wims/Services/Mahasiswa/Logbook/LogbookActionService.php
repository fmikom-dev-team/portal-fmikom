<?php

namespace App\Modules\Wims\Services\Mahasiswa\Logbook;

use App\Models\Magang\LogbookMagang;
use App\Models\Magang\LogbookPhoto;
use App\Models\Magang\PendaftaranMagang;
use App\Support\WimsStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class LogbookActionService
{
    public function alreadySubmittedToday(PendaftaranMagang $pendaftaran): bool
    {
        return LogbookMagang::query()
            ->where('pendaftaran_id', $pendaftaran->id)
            ->whereDate('tanggal', now()->toDateString())
            ->exists();
    }

    public function create(PendaftaranMagang $pendaftaran, array $attributes, array $photos = []): void
    {
        $storedPaths = [];

        try {
            DB::transaction(function () use ($pendaftaran, $attributes, $photos, &$storedPaths): void {
                $logbook = LogbookMagang::create([
                    'pendaftaran_id' => $pendaftaran->id,
                    'tanggal' => now()->toDateString(),
                    'jam_mulai' => $attributes['jam_mulai'],
                    'jam_selesai' => $attributes['jam_selesai'],
                    'aktivitas_harian' => $attributes['aktivitas_harian'],
                    'kompetensi_dicapai' => $attributes['kompetensi_dicapai'],
                    'status' => 'pending',
                ]);

                foreach ($photos as $photo) {
                    if (! $photo instanceof UploadedFile) {
                        continue;
                    }

                    $path = $this->storePhoto($photo);
                    $storedPaths[] = $path;

                    LogbookPhoto::create([
                        'logbook_id' => $logbook->id,
                        'file_path' => $path,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                WimsStorage::delete($storedPaths);
            }

            throw $exception;
        }
    }

    public function updateRevision(LogbookMagang $logbook, array $attributes, bool $replacePhotos, array $photos = []): void
    {
        $storedPaths = [];
        $deletedPaths = [];

        try {
            DB::transaction(function () use ($logbook, $attributes, $replacePhotos, $photos, &$storedPaths, &$deletedPaths): void {
                $logbook->update([
                    'jam_mulai' => $attributes['jam_mulai'],
                    'jam_selesai' => $attributes['jam_selesai'],
                    'aktivitas_harian' => $attributes['aktivitas_harian'],
                    'kompetensi_dicapai' => $attributes['kompetensi_dicapai'],
                    'status' => 'pending',
                    'reviewed_by_mitra_user_id' => null,
                    'reviewed_by_mitra_at' => null,
                ]);

                if (! $replacePhotos) {
                    return;
                }

                $existingPhotos = $logbook->photos()->get();
                $deletedPaths = $existingPhotos
                    ->pluck('file_path')
                    ->filter()
                    ->values()
                    ->all();

                $logbook->photos()->delete();

                foreach ($photos as $photo) {
                    if (! $photo instanceof UploadedFile) {
                        continue;
                    }

                    $path = $this->storePhoto($photo);
                    $storedPaths[] = $path;

                    LogbookPhoto::create([
                        'logbook_id' => $logbook->id,
                        'file_path' => $path,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            if ($storedPaths !== []) {
                WimsStorage::delete($storedPaths);
            }

            throw $exception;
        }

        if ($deletedPaths !== []) {
            WimsStorage::delete($deletedPaths);
        }
    }

    private function storePhoto(UploadedFile $photo): string
    {
        $directory = 'logbook';
        $extension = strtolower($photo->getClientOriginalExtension() ?: $photo->extension() ?: 'bin');
        $filename = Str::uuid() . '.' . $extension;

        WimsStorage::storeUploadedFileAs($photo, $directory, $filename);

        return $directory . '/' . $filename;
    }
}
