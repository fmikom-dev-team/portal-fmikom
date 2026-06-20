<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class VirusScannerService
{
    /**
     * Scan an uploaded file for viruses using ClamAV.
     *
     * @return array ['safe' => bool, 'reason' => ?string]
     */
    public function scan(UploadedFile $file): array
    {
        $enabled = env('CLAMAV_ENABLED', false);
        $host = env('CLAMAV_HOST', '127.0.0.1');
        $port = env('CLAMAV_PORT', 3310);
        $timeout = 2; // Short timeout to check availability first

        $socketUri = "tcp://{$host}:{$port}";
        $socket = @stream_socket_client($socketUri, $errno, $errstr, $timeout);

        if (! $socket) {
            if ($enabled) {
                Log::error("[VirusScannerService] Connection to ClamAV daemon failed: {$errstr} ({$errno}) at {$socketUri}");

                return [
                    'safe' => false,
                    'reason' => 'Layanan pemindaian antivirus (ClamAV) offline. Unggah berkas dibatalkan demi keamanan.',
                ];
            }

            Log::warning('[VirusScannerService] ClamAV is disabled in .env and offline. Skipping scan for file: '.$file->getClientOriginalName());

            return [
                'safe' => true,
                'reason' => 'ClamAV disabled and offline',
            ];
        }

        $timeout = 10; // Restore standard timeout for the scanning process

        Log::info('[VirusScannerService] Starting scan for file: '.$file->getClientOriginalName());

        try {
            // Set read/write timeout on socket
            stream_set_timeout($socket, $timeout);

            // Initiate INSTREAM mode (using z-prefixed command terminated with null byte)
            fwrite($socket, "zINSTREAM\0");

            $fileHandle = fopen($file->getRealPath(), 'rb');
            if (! $fileHandle) {
                return [
                    'safe' => false,
                    'reason' => 'Gagal membuka berkas untuk pemindaian.',
                ];
            }

            $chunkSize = 32768; // 32KB
            while (! feof($fileHandle)) {
                $chunk = fread($fileHandle, $chunkSize);
                $size = strlen($chunk);

                if ($size > 0) {
                    // Send 4-byte chunk length in big-endian (network byte order), followed by the chunk
                    fwrite($socket, pack('N', $size));
                    fwrite($socket, $chunk);
                }
            }
            fclose($fileHandle);

            // Terminate stream with a 4-byte zero length indicator
            fwrite($socket, pack('N', 0));

            // Read scan response
            $response = stream_get_line($socket, 1024, "\n");

            // Check for timeout or stream failure
            $info = stream_get_meta_data($socket);
            if ($info['timed_out']) {
                Log::error('[VirusScannerService] Connection to ClamAV timed out during scan.');

                return [
                    'safe' => false,
                    'reason' => 'Proses pemindaian antivirus timed out.',
                ];
            }

            fclose($socket);

            $response = trim($response);
            Log::info("[VirusScannerService] Response: {$response}");

            if (str_contains($response, 'OK')) {
                return [
                    'safe' => true,
                    'reason' => 'Safe',
                ];
            }

            if (str_contains($response, 'FOUND')) {
                // Parse virus name from response (e.g. stream: Eicar-Signature FOUND)
                $parts = explode('FOUND', $response);
                $virusName = trim(str_replace('stream:', '', $parts[0]));
                Log::warning("[VirusScannerService] Virus detected in upload: {$virusName}");

                return [
                    'safe' => false,
                    'reason' => "Ancaman terdeteksi oleh antivirus: {$virusName}",
                ];
            }

            Log::error("[VirusScannerService] Unexpected ClamAV response: {$response}");

            return [
                'safe' => false,
                'reason' => 'Respons pemindaian antivirus tidak valid.',
            ];

        } catch (\Throwable $e) {
            Log::error('[VirusScannerService] Exception during scan: '.$e->getMessage());
            if (is_resource($socket)) {
                fclose($socket);
            }

            return [
                'safe' => false,
                'reason' => 'Terjadi kesalahan sistem saat memindai berkas.',
            ];
        }
    }
}
