<?php

namespace App\Modules\WorkOs\Controllers\Concerns;

use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

trait HasUserImport
{
    private const LITERAL_PROGRAM_STUDI = 'Program Studi';

    private const LITERAL_NOMOR_TELEPON = 'Nomor Telepon';

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
            $headers = ['Nama', 'Email', 'Password', 'NIM', self::LITERAL_PROGRAM_STUDI];
        } elseif ($type === 'alumni') {
            $headers = ['Nama', 'Email', 'Password', 'NIM', self::LITERAL_PROGRAM_STUDI, 'Tahun Lulus', self::LITERAL_NOMOR_TELEPON];
        } elseif ($type === 'dosen') {
            $headers = ['Nama', 'Email', 'Password', 'NIP/NIDN', self::LITERAL_PROGRAM_STUDI, self::LITERAL_NOMOR_TELEPON];
        } elseif ($type === 'mitra') {
            $headers = ['Nama', 'Email', 'Password', 'NIB/Nomor Induk', 'Nama Perusahaan', self::LITERAL_NOMOR_TELEPON];
        }

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $colIndex => $header) {
            $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
            $sheet->setCellValue($colLetter.'1', $header);
            $sheet->getStyle($colLetter.'1')->getFont()->setBold(true);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $fileName = 'template_'.$type.'_'.date('YmdHis');

        if ($format === 'xlsx') {
            $writer = new Xlsx($spreadsheet);
            $responseHeader = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'.xlsx"',
            ];
        } else {
            $writer = new Csv($spreadsheet);
            $responseHeader = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$fileName.'.csv"',
            ];
        }

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName.'.'.$format, $responseHeader);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:1048576'],
            'user_type' => ['required', 'in:mahasiswa,alumni,dosen,mitra'],
        ]);

        $file = $request->file('file');
        $userType = $request->user_type;

        $rows = $this->loadSpreadsheetRows($file);
        if ($rows === null) {
            return back()->withErrors(['file' => 'Gagal membaca file. Pastikan format file benar (.csv / .xlsx).']);
        }

        if (count($rows) <= 1) {
            return back()->withErrors(['file' => 'File kosong atau tidak memiliki baris data.']);
        }

        // Get headers from first row
        $headerRow = array_shift($rows);
        $mapping = $this->extractHeaderMapping($headerRow);

        // Validate required headers present
        $requiredFields = $this->getRequiredFields($userType);

        $missingHeaders = $this->validateRequiredHeaders($mapping, $requiredFields);
        if ($missingHeaders !== null) {
            return back()->withErrors(['file' => 'Kolom wajib berikut tidak ditemukan di file: '.implode(', ', $missingHeaders)]);
        }

        // Fetch prodi to map names/codes to IDs
        $prodis = ProgramStudi::all();

        // BUG-006: Pre-load all existing emails and nomor_induks into sets.
        $existingEmails = User::query()->pluck('email', null)->map('strtolower')->flip()->toArray();
        $existingNomors = User::query()->whereNotNull('nomor_induk', 'and', false)->pluck('nomor_induk', null)->flip()->toArray();

        $errors = [];
        $validRows = [];
        $emailsInBatch = [];
        $nomorInduksInBatch = [];

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // 1-based index (including header row)

            $data = $this->parseRowData($row, $mapping);
            $res = $this->validateRowData($data, [
                'userType' => $userType,
                'requiredFields' => $requiredFields,
                'prodis' => $prodis,
                'existingEmails' => $existingEmails,
                'existingNomors' => $existingNomors,
            ]);

            $rowErrors = $res['errors'];

            // Validate batch duplicates
            if (empty($rowErrors)) {
                $lowerEmail = strtolower($data['email']);
                if (in_array($lowerEmail, $emailsInBatch, true)) {
                    $rowErrors[] = 'Email duplikat dalam file ini.';
                } else {
                    $emailsInBatch[] = $lowerEmail;
                }

                if (! empty($data['nomor_induk'])) {
                    if (in_array($data['nomor_induk'], $nomorInduksInBatch, true)) {
                        $rowErrors[] = 'Nomor Induk duplikat dalam file ini.';
                    } else {
                        $nomorInduksInBatch[] = $data['nomor_induk'];
                    }
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = [
                    'row' => $rowNum,
                    'email' => $data['email'] ?: 'N/A',
                    'errors' => $rowErrors,
                ];
            } else {
                $validRows[] = $res['data'];
            }
        }

        // If there are validation errors, return back with details
        if (! empty($errors)) {
            return back()->with('import_errors', $errors);
        }

        // Save users
        try {
            $this->saveImportedUsers($validRows, $userType);
        } catch (\Exception $e) {
            return back()->withErrors(['file' => 'Terjadi kesalahan saat menyimpan data: '.$e->getMessage()]);
        }

        return back()->with('success', count($validRows).' user berhasil diimpor.');
    }

    private function loadSpreadsheetRows($file): ?array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());

            return $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function extractHeaderMapping(array $headerRow): array
    {
        $headers = array_map(function ($val) {
            return strtolower(trim(preg_replace('/[^a-zA-Z0-9\s]/', '', $val)));
        }, $headerRow);

        $mapping = [];
        foreach ($headers as $colLetter => $headerName) {
            if (in_array($headerName, ['name', 'nama', 'fullname', 'nama lengkap'], true)) {
                $mapping['name'] = $colLetter;
            } elseif (in_array($headerName, ['email', 'surel'], true)) {
                $mapping['email'] = $colLetter;
            } elseif (in_array($headerName, ['password', 'kata sandi', 'sandi'], true)) {
                $mapping['password'] = $colLetter;
            } elseif (in_array($headerName, ['nim', 'nip', 'nidn', 'nib', 'nomor induk', 'id', 'external id'], true)) {
                $mapping['nomor_induk'] = $colLetter;
            } elseif (in_array($headerName, ['program studi', 'prodi', 'jurusan'], true)) {
                $mapping['program_studi'] = $colLetter;
            } elseif (in_array($headerName, ['tahun lulus', 'tahun'], true)) {
                $mapping['tahun_lulus'] = $colLetter;
            } elseif (in_array($headerName, ['nomor telepon', 'no telepon', 'telepon', 'no hp', 'hp', 'telp'], true)) {
                $mapping['no_telepon'] = $colLetter;
            } elseif (in_array($headerName, ['nama perusahaan', 'perusahaan', 'company'], true)) {
                $mapping['nama_perusahaan'] = $colLetter;
            }
        }

        return $mapping;
    }

    private function getRequiredFields(string $userType): array
    {
        $requiredFields = ['name', 'email'];
        if ($userType === 'mahasiswa' || $userType === 'alumni') {
            $requiredFields[] = 'nomor_induk';
            $requiredFields[] = 'program_studi';
        } elseif ($userType === 'dosen') {
            $requiredFields[] = 'nomor_induk';
        } elseif ($userType === 'mitra') {
            $requiredFields[] = 'nomor_induk';
        }

        return $requiredFields;
    }

    private function validateRequiredHeaders(array $mapping, array $requiredFields): ?array
    {
        $missingHeaders = [];
        foreach ($requiredFields as $field) {
            if (! isset($mapping[$field])) {
                $missingHeaders[] = str_replace('_', ' ', $field);
            }
        }

        return $missingHeaders ?: null;
    }

    private function saveImportedUsers(array $validRows, string $userType): void
    {
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
                $user->assignDefaultModuleRoles();
            }
        });
    }

    private function parseRowData(array $row, array $mapping): array
    {
        return [
            'name' => isset($mapping['name']) ? trim($row[$mapping['name']]) : '',
            'email' => isset($mapping['email']) ? trim($row[$mapping['email']]) : '',
            'password' => isset($mapping['password']) ? trim($row[$mapping['password']]) : '',
            'nomor_induk' => isset($mapping['nomor_induk']) ? trim($row[$mapping['nomor_induk']]) : '',
            'program_studi' => isset($mapping['program_studi']) ? trim($row[$mapping['program_studi']]) : '',
            'tahun_lulus' => isset($mapping['tahun_lulus']) ? trim($row[$mapping['tahun_lulus']]) : '',
            'no_telepon' => isset($mapping['no_telepon']) ? trim($row[$mapping['no_telepon']]) : '',
            'nama_perusahaan' => isset($mapping['nama_perusahaan']) ? trim($row[$mapping['nama_perusahaan']]) : '',
        ];
    }

    private function validateRowData(array $data, array $context): array
    {
        $rowErrors = [];
        $requiredFields = $context['requiredFields'];
        $userType = $context['userType'];
        $prodis = $context['prodis'];
        $existingEmails = $context['existingEmails'];
        $existingNomors = $context['existingNomors'];

        if (empty($data['name'])) {
            $rowErrors[] = 'Nama wajib diisi.';
        }

        if (empty($data['email'])) {
            $rowErrors[] = 'Email wajib diisi.';
        } elseif (! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $rowErrors[] = 'Format email tidak valid.';
        } elseif (isset($existingEmails[strtolower($data['email'])])) {
            $rowErrors[] = 'Email sudah terdaftar di sistem.';
        }

        if (in_array('nomor_induk', $requiredFields, true) && empty($data['nomor_induk'])) {
            $rowErrors[] = 'Nomor Induk wajib diisi.';
        } elseif (! empty($data['nomor_induk']) && isset($existingNomors[$data['nomor_induk']])) {
            $rowErrors[] = 'Nomor Induk sudah terdaftar di sistem.';
        }

        $prodiId = null;
        if (! empty($data['program_studi'])) {
            $matchedProdi = $prodis->first(function ($p) use ($data) {
                return strtolower($p->kode) === strtolower($data['program_studi']) ||
                       strtolower($p->nama) === strtolower($data['program_studi']);
            });

            if ($matchedProdi) {
                $prodiId = $matchedProdi->id;
            } else {
                $rowErrors[] = 'Program Studi "'.$data['program_studi'].'" tidak ditemukan. Pilihan: IF, SI, MTK.';
            }
        } elseif (in_array('program_studi', $requiredFields, true)) {
            $rowErrors[] = 'Program Studi wajib diisi.';
        }

        $tahunLulus = null;
        if ($userType === 'alumni') {
            if (empty($data['tahun_lulus'])) {
                $rowErrors[] = 'Tahun lulus wajib diisi untuk Alumni.';
            } elseif (! is_numeric($data['tahun_lulus']) || strlen($data['tahun_lulus']) !== 4) {
                $rowErrors[] = 'Tahun lulus harus berupa 4 digit angka.';
            } else {
                $tahunLulus = (int) $data['tahun_lulus'];
            }
        }

        $password = $data['password'];
        if (empty($password)) {
            $password = 'Fmikom@'.($data['nomor_induk'] ?: rand(1000, 9999));
        }

        return [
            'errors' => $rowErrors,
            'data' => [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $password,
                'nomor_induk' => $data['nomor_induk'],
                'program_studi_id' => $prodiId,
                'tahun_lulus' => $tahunLulus,
                'no_telepon' => $data['no_telepon'] ?: null,
                'nama_perusahaan' => $data['nama_perusahaan'] ?: null,
            ],
        ];
    }
}
