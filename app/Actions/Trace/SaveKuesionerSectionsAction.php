<?php

namespace App\Actions\Trace;

use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Section;
use App\Models\Tracer\Pertanyaan;
use App\Models\Tracer\OpsiJawaban;
use Illuminate\Support\Facades\DB;

class SaveKuesionerSectionsAction
{
    public function execute(Kuesioner $kuesioner, array $sections): void
    {
        // Kumpulkan ID sections yang dikirim dari frontend (untuk deteksi hapus)
        $incomingSectionIds = collect($sections)
            ->filter(fn($s) => !empty($s['id']))
            ->pluck('id')
            ->toArray();

        // Hapus sections yang tidak ada lagi di payload (cascade ke pertanyaan & opsi)
        $kuesioner->sections()
            ->whereNotIn('id', $incomingSectionIds)
            ->delete();

        foreach ($sections as $order => $sectionData) {
            // Upsert section
            $section = $kuesioner->sections()->updateOrCreate(
                ['id' => $sectionData['id'] ?? null],
                [
                    'title'       => $sectionData['judul'] ?? $sectionData['title'] ?? '',
                    'description' => $sectionData['deskripsi'] ?? $sectionData['description'] ?? null,
                    'order'       => $order,
                    // Kolom di DB bernama 'conditions' (bukan logic_condition)
                    'conditions'  => isset($sectionData['conditions']) && $sectionData['conditions']
                        ? $sectionData['conditions']
                        : null,
                ]
            );

            $pertanyaans = $sectionData['pertanyaans'] ?? $sectionData['questions'] ?? [];

            // Kumpulkan ID pertanyaan yang masih ada
            $incomingQIds = collect($pertanyaans)
                ->filter(fn($q) => !empty($q['id']))
                ->pluck('id')
                ->toArray();

            // Hapus pertanyaan yang dihapus dari builder
            $section->pertanyaans()
                ->whereNotIn('id', $incomingQIds)
                ->delete();

            foreach ($pertanyaans as $qOrder => $qData) {
                // Bangun meta dari data pertanyaan
                $meta = $qData['meta'] ?? [];

                // Simpan matrix_rows ke dalam meta.rows agar konsisten dengan FillKuesioner
                if (!empty($qData['matrix_rows'])) {
                    $meta['rows'] = $qData['matrix_rows'];
                }

                // Upsert pertanyaan
                $pertanyaan = $section->pertanyaans()->updateOrCreate(
                    ['id' => $qData['id'] ?? null],
                    [
                        'kuesioner_id'    => $kuesioner->id,
                        'teks'            => $qData['teks'] ?? '',
                        'tipe'            => $qData['tipe'] ?? 'text',
                        'tipe_data'       => $qData['tipe_data'] ?? 'text',
                        'is_required'     => in_array($qData['is_required'] ?? false, [true, 1, '1', 'true'], true),
                        'urutan'          => $qOrder,
                        'kategori'        => $meta['kategori'] ?? $qData['kategori'] ?? null,
                        'meta'            => $meta ?: null,
                        'acuan'           => !empty($meta['acuan']) ? $meta['acuan'] : null,
                        // Kolom di DB bernama 'logic_condition'
                        'logic_condition' => isset($qData['logic_condition']) && $qData['logic_condition']
                            ? $qData['logic_condition']
                            : null,
                        'skoring'         => $qData['skoring'] ?? null,
                    ]
                );

                $opsiJawabans = $qData['opsi_jawabans'] ?? [];

                // Kumpulkan ID opsi yang masih ada
                $incomingOpsiIds = collect($opsiJawabans)
                    ->filter(fn($o) => !empty($o['id']))
                    ->pluck('id')
                    ->toArray();

                // Hapus opsi yang dihapus
                $pertanyaan->opsiJawabans()
                    ->whereNotIn('id', $incomingOpsiIds)
                    ->delete();

                foreach ($opsiJawabans as $oOrder => $opsiData) {
                    $pertanyaan->opsiJawabans()->updateOrCreate(
                        ['id' => $opsiData['id'] ?? null],
                        [
                            'label'  => $opsiData['label'] ?? '',
                            'nilai'  => $opsiData['skor'] ?? $opsiData['nilai'] ?? null,
                            'urutan' => $oOrder,
                        ]
                    );
                }
            }
        }
    }
}