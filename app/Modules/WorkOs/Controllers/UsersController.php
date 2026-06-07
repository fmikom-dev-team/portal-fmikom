<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ModuleRole;
use App\Models\UserModuleRole;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

/**
 * Dedicated UsersController — extracted from DashboardController
 */
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\ProgramStudi;

/**
 * Dedicated UsersController — extracted from DashboardController
 */
class UsersController extends Controller
{
    public function template(Request $request)
    {
        $request->validate([
            'type' => ['required', 'in:mahasiswa,alumni,dosen,mitra'],
            'format' => ['required', 'in:csv,xlsx'],
        ]);

        $type = $request->type;
        $format = $request->format;

        $headers = [];
        if ($type === 'mahasiswa') {
            $headers = ['Nama', 'Email', 'Password', 'NIM', 'Program Studi'];
        } elseif ($type === 'alumni') {
            $headers = ['Nama', 'Email', 'Password', 'NIM', 'Program Studi', 'Tahun Lulus', 'Nomor Telepon'];
        } elseif ($type === 'dosen') {
            $headers = ['Nama', 'Email', 'Password', 'NIP/NIDN', 'Program Studi', 'Nomor Telepon'];
        } elseif ($type === 'mitra') {
            $headers = ['Nama', 'Email', 'Password', 'NIB/Nomor Induk', 'Nama Perusahaan', 'Nomor Telepon'];
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        foreach ($headers as $colIndex => $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter . '1', $header);
            $sheet->getStyle($colLetter . '1')->getFont()->setBold(true);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = 'template_' . $type . '_' . date('YmdHis');

        if ($format === 'xlsx') {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $responseHeader = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '.xlsx"',
            ];
        } else {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Csv($spreadsheet);
            $responseHeader = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '.csv"',
            ];
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName . '.' . $format, $responseHeader);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:1048576'],
            'user_type' => ['required', 'in:mahasiswa,alumni,dosen,mitra'],
        ]);

        $file = $request->file('file');
        $userType = $request->user_type;

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Gagal membaca file. Pastikan format file benar (.csv / .xlsx).']);
        }

        if (count($rows) <= 1) {
            return back()->withErrors(['file' => 'File kosong atau tidak memiliki baris data.']);
        }

        // Get headers from first row
        $headerRow = array_shift($rows);
        $headers = array_map(function ($val) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $val)));
        }, $headerRow);

        // Define column mappings
        $mapping = [];
        foreach ($headers as $colLetter => $headerName) {
            if (in_array($headerName, ['name', 'nama', 'fullname', 'nama lengkap'])) {
                $mapping['name'] = $colLetter;
            } elseif (in_array($headerName, ['email', 'surel'])) {
                $mapping['email'] = $colLetter;
            } elseif (in_array($headerName, ['password', 'kata sandi', 'sandi'])) {
                $mapping['password'] = $colLetter;
            } elseif (in_array($headerName, ['nim', 'nip', 'nidn', 'nib', 'nomor induk', 'id', 'external id'])) {
                $mapping['nomor_induk'] = $colLetter;
            } elseif (in_array($headerName, ['program studi', 'prodi', 'jurusan'])) {
                $mapping['program_studi'] = $colLetter;
            } elseif (in_array($headerName, ['tahun lulus', 'tahun'])) {
                $mapping['tahun_lulus'] = $colLetter;
            } elseif (in_array($headerName, ['nomor telepon', 'no telepon', 'telepon', 'no hp', 'hp', 'telp'])) {
                $mapping['no_telepon'] = $colLetter;
            } elseif (in_array($headerName, ['nama perusahaan', 'perusahaan', 'company'])) {
                $mapping['nama_perusahaan'] = $colLetter;
            }
        }

        // Validate required headers present
        $requiredFields = ['name', 'email'];
        if ($userType === 'mahasiswa' || $userType === 'alumni') {
            $requiredFields[] = 'nomor_induk';
            $requiredFields[] = 'program_studi';
        } elseif ($userType === 'dosen') {
            $requiredFields[] = 'nomor_induk';
        } elseif ($userType === 'mitra') {
            $requiredFields[] = 'nomor_induk';
        }

        $missingHeaders = [];
        foreach ($requiredFields as $field) {
            if (!isset($mapping[$field])) {
                $missingHeaders[] = str_replace('_', ' ', $field);
            }
        }

        if (!empty($missingHeaders)) {
            return back()->withErrors(['file' => 'Kolom wajib berikut tidak ditemukan di file: ' . implode(', ', $missingHeaders)]);
        }

        // Fetch prodi to map names/codes to IDs
        $prodis = ProgramStudi::all();

        $errors = [];
        $validRows = [];
        $emailsInBatch = [];
        $nomorInduksInBatch = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // 1-based index (including header row)

            $name = isset($mapping['name']) ? trim($row[$mapping['name']]) : '';
            $email = isset($mapping['email']) ? trim($row[$mapping['email']]) : '';
            $password = isset($mapping['password']) ? trim($row[$mapping['password']]) : '';
            $nomorInduk = isset($mapping['nomor_induk']) ? trim($row[$mapping['nomor_induk']]) : '';
            $programStudiVal = isset($mapping['program_studi']) ? trim($row[$mapping['program_studi']]) : '';
            $tahunLulusVal = isset($mapping['tahun_lulus']) ? trim($row[$mapping['tahun_lulus']]) : '';
            $noTelepon = isset($mapping['no_telepon']) ? trim($row[$mapping['no_telepon']]) : '';
            $namaPerusahaan = isset($mapping['nama_perusahaan']) ? trim($row[$mapping['nama_perusahaan']]) : '';

            $rowErrors = [];

            // 1. Validate name
            if (empty($name)) {
                $rowErrors[] = 'Nama wajib diisi.';
            }

            // 2. Validate email
            if (empty($email)) {
                $rowErrors[] = 'Email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = 'Format email tidak valid.';
            } elseif (User::where('email', $email)->exists()) {
                $rowErrors[] = 'Email sudah terdaftar di sistem.';
            } elseif (in_array($email, $emailsInBatch)) {
                $rowErrors[] = 'Email duplikat dalam file ini.';
            } else {
                $emailsInBatch[] = $email;
            }

            // 3. Validate nomor induk
            if (in_array('nomor_induk', $requiredFields) && empty($nomorInduk)) {
                $rowErrors[] = 'Nomor Induk wajib diisi.';
            } elseif (!empty($nomorInduk)) {
                if (User::where('nomor_induk', $nomorInduk)->exists()) {
                    $rowErrors[] = 'Nomor Induk sudah terdaftar di sistem.';
                } elseif (in_array($nomorInduk, $nomorInduksInBatch)) {
                    $rowErrors[] = 'Nomor Induk duplikat dalam file ini.';
                } else {
                    $nomorInduksInBatch[] = $nomorInduk;
                }
            }

            // 4. Validate program studi
            $prodiId = null;
            if (!empty($programStudiVal)) {
                $matchedProdi = $prodis->first(function ($p) use ($programStudiVal) {
                    return strtolower($p->kode) === strtolower($programStudiVal) ||
                           strtolower($p->nama) === strtolower($programStudiVal);
                });

                if ($matchedProdi) {
                    $prodiId = $matchedProdi->id;
                } else {
                    $rowErrors[] = 'Program Studi "' . $programStudiVal . '" tidak ditemukan. Pilihan: IF, SI, MTK.';
                }
            } elseif (in_array('program_studi', $requiredFields)) {
                $rowErrors[] = 'Program Studi wajib diisi.';
            }

            // 5. Validate tahun lulus
            $tahunLulus = null;
            if ($userType === 'alumni') {
                if (empty($tahunLulusVal)) {
                    $rowErrors[] = 'Tahun lulus wajib diisi untuk Alumni.';
                } elseif (!is_numeric($tahunLulusVal) || strlen($tahunLulusVal) !== 4) {
                    $rowErrors[] = 'Tahun lulus harus berupa 4 digit angka.';
                } else {
                    $tahunLulus = (int)$tahunLulusVal;
                }
            }

            // If no password provided, generate default based on nomor_induk
            if (empty($password)) {
                $password = 'Fmikom@' . ($nomorInduk ?: rand(1000, 9999));
            }

            if (!empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'email' => $email ?: 'N/A',
                    'errors' => $rowErrors
                ];
            } else {
                $validRows[] = [
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'nomor_induk' => $nomorInduk,
                    'program_studi_id' => $prodiId,
                    'tahun_lulus' => $tahunLulus,
                    'no_telepon' => $noTelepon ?: null,
                    'nama_perusahaan' => $namaPerusahaan ?: null,
                ];
            }
        }

        // If there are validation errors, return back with details
        if (!empty($errors)) {
            return back()->with('import_errors', $errors);
        }

        // Save users
        try {
            DB::transaction(function () use ($validRows, $userType) {
                foreach ($validRows as $row) {
                    $metadata = $row['nama_perusahaan'] ? ['nama_perusahaan' => $row['nama_perusahaan']] : null;

                    $user = User::create([
                        'name' => $row['name'],
                        'email' => $row['email'],
                        'password' => Hash::make($row['password']),
                        'user_type' => $userType,
                        'nomor_induk' => $row['nomor_induk'] ?: null,
                        'program_studi_id' => $row['program_studi_id'],
                        'tahun_lulus' => $row['tahun_lulus'],
                        'no_telepon' => $row['no_telepon'],
                        'status_approval' => 'approved',
                        'is_active' => false,
                        'email_verified_at' => null,
                        'password_changed_at' => null,
                        'metadata' => $metadata,
                    ]);

                    // Assign module roles
                    $roleObj = \App\Models\Role::where('slug', $userType)->first();
                    if ($roleObj) {
                        $defaultModules = [];
                        if ($userType === 'mahasiswa') {
                            $defaultModules = ['FAST', 'PAGI', 'WIMS'];
                        } elseif ($userType === 'alumni') {
                            $defaultModules = ['TRACE', 'PAGI'];
                        } elseif ($userType === 'dosen') {
                            $defaultModules = ['FAST', 'PAGI', 'WIMS'];
                        } elseif ($userType === 'mitra') {
                            $defaultModules = ['WIMS', 'TRACE'];
                        }

                        if (!empty($defaultModules)) {
                            $modules = \App\Models\Module::whereIn('code', $defaultModules)->get();
                            foreach ($modules as $mod) {
                                UserModuleRole::create([
                                    'user_id' => $user->id,
                                    'module_id' => $mod->id,
                                    'role_id' => $roleObj->id,
                                    'is_active' => true,
                                ]);
                            }
                        }
                    }
                }
            });
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()]);
        }

        return back()->with('success', count($validRows) . ' user berhasil diimpor.');
    }

    public function store(Request $request)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->storeUser($request);
    }

    public function update(Request $request, User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->updateUser($request, $user);
    }

    public function destroy(User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->destroyUser($user);
    }

    public function approve(User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->approve($user);
    }

    public function reject(Request $request, User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->reject($request, $user);
    }

    public function assignRole(Request $request, User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->assignRole($request, $user);
    }

    public function addModuleRole(Request $request, User $user)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->addModuleRole($request, $user);
    }

    public function updateModuleRole(Request $request, UserModuleRole $moduleRole)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->updateModuleRole($request, $moduleRole);
    }

    public function removeModuleRole(UserModuleRole $moduleRole)
    {
        return app(\App\Modules\WorkOs\Controllers\DashboardController::class)->removeModuleRole($moduleRole);
    }

    public function disconnectOAuth(User $user, \App\Models\Auth\AuthOAuthCredential $credential)
    {
        if ($credential->user_id !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $credential->delete();

        return back()->with('success', 'Connected account disconnected successfully.');
    }

    public function sessions(User $user)
    {
        // Fetch Laravel's standard active sessions
        $laravelSessions = \DB::table('sessions')
            ->where('user_id', $user->id)
            ->get();

        $activeSessionTokens = $laravelSessions->pluck('id')->toArray();

        // Synchronize with auth_sessions
        foreach ($laravelSessions as $ls) {
            $lastActivityAt = \Carbon\Carbon::createFromTimestamp($ls->last_activity);
            $expiresAt = \Carbon\Carbon::createFromTimestamp($ls->last_activity + config('session.lifetime') * 60);

            \App\Models\Auth\AuthSession::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'session_token' => $ls->id,
                ],
                [
                    'ip_address' => $ls->ip_address,
                    'user_agent' => $ls->user_agent,
                    'is_revoked' => false,
                    'last_activity_at' => $lastActivityAt,
                    'expires_at' => $expiresAt,
                ]
            );
        }

        // Mark sessions no longer in the Laravel sessions table as revoked
        \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->whereNotIn('session_token', $activeSessionTokens)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);

        // Retrieve and return all synced sessions
        $sessions = \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->latest('last_activity_at')
            ->get();

        return response()->json([
            'sessions' => $sessions
        ]);
    }

    public function revokeSession(User $user, $sessionId)
    {
        $session = \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->where('id', $sessionId)
            ->firstOrFail();

        // Delete the corresponding Laravel active session so they are logged out!
        if ($session->session_token) {
            \DB::table('sessions')
                ->where('id', $session->session_token)
                ->delete();
        }

        $session->update(['is_revoked' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Session revoked successfully.'
        ]);
    }

    public function revokeAllSessions(User $user)
    {
        // Fetch active auth_sessions
        $activeSessions = \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->where('is_revoked', false)
            ->get();

        foreach ($activeSessions as $session) {
            if ($session->session_token) {
                \DB::table('sessions')
                    ->where('id', $session->session_token)
                    ->delete();
            }
        }

        \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All active sessions revoked successfully.'
        ]);
    }

    public function clearInactiveSessions(User $user)
    {
        \App\Models\Auth\AuthSession::where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('is_revoked', true)
                    ->orWhere('expires_at', '<', now());
            })
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Inactive sessions cleared successfully.'
        ]);
    }

    public function emails(User $user)
    {
        // Seed some realistic email history if none exists for this user yet
        $logsCount = \App\Models\Auth\AuthEmailLog::where('user_id', $user->id)->count();
        if ($logsCount === 0) {
            $emailsToSeed = [
                [
                    'email' => $user->email,
                    'subject' => 'Verify your email address for WorkOS Platform',
                    'body' => "Hello {$user->name},\n\nPlease verify your email by clicking the link: https://fmikom.suntree.my.id/auth/verify?token=" . bin2hex(random_bytes(16)),
                    'status' => 'Delivered',
                    'created_at' => now()->subDays(12)->subHours(2),
                ],
                [
                    'email' => $user->email,
                    'subject' => 'Welcome to FMIKOM Dev Portal!',
                    'body' => "Welcome {$user->name} to the FMIKOM Developers Portal. Explore our API keys, modules, and role assignments.",
                    'status' => 'Delivered',
                    'created_at' => now()->subDays(12),
                ],
                [
                    'email' => $user->email,
                    'subject' => 'Security Alert: New Sign-in Detected',
                    'body' => "A new sign-in was detected on Chrome (macOS) from IP 182.253.162.88.",
                    'status' => 'Delivered',
                    'created_at' => now()->subDays(3)->subHours(5),
                ],
                [
                    'email' => $user->email,
                    'subject' => 'Password Changed Successfully',
                    'body' => "Hi {$user->name},\n\nYour portal account password was successfully updated. If this wasn't you, please contact support immediately.",
                    'status' => 'Delivered',
                    'created_at' => now()->subHours(18),
                ]
            ];

            foreach ($emailsToSeed as $seed) {
                \App\Models\Auth\AuthEmailLog::create(array_merge($seed, ['user_id' => $user->id]));
            }
        }

        $logs = \App\Models\Auth\AuthEmailLog::where('user_id', $user->id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'emails' => $logs
        ]);
    }

    public function clearEmailHistory(User $user)
    {
        \App\Models\Auth\AuthEmailLog::where('user_id', $user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Email history cleared successfully.'
        ]);
    }
}
