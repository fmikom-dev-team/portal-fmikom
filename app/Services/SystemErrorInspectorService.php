<?php

namespace App\Services;

use App\Models\Audit\AuditSecurityIncident;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class SystemErrorInspectorService
{
    /**
     * Capture and diagnose an exception in real-time.
     */
    public static function captureException(Throwable $e, ?Request $request = null): ?AuditSecurityIncident
    {
        try {
            $request = $request ?? (app()->bound('request') ? request() : null);
            $analysis = self::analyzeException($e, $request);

            $user = Auth::user();
            $userId = $user?->id;
            $ipAddress = $request?->ip() ?? '127.0.0.1';

            // Avoid logging duplicates if the same error occurred within the last 15 seconds
            $recent = AuditSecurityIncident::query()
                ->where('incident_type', $analysis['incident_type'])
                ->where('ip_address', $ipAddress)
                ->where('created_at', '>=', now()->subSeconds(15))
                ->first();

            if ($recent) {
                return $recent;
            }

            $incident = AuditSecurityIncident::create([
                'incident_type' => $analysis['incident_type'],
                'user_id' => $userId,
                'ip_address' => $ipAddress,
                'severity' => $analysis['severity'],
                'details' => $analysis['details'],
                'mitigation_status' => 'open',
            ]);

            // Notify admins via SystemAlertService
            SystemAlertService::log(
                $analysis['incident_type'],
                $analysis['details']['root_cause'] ?? $e->getMessage(),
                $analysis['severity'],
                [
                    'incident_id' => $incident->id,
                    'file' => $analysis['details']['file'].':'.$analysis['details']['line'],
                    'url' => $analysis['details']['url'] ?? '-',
                ]
            );

            return $incident;
        } catch (Throwable $internalError) {
            Log::error('SystemErrorInspectorService failed to capture exception: '.$internalError->getMessage());

            return null;
        }
    }

    /**
     * Analyze the exception and generate smart diagnostics and suggested fixes.
     */
    public static function analyzeException(Throwable $e, ?Request $request = null): array
    {
        $class = get_class($e);
        $shortClass = class_basename($e);
        $message = $e->getMessage();
        $file = Str::replace(base_path().'/', '', $e->getFile());
        $line = $e->getLine();

        $severity = 'critical';
        $incidentType = "HTTP 500: {$shortClass}";
        $rootCause = "Terjadi kegagalan server yang tidak terduga ({$shortClass}).";
        $suggestedFix = 'Periksa detail pesan error dan file sumber yang bersangkutan.';

        // 1. Database Query Exceptions
        if ($e instanceof QueryException) {
            $severity = 'critical';
            $sqlMessage = $e->errorInfo[2] ?? $message;

            if (Str::contains($sqlMessage, ['Unknown column', 'no such column', 'column not found'])) {
                $incidentType = 'Database Error: Kolom Tidak Ditemukan';
                $rootCause = "Query mencoba mengakses kolom database yang belum ada atau salah ketik: \"{$sqlMessage}\"";
                $suggestedFix = 'Jalankan migrasi database (`php artisan migrate`) atau periksa nama kolom pada Model / Query Builder.';
            } elseif (Str::contains($sqlMessage, ['Base table or view not found', 'no such table', 'Table'])) {
                $incidentType = 'Database Error: Tabel Tidak Ditemukan';
                $rootCause = "Tabel database belum dibuat atau nama tabel salah: \"{$sqlMessage}\"";
                $suggestedFix = 'Pastikan seluruh file migrasi telah dijalankan ke database (`php artisan migrate`).';
            } elseif (Str::contains($sqlMessage, ['Duplicate entry', 'UNIQUE constraint failed', 'Integrity constraint violation'])) {
                $incidentType = 'Database Error: Duplikasi Data Unik';
                $rootCause = 'Terjadi pelanggaran constraint database karena data sudah ada sebelumnya (duplicate unique value).';
                $suggestedFix = 'Pastikan nilai kolom unik (seperti email, slug, username) divalidasi sebelum disimpan.';
            } else {
                $incidentType = "Database Error: {$shortClass}";
                $rootCause = "Terjadi kesalahan eksekusi SQL pada database: {$sqlMessage}";
                $suggestedFix = 'Periksa query SQL, tipe data parameter yang dikirimkan, atau koneksi database.';
            }
        }
        // 2. Model Not Found (404)
        elseif ($e instanceof ModelNotFoundException) {
            $severity = 'warning';
            $model = class_basename($e->getModel());
            $incidentType = "Data Tidak Ditemukan (404: {$model})";
            $rootCause = "Record model {$model} yang diminta tidak ditemukan di database (kemungkinan sudah dihapus atau ID tidak valid).";
            $suggestedFix = 'Periksa parameter ID yang dikirim atau gunakan `find()` dengan penanganan kondisi `null` ketimbang `findOrFail()`.';
        }
        // 3. 404 Route Not Found
        elseif ($e instanceof NotFoundHttpException) {
            $severity = 'warning';
            $incidentType = 'HTTP 404: Halaman / Rute Tidak Ditemukan';
            $rootCause = 'Endpoint URL yang diakses tidak terdaftar dalam rute aplikasi.';
            $suggestedFix = 'Periksa URL yang diminta atau daftarkan rute pada file `routes/web.php` / `routes/api.php`.';
        }
        // 4. CSRF / Token Mismatch (419)
        elseif ($e instanceof TokenMismatchException) {
            $severity = 'warning';
            $incidentType = 'HTTP 419: Sesi / Token CSRF Kedaluwarsa';
            $rootCause = 'Sesi web pengguna telah kedaluwarsa atau token CSRF form tidak cocok saat submit POST/PUT.';
            $suggestedFix = 'Minta pengguna memuat ulang halaman (refresh) atau login ulang agar token CSRF diperbarui.';
        }
        // 5. Authorization / 403 Forbidden
        elseif ($e instanceof AccessDeniedHttpException || Str::contains($shortClass, ['Unauthorized', 'AccessDenied', 'Authorization'])) {
            $severity = 'warning';
            $incidentType = 'HTTP 403: Akses Ditolak (Unauthorized)';
            $rootCause = 'Pengguna mencoba mengakses fitur atau endpoint tanpa izin role/permission yang mencukupi.';
            $suggestedFix = 'Periksa izin dan role pengguna pada menu WorkOS Authorization atau middleware rute terkait.';
        }
        // 6. File & Storage Permission Errors
        elseif (Str::contains($message, ['Permission denied', 'is not writable', 'failed to open stream', 'mkdir()'])) {
            $severity = 'high';
            $incidentType = 'File System: Izin Akses Direktori Gagal';
            $rootCause = "Server tidak memiliki izin menulis (*write permission*) pada direktori storage/file target: {$message}";
            $suggestedFix = 'Jalankan `chmod -R 775 storage bootstrap/cache` dan `php artisan storage:link` pada server.';
        }
        // 7. Inertia Invalid JSON Response
        elseif (Str::contains($message, ['Inertia response', 'plain JSON response'])) {
            $severity = 'high';
            $incidentType = 'Inertia Error: Endpoint Mengembalikan JSON Mentah';
            $rootCause = 'Rute navigasi Inertia menerima respons JSON mentah alih-alih `Inertia::render()`.';
            $suggestedFix = "Pastikan controller memeriksa `$request->header('X-Inertia')` dan merender halaman Inertia jika diakses via browser.";
        }
        // 8. General Error / Syntax / Call to undefined
        elseif (Str::contains($message, ['Call to undefined method', 'Call to a member function', 'on null'])) {
            $severity = 'critical';
            $incidentType = 'PHP Error: Pemanggilan Method / Object Null';
            $rootCause = "Kode mencoba memanggil method atau property pada variabel bernilai `null`: {$message}";
            $suggestedFix = "Gunakan operator null-safe (`?->`) atau pastikan objek tidak bernilai null sebelum dipanggil di {$file}:{$line}.";
        }
        // 9. Class / Trait not found
        elseif (Str::contains($message, ['Class', 'not found', 'Interface'])) {
            $severity = 'critical';
            $incidentType = 'PHP Error: Class / Interface Tidak Ditemukan';
            $rootCause = "Aplikasi mencoba menggunakan class/library yang belum di-import atau belum diinstall: {$message}";
            $suggestedFix = 'Periksa namespace `use ...` atau jalankan `composer dump-autoload` / `composer install`.';
        }

        // Clean stack trace
        $cleanTrace = [];
        $rawTrace = $e->getTrace();
        $count = 0;
        foreach ($rawTrace as $frame) {
            if ($count >= 12) {
                break;
            }
            $frameFile = isset($frame['file']) ? Str::replace(base_path().'/', '', $frame['file']) : '[internal function]';
            $frameLine = $frame['line'] ?? 0;
            $frameClass = $frame['class'] ?? '';
            $frameType = $frame['type'] ?? '';
            $frameFunction = $frame['function'] ?? '';

            $isAppCode = Str::startsWith($frameFile, ['app/', 'resources/', 'routes/']);

            $cleanTrace[] = [
                'file' => $frameFile,
                'line' => $frameLine,
                'call' => $frameClass ? "{$frameClass}{$frameType}{$frameFunction}()" : "{$frameFunction}()",
                'is_app' => $isAppCode,
            ];
            $count++;
        }

        // Sanitize request payload
        $sanitizedPayload = [];
        if ($request) {
            $payload = $request->except(['password', 'password_confirmation', 'token', 'secret', 'client_secret', 'remember_token', '_token']);
            // Limit payload size
            $sanitizedPayload = array_slice($payload, 0, 25);
        }

        return [
            'severity' => $severity,
            'incident_type' => $incidentType,
            'details' => [
                'exception_class' => $class,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'url' => $request ? $request->fullUrl() : null,
                'method' => $request ? $request->method() : null,
                'route_name' => $request?->route() ? $request->route()->getName() : null,
                'ip' => $request ? $request->ip() : '127.0.0.1',
                'user_agent' => $request ? $request->userAgent() : null,
                'payload' => $sanitizedPayload,
                'root_cause' => $rootCause,
                'suggested_fix' => $suggestedFix,
                'stack_trace' => $cleanTrace,
            ],
        ];
    }
}
