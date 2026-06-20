<?php

namespace App\Modules\Fast\DTOs;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SuratDataContract
{
    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function fieldSourceOptions(): array
    {
        return [
            ['value' => 'data_pemohon', 'label' => 'Data Pemohon'],
            ['value' => 'data_kampus', 'label' => 'Data Kampus'],
            ['value' => 'data_sistem', 'label' => 'Data Sistem'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function fieldEditableRoleOptions(): array
    {
        return [
            ['value' => 'mahasiswa', 'label' => 'Mahasiswa'],
            ['value' => 'admin', 'label' => 'Admin'],
            ['value' => 'sistem', 'label' => 'Sistem'],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function fieldFormModeOptions(): array
    {
        return [
            ['value' => 'editable', 'label' => 'Bisa diisi pemohon'],
            ['value' => 'readonly', 'label' => 'Hanya dibaca pemohon'],
            ['value' => 'hidden', 'label' => 'Tidak tampil di form pemohon'],
        ];
    }

    public static function fieldSourceLabel(string $source): string
    {
        return match ($source) {
            'data_kampus' => 'Data Kampus',
            'data_sistem' => 'Data Sistem',
            default => 'Data Pemohon',
        };
    }

    public static function fieldEditableRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Admin',
            'sistem' => 'Sistem',
            default => 'Mahasiswa',
        };
    }

    public static function fieldFormModeLabel(string $mode): string
    {
        return match ($mode) {
            'readonly' => 'Hanya dibaca pemohon',
            'hidden' => 'Tidak tampil di form pemohon',
            default => 'Bisa diisi pemohon',
        };
    }

    public static function inferFieldSource(string $name, ?string $type = null): string
    {
        $key = strtolower(trim($name));

        if (preg_match('/^(nomor_sk_|ipk_akhir|akreditasi|nama_penandatangan|nik_penandatangan|jabatan_penandatangan)/', $key)) {
            return 'data_kampus';
        }

        if (preg_match('/^(nomor_surat|tanggal_surat|kota_surat|status|tanggal_pengajuan|tanggal_selesai)/', $key)) {
            return 'data_sistem';
        }

        if ($type !== null && in_array(strtolower($type), ['recipient'], true)) {
            return 'data_kampus';
        }

        return 'data_pemohon';
    }

    public static function inferFieldEditableRole(string $source, ?string $mode = null): string
    {
        if ($mode === 'hidden') {
            return 'sistem';
        }

        return match ($source) {
            'data_kampus' => 'admin',
            'data_sistem' => 'sistem',
            default => 'mahasiswa',
        };
    }

    public static function inferFieldFormMode(string $source): string
    {
        return match ($source) {
            'data_kampus' => 'readonly',
            'data_sistem' => 'hidden',
            default => 'editable',
        };
    }

    /**
     * @param  array<string, mixed>  $field
     * @return array<string, mixed>
     */
    public static function normalizeDynamicFieldConfigItem(array $field): array
    {
        $name = trim((string) ($field['name'] ?? ''));
        $label = trim((string) ($field['label'] ?? '')) ?: $name;
        $type = strtolower(trim((string) ($field['type'] ?? 'text')));

        $source = strtolower(trim((string) ($field['sumber_data'] ?? $field['source_type'] ?? '')));
        if (! in_array($source, ['data_pemohon', 'data_kampus', 'data_sistem'], true)) {
            $source = static::inferFieldSource($name, $type);
        }

        $mode = strtolower(trim((string) ($field['mode_form_pemohon'] ?? $field['applicant_mode'] ?? '')));
        if (! in_array($mode, ['editable', 'readonly', 'hidden'], true)) {
            $mode = static::inferFieldFormMode($source);
        }

        $editableRole = strtolower(trim((string) ($field['editable_role'] ?? '')));
        if (! in_array($editableRole, ['mahasiswa', 'admin', 'sistem'], true)) {
            $editableRole = static::inferFieldEditableRole($source, $mode);
        }

        return [
            'name' => $name,
            'label' => $label,
            'type' => $type ?: 'text',
            'required' => (bool) ($field['required'] ?? false),
            'placeholder' => (string) ($field['placeholder'] ?? ''),
            'help' => (string) ($field['help'] ?? ''),
            'options' => is_array($field['options'] ?? null) ? $field['options'] : [],
            'repeatable' => (bool) ($field['repeatable'] ?? false) || $type === 'repeatable',
            'add_label' => (string) ($field['add_label'] ?? 'Tambah'),
            'item_label' => (string) ($field['item_label'] ?? 'Item'),
            'sumber_data' => $source,
            'editable_role' => $editableRole,
            'mode_form_pemohon' => $mode,
        ];
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    public static function normalizeDynamicFieldConfig(array $fieldConfig): array
    {
        return collect($fieldConfig)
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->map(fn (array $field): array => static::normalizeDynamicFieldConfigItem($field))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    public static function applicantVisibleFieldConfig(array $fieldConfig): array
    {
        return collect(static::normalizeDynamicFieldConfig($fieldConfig))
            ->reject(fn (array $field): bool => (string) ($field['mode_form_pemohon'] ?? 'editable') === 'hidden')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    public static function applicantEditableFieldConfig(array $fieldConfig): array
    {
        return collect(static::normalizeDynamicFieldConfig($fieldConfig))
            ->filter(fn (array $field): bool => (string) ($field['mode_form_pemohon'] ?? 'editable') === 'editable')
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function accountBoundFieldKeys(): array
    {
        return [
            'nama_pemohon',
            'nama_mahasiswa',
            'nama_dosen',
            'nama_kaprodi',
            'nama_dekan',
            'nim_mahasiswa',
            'nip_dosen',
            'nip_kaprodi',
            'nip_dekan',
            'nomor_induk_mahasiswa',
            'nomor_induk_dosen',
            'nomor_induk_kaprodi',
            'nomor_induk_dekan',
            'program_studi_mahasiswa',
            'program_studi_dosen',
            'program_studi_kaprodi',
            'program_studi_dekan',
            'email_pemohon',
            'nim_pemohon',
            'nomor_induk_pemohon',
            'program_studi_pemohon',
            'telepon_pemohon',
            'nama_penanda_tangan',
            'email_penanda_tangan',
            'nik_penanda_tangan',
            'nomor_induk_penanda_tangan',
            'jabatan_penanda_tangan',
            'program_studi_penanda_tangan',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function signerBoundFieldKeys(): array
    {
        return [
            'nama_penanda_tangan',
            'email_penanda_tangan',
            'nik_penanda_tangan',
            'nomor_induk_penanda_tangan',
            'jabatan_penanda_tangan',
            'program_studi_penanda_tangan',
            'nama_kaprodi',
            'nip_kaprodi',
            'nomor_induk_kaprodi',
            'program_studi_kaprodi',
            'nama_dekan',
            'nip_dekan',
            'nomor_induk_dekan',
            'program_studi_dekan',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function adminManualFieldDefaults(): array
    {
        return [
            'perihal' => '',
            'lampiran_keterangan' => '',
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function adminManualArrayFields(): array
    {
        return ['kepada_yth'];
    }

    /**
     * @return array<int, string>
     */
    public static function adminManualScalarFields(): array
    {
        return array_keys(static::adminManualFieldDefaults());
    }

    /**
     * @return array<int, string>
     */
    public static function adminManualDataKeys(): array
    {
        return [
            ...static::adminManualScalarFields(),
            ...static::adminManualArrayFields(),
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function adminManualValidationRules(): array
    {
        return [
            'perihal' => ['nullable', 'string', 'max:255'],
            'lampiran_keterangan' => ['nullable', 'string', 'max:255'],
            'kepada_yth' => ['nullable', 'array'],
            'kepada_yth.*' => ['string', 'max:255'],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function extractManualDataFromValidatedPayload(array $validated): array
    {
        $manual = Arr::only($validated, static::adminManualDataKeys());

        foreach (['form_data', 'data'] as $nestedKey) {
            $nestedPayload = Arr::get($validated, $nestedKey);

            if (! is_array($nestedPayload)) {
                continue;
            }

            foreach (static::adminManualDataKeys() as $fieldKey) {
                if (array_key_exists($fieldKey, $manual)) {
                    continue;
                }

                if (array_key_exists($fieldKey, $nestedPayload)) {
                    $manual[$fieldKey] = $nestedPayload[$fieldKey];
                }
            }
        }

        return $manual;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function mergeManualDataIntoDynamicPayload(array $dynamicPayload, array $sourcePayload = []): array
    {
        return array_replace(
            $dynamicPayload,
            static::extractManualDataFromValidatedPayload($sourcePayload),
        );
    }

    /**
     * @param  array<string, mixed>  $field
     */
    public static function isReservedManualFieldConfig(array $field): bool
    {
        $name = strtolower(trim((string) ($field['name'] ?? '')));
        $type = strtolower(trim((string) ($field['type'] ?? '')));

        return $type === 'recipient'
            || in_array($name, static::adminManualDataKeys(), true)
            || in_array($name, static::signerBoundFieldKeys(), true);
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    public static function filterDynamicFieldConfig(array $fieldConfig): array
    {
        return collect($fieldConfig)
            ->filter(fn ($field): bool => is_array($field) && filled($field['name'] ?? null))
            ->reject(fn (array $field): bool => static::isReservedManualFieldConfig($field))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @param  array<string, mixed>  $payload
     * @return array<string, string> field_name => label
     */
    public static function missingRequiredCampusFields(array $fieldConfig, array $payload): array
    {
        $missing = [];

        foreach (static::normalizeDynamicFieldConfig($fieldConfig) as $field) {
            if ((string) ($field['sumber_data'] ?? 'data_pemohon') !== 'data_kampus') {
                continue;
            }

            if (! (bool) ($field['required'] ?? false)) {
                continue;
            }

            $fieldName = (string) ($field['name'] ?? '');
            if ($fieldName === '') {
                continue;
            }

            $value = Arr::get($payload, $fieldName);

            if (! static::isFieldValueFilledForValidation($value, (string) ($field['type'] ?? 'text'))) {
                $missing[$fieldName] = (string) ($field['label'] ?? $fieldName);
            }
        }

        return $missing;
    }

    protected static function isFieldValueFilledForValidation(mixed $value, string $type): bool
    {
        $type = strtolower(trim($type));

        if ($value === null) {
            return false;
        }

        if ($type === 'checkbox') {
            if (is_bool($value)) {
                return $value;
            }

            if (is_numeric($value)) {
                return (int) $value === 1;
            }

            $normalized = strtolower(trim((string) $value));

            return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        if (in_array($type, ['repeatable', 'checkbox-group', 'multiselect'], true)) {
            if (is_array($value)) {
                return collect($value)
                    ->flatten()
                    ->contains(fn ($item): bool => filled($item));
            }

            if (is_string($value)) {
                $decoded = json_decode($value, true);

                if (is_array($decoded)) {
                    return collect($decoded)
                        ->flatten()
                        ->contains(fn ($item): bool => filled($item));
                }
            }

            return filled($value);
        }

        if (is_array($value)) {
            return collect($value)
                ->flatten()
                ->contains(fn ($item): bool => filled($item));
        }

        return filled($value);
    }

    /**
     * @param  array<string, mixed>|null  $field
     * @return array{label: string, source_type: string, source_key: string, is_required: bool, default_value: mixed, description: string}
     */
    public static function placeholderDefinition(string $key, ?array $field = null): array
    {
        $common = [
            'kop_logo_data_uri' => ['label' => 'Logo Kop Surat', 'source_type' => 'system', 'source_key' => 'kop_logo_data_uri', 'is_required' => true, 'default_value' => null, 'description' => 'Logo kop surat.'],
            'nomor_surat' => ['label' => 'Nomor Surat', 'source_type' => 'surat', 'source_key' => 'nomor_surat', 'is_required' => true, 'default_value' => null, 'description' => 'Nomor surat final yang digenerate sistem.'],
            'keperluan' => ['label' => 'Keperluan', 'source_type' => 'surat', 'source_key' => 'keperluan', 'is_required' => false, 'default_value' => null, 'description' => 'Keperluan surat.'],
            'tanggal_pengajuan' => ['label' => 'Tanggal Pengajuan', 'source_type' => 'surat', 'source_key' => 'tanggal_pengajuan', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal pengajuan surat.'],
            'tanggal_kebutuhan' => ['label' => 'Tanggal Kebutuhan', 'source_type' => 'surat', 'source_key' => 'tanggal_kebutuhan', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal kebutuhan surat.'],
            'tanggal_selesai' => ['label' => 'Tanggal Selesai', 'source_type' => 'surat', 'source_key' => 'tanggal_selesai', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal selesai surat.'],
            'nama_pemohon' => ['label' => 'Nama Pemohon', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pemohon dari akun pengguna.'],
            'email_pemohon' => ['label' => 'Email Pemohon', 'source_type' => 'computed', 'source_key' => 'email_pemohon', 'is_required' => false, 'default_value' => null, 'description' => 'Email pemohon dari akun pengguna.'],
            'nim_pemohon' => ['label' => 'NIM Pemohon', 'source_type' => 'computed', 'source_key' => 'nim_pemohon', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk pemohon dari akun pengguna.'],
            'nomor_induk_pemohon' => ['label' => 'Nomor Induk Pemohon', 'source_type' => 'computed', 'source_key' => 'nomor_induk_pemohon', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk pemohon dari akun pengguna.'],
            'program_studi_pemohon' => ['label' => 'Program Studi Pemohon', 'source_type' => 'computed', 'source_key' => 'program_studi_pemohon', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi pemohon dari akun pengguna.'],
            'telepon_pemohon' => ['label' => 'Telepon Pemohon', 'source_type' => 'computed', 'source_key' => 'telepon_pemohon', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor telepon pemohon dari akun pengguna.'],
            'nama' => ['label' => 'Nama', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => false, 'default_value' => null, 'description' => 'Nama dari akun pengguna.'],
            'email' => ['label' => 'Email', 'source_type' => 'user', 'source_key' => 'email', 'is_required' => false, 'default_value' => null, 'description' => 'Email pengguna.'],
            'nama_mahasiswa' => ['label' => 'Nama Mahasiswa', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pemohon yang berperan sebagai mahasiswa.'],
            'nim_mahasiswa' => ['label' => 'NIM Mahasiswa', 'source_type' => 'computed', 'source_key' => 'nim_mahasiswa', 'is_required' => false, 'default_value' => null, 'description' => 'NIM mahasiswa dari akun pemohon.'],
            'nomor_induk_mahasiswa' => ['label' => 'Nomor Induk Mahasiswa', 'source_type' => 'computed', 'source_key' => 'nomor_induk_mahasiswa', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk mahasiswa dari akun pemohon.'],
            'program_studi_mahasiswa' => ['label' => 'Program Studi Mahasiswa', 'source_type' => 'computed', 'source_key' => 'program_studi_mahasiswa', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi mahasiswa dari akun pemohon.'],
            'nama_dosen' => ['label' => 'Nama Dosen', 'source_type' => 'user', 'source_key' => 'name', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pemohon yang berperan sebagai dosen.'],
            'nip_dosen' => ['label' => 'NIP Dosen', 'source_type' => 'computed', 'source_key' => 'nip_dosen', 'is_required' => false, 'default_value' => null, 'description' => 'NIP dosen dari akun pemohon.'],
            'nomor_induk_dosen' => ['label' => 'Nomor Induk Dosen', 'source_type' => 'computed', 'source_key' => 'nomor_induk_dosen', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk dosen dari akun pemohon.'],
            'program_studi_dosen' => ['label' => 'Program Studi Dosen', 'source_type' => 'computed', 'source_key' => 'program_studi_dosen', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi dosen dari akun pemohon.'],
            'nama_kaprodi' => ['label' => 'Nama Kaprodi', 'source_type' => 'computed', 'source_key' => 'nama_kaprodi', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pejabat Kaprodi aktif.'],
            'nip_kaprodi' => ['label' => 'NIP Kaprodi', 'source_type' => 'computed', 'source_key' => 'nip_kaprodi', 'is_required' => false, 'default_value' => null, 'description' => 'NIP Kaprodi aktif.'],
            'nomor_induk_kaprodi' => ['label' => 'Nomor Induk Kaprodi', 'source_type' => 'computed', 'source_key' => 'nomor_induk_kaprodi', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk Kaprodi aktif.'],
            'program_studi_kaprodi' => ['label' => 'Program Studi Kaprodi', 'source_type' => 'computed', 'source_key' => 'program_studi_kaprodi', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi Kaprodi aktif.'],
            'nama_dekan' => ['label' => 'Nama Dekan', 'source_type' => 'computed', 'source_key' => 'nama_dekan', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pejabat Dekan aktif.'],
            'nip_dekan' => ['label' => 'NIP Dekan', 'source_type' => 'computed', 'source_key' => 'nip_dekan', 'is_required' => false, 'default_value' => null, 'description' => 'NIP Dekan aktif.'],
            'nomor_induk_dekan' => ['label' => 'Nomor Induk Dekan', 'source_type' => 'computed', 'source_key' => 'nomor_induk_dekan', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk Dekan aktif.'],
            'program_studi_dekan' => ['label' => 'Program Studi Dekan', 'source_type' => 'computed', 'source_key' => 'program_studi_dekan', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi Dekan aktif.'],
            'nama_penanda_tangan' => ['label' => 'Nama Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'nama_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Nama pejabat penanda tangan dari akun approver aktif.'],
            'email_penanda_tangan' => ['label' => 'Email Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'email_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Email pejabat penanda tangan dari akun approver aktif.'],
            'nik_penanda_tangan' => ['label' => 'Nomor Induk Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'nik_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Alias nomor induk pejabat penanda tangan.'],
            'nomor_induk_penanda_tangan' => ['label' => 'Nomor Induk Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'nomor_induk_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Nomor induk pejabat penanda tangan dari akun approver aktif.'],
            'jabatan_penanda_tangan' => ['label' => 'Jabatan Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'jabatan_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Jabatan pejabat penanda tangan dari role approver aktif.'],
            'program_studi_penanda_tangan' => ['label' => 'Program Studi Penanda Tangan', 'source_type' => 'computed', 'source_key' => 'program_studi_penanda_tangan', 'is_required' => false, 'default_value' => null, 'description' => 'Program studi pejabat penanda tangan jika tersedia.'],
            'kota_surat' => ['label' => 'Kota Surat', 'source_type' => 'system', 'source_key' => 'kota_surat', 'is_required' => true, 'default_value' => 'Cilacap', 'description' => 'Kota penerbitan surat.'],
            'tanggal_surat' => ['label' => 'Tanggal Surat', 'source_type' => 'system', 'source_key' => 'tanggal_surat', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal surat default sistem.'],
            'tanggal_surat_panjang' => ['label' => 'Tanggal Surat Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_surat_panjang', 'is_required' => true, 'default_value' => null, 'description' => 'Tanggal surat dalam format panjang.'],
            'semester_terbilang' => ['label' => 'Semester Terbilang', 'source_type' => 'computed', 'source_key' => 'semester_terbilang', 'is_required' => false, 'default_value' => null, 'description' => 'Konversi semester ke bentuk teks.'],
            'tanggal_yudisium_panjang' => ['label' => 'Tanggal Yudisium Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_yudisium_panjang', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal yudisium dalam format panjang.'],
            'tanggal_mulai_panjang' => ['label' => 'Tanggal Mulai Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_mulai_panjang', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal mulai dalam format panjang.'],
            'tanggal_selesai_panjang' => ['label' => 'Tanggal Selesai Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_selesai_panjang', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal selesai dalam format panjang.'],
            'tanggal_kegiatan_panjang' => ['label' => 'Tanggal Kegiatan Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_kegiatan_panjang', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal kegiatan dalam format panjang.'],
            'tanggal_sidang_panjang' => ['label' => 'Tanggal Sidang Panjang', 'source_type' => 'computed', 'source_key' => 'tanggal_sidang_panjang', 'is_required' => false, 'default_value' => null, 'description' => 'Tanggal sidang dalam format panjang.'],
            'kelas_info' => ['label' => 'Informasi Kelas', 'source_type' => 'computed', 'source_key' => 'kelas_info', 'is_required' => false, 'default_value' => null, 'description' => 'Potongan teks kelas jika tersedia.'],
            'dosen_pengampu_info' => ['label' => 'Informasi Dosen Pengampu', 'source_type' => 'computed', 'source_key' => 'dosen_pengampu_info', 'is_required' => false, 'default_value' => null, 'description' => 'Potongan teks dosen pengampu jika tersedia.'],
            'judul_tugas_akhir_kalimat' => ['label' => 'Kalimat Judul Tugas Akhir', 'source_type' => 'computed', 'source_key' => 'judul_tugas_akhir_kalimat', 'is_required' => false, 'default_value' => null, 'description' => 'Kalimat judul tugas akhir jika tersedia.'],
            'ruang_sidang_info' => ['label' => 'Informasi Ruang Sidang', 'source_type' => 'computed', 'source_key' => 'ruang_sidang_info', 'is_required' => false, 'default_value' => null, 'description' => 'Potongan teks ruang sidang jika tersedia.'],
            'lampiran_keterangan' => ['label' => 'Keterangan Lampiran', 'source_type' => 'surat_data', 'source_key' => 'lampiran_keterangan', 'is_required' => false, 'default_value' => null, 'description' => 'Keterangan lampiran surat.'],
            'perihal' => ['label' => 'Perihal', 'source_type' => 'surat_data', 'source_key' => 'perihal', 'is_required' => false, 'default_value' => null, 'description' => 'Perihal surat.'],
            'kepada_yth' => ['label' => 'Kepada Yth', 'source_type' => 'surat_data', 'source_key' => 'kepada_yth', 'is_required' => false, 'default_value' => null, 'description' => 'Daftar penerima surat.'],
        ];

        if (isset($common[$key])) {
            return $common[$key];
        }

        if ($field !== null) {
            return [
                'label' => (string) ($field['label'] ?? Str::headline(str_replace('_', ' ', $key))),
                'source_type' => 'surat_data',
                'source_key' => $key,
                'is_required' => (bool) ($field['required'] ?? false),
                'default_value' => null,
                'description' => 'Field dinamis surat.',
            ];
        }

        return [
            'label' => Str::headline(str_replace('.', ' ', str_replace('_', ' ', $key))),
            'source_type' => 'surat_data',
            'source_key' => $key,
            'is_required' => false,
            'default_value' => null,
            'description' => 'Placeholder yang dideteksi otomatis dari template.',
        ];
    }
}

