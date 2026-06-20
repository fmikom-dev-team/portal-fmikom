<?php

use App\Models\JenisSurat;
use App\Support\TemplatePlaceholderSynchronizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $jenisSurats = JenisSurat::query()
            ->with('template')
            ->where('nama', 'Surat Keterangan Lulus')
            ->get();

        foreach ($jenisSurats as $jenisSurat) {
            $fieldConfig = [
                [
                    'name' => 'nama_penanda_tangan',
                    'label' => 'Nama Penanda Tangan',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'nik_penanda_tangan',
                    'label' => 'NIK Penanda Tangan',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'jabatan',
                    'label' => 'Jabatan',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'nama_mahasiswa',
                    'label' => 'Nama Mahasiswa',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'nim',
                    'label' => 'NIM',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'tahun_lulus',
                    'label' => 'Tahun Lulus',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
                [
                    'name' => 'tempat_tanggal_lahir',
                    'label' => 'Tempat/Tanggal Lahir',
                    'type' => 'text',
                    'required' => true,
                    'placeholder' => '',
                    'help' => '',
                    'options' => [],
                    'repeatable' => false,
                    'add_label' => 'Tambah',
                    'item_label' => 'Item',
                ],
            ];

            $jenisSurat->update([
                'field_config' => $fieldConfig,
            ]);

            $template = $jenisSurat->template;

            if ($template === null) {
                continue;
            }

            $components = json_decode((string) $template->template_body, true);

            if (! is_array($components)) {
                continue;
            }

            foreach ($components as &$component) {
                if (! is_array($component) || ($component['type'] ?? null) !== 'tabel_data') {
                    continue;
                }

                $rows = $component['rows'] ?? [];

                if (! is_array($rows) || $rows === []) {
                    continue;
                }

                $firstLabel = strtolower(trim((string) ($rows[0]['label'] ?? '')));

                if ($firstLabel === 'nama' && isset($rows[1]['label']) && strtolower(trim((string) $rows[1]['label'])) === 'nik') {
                    $component['rows'] = [
                        ['label' => 'Nama', 'nilai' => '{{nama_penanda_tangan}}'],
                        ['label' => 'NIK', 'nilai' => '{{nik_penanda_tangan}}'],
                        ['label' => 'Jabatan', 'nilai' => '{{jabatan}}'],
                    ];

                    continue;
                }

                if ($firstLabel === 'nama' && isset($rows[1]['label']) && strtolower(trim((string) $rows[1]['label'])) === 'nim') {
                    $component['rows'] = [
                        ['label' => 'Nama', 'nilai' => '{{nama_mahasiswa}}'],
                        ['label' => 'NIM', 'nilai' => '{{nim}}'],
                        ['label' => 'Tahun Lulus', 'nilai' => '{{tahun_lulus}}'],
                        ['label' => 'Tempat, Tanggal Lahir', 'nilai' => '{{tempat_tanggal_lahir}}'],
                    ];
                }
            }
            unset($component);

            $template->template_body = json_encode($components, JSON_THROW_ON_ERROR);
            $template->save();

            TemplatePlaceholderSynchronizer::syncTemplate($template, $fieldConfig);
        }
    }

    public function down(): void
    {
    }
};
