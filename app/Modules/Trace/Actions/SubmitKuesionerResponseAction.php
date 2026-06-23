<?php

namespace App\Modules\Trace\Actions;

use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Pertanyaan;
use Illuminate\Support\Facades\DB;

class SubmitKuesionerResponseAction
{
    /**
     * Build dynamic validation rules based on kuesioner structure.
     */
    public function buildValidationRules(Kuesioner $kuesioner): array
    {
        $rules = ['answers' => ['required', 'array']];

        foreach ($kuesioner->sections as $section) {
            foreach ($section->pertanyaans as $pertanyaan) {
                if ($pertanyaan->tipe === 'matrix') {
                    $rows = $pertanyaan->meta['rows'] ?? [];
                    foreach ($rows as $row) {
                        $rules["answers.{$pertanyaan->id}.{$row}"] = $pertanyaan->is_required
                            ? ['required']
                            : ['nullable'];
                    }
                } elseif ($pertanyaan->tipe === 'checkbox') {
                    $rules["answers.{$pertanyaan->id}"] = $pertanyaan->is_required
                        ? ['required', 'array', 'min:1']
                        : ['nullable'];
                } else {
                    $rules["answers.{$pertanyaan->id}"] = $pertanyaan->is_required
                        ? ['required']
                        : ['nullable'];
                }
            }
        }

        return $rules;
    }

    /**
     * Submit kuesioner response within a transaction.
     *
     * @throws \RuntimeException if already responded
     */
    public function execute(Kuesioner $kuesioner, array $answers, int $userId): void
    {
        DB::transaction(function () use ($kuesioner, $answers, $userId) {
            $alreadyResponded = DB::table('responses')
                ->where('kuesioner_id', $kuesioner->id)
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->exists();

            if ($alreadyResponded) {
                // Melempar exception akan membuat transaksi otomatis di-rollback
                throw new \RuntimeException('already_responded');
            }

            $responseId = DB::table('responses')->insertGetId([
                'kuesioner_id' => $kuesioner->id,
                'user_id' => $userId,
                'submitted_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Pre-load semua pertanyaan + opsi sekaligus (hindari N+1)
            $pertanyaans = Pertanyaan::where('kuesioner_id', $kuesioner->id)
                ->whereIn('id', array_keys($answers))
                ->with('opsiJawabans')
                ->get()
                ->keyBy('id');

            $detailRows = [];

            foreach ($answers as $pertanyaanId => $jawaban) {
                $pertanyaan = $pertanyaans->get($pertanyaanId);
                $opsiId = null;
                $jawabanText = is_array($jawaban) ? json_encode($jawaban) : $jawaban;

                if ($pertanyaan && in_array($pertanyaan->tipe, ['radio', 'dropdown', 'checkbox'])) {
                    if (is_array($jawaban)) {
                        $jawabanText = json_encode($jawaban);
                    } elseif (is_numeric($jawaban)) {
                        $opsi = $pertanyaan->opsiJawabans->find($jawaban);
                        if ($opsi) {
                            $opsiId = $opsi->id;
                            $jawabanText = $opsi->label;
                        }
                    } else {
                        $opsi = $pertanyaan->opsiJawabans->firstWhere('label', $jawaban);
                        if ($opsi) {
                            $opsiId = $opsi->id;
                        }
                    }
                }

                $detailRows[] = [
                    'response_id' => $responseId,
                    'pertanyaan_id' => $pertanyaanId,
                    'opsi_jawaban_id' => $opsiId,
                    'jawaban_text' => $jawabanText,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (! empty($detailRows)) {
                DB::table('detail_jawabans')->insert($detailRows);
            }
        });
    }
}
