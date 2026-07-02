<?php

namespace App\Modules\Trace\Services;

use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Pertanyaan;
use App\Models\Tracer\Response;
use Illuminate\Support\Facades\DB;

class KuesionerAnalyticsService
{
    public function __construct(
        protected CareerService $careerService,
    ) {}

    /**
     * Build analytics data for a kuesioner.
     *
     * Contains all the query/transformation logic for question analysis,
     * distributions, averages, radar data, etc.
     */
    public function buildAnalytics(Kuesioner $kuesioner): array
    {
        $id = $kuesioner->id;

        $questions = Pertanyaan::where('kuesioner_id', $id)
            ->with(['opsiJawabans'])
            ->get()
            ->keyBy('id');

        $questionIds = $questions->pluck('id')->toArray();

        // Check if there are any responses
        $hasResponses = DB::table('detail_jawabans')
            ->whereIn('pertanyaan_id', $questionIds)
            ->exists();

        $baseQuery = DB::table('detail_jawabans as dj')
            ->join('responses as r', 'dj.response_id', '=', 'r.id')
            ->whereIn('dj.pertanyaan_id', $questionIds);

        // Bulk fetch distributions
        $allCounts = (clone $baseQuery)
            ->select('dj.pertanyaan_id', 'dj.opsi_jawaban_id', DB::raw('count(*) as total'))
            ->groupBy('dj.pertanyaan_id', 'dj.opsi_jawaban_id')
            ->get()
            ->groupBy('pertanyaan_id');

        $isSqlite = DB::getDriverName() === 'sqlite';

        // Fetch Averages
        $numericAveragesQuery = (clone $baseQuery)
            ->join('pertanyaan as p', 'dj.pertanyaan_id', '=', 'p.id')
            ->where('p.tipe', 'number');

        if ($isSqlite) {
            $numericAveragesQuery->whereRaw("dj.jawaban_text GLOB '[0-9]*'");
        } else {
            $numericAveragesQuery->whereRaw("dj.jawaban_text REGEXP '^[0-9.]+$'");
        }

        $numericAverages = $numericAveragesQuery
            ->select('dj.pertanyaan_id', DB::raw('AVG(CAST(dj.jawaban_text AS FLOAT)) as average'))
            ->groupBy('dj.pertanyaan_id')
            ->get()
            ->keyBy('pertanyaan_id');

        $choiceAverages = (clone $baseQuery)
            ->join('opsi_jawabans as oj', 'dj.opsi_jawaban_id', '=', 'oj.id')
            ->select('dj.pertanyaan_id', DB::raw('AVG(CAST(oj.nilai AS FLOAT)) as average'))
            ->groupBy('dj.pertanyaan_id')
            ->get()
            ->keyBy('pertanyaan_id');

        $allAverages = $numericAverages->union($choiceAverages);

        $allAnswersRaw = DB::table('detail_jawabans as dj')
            ->join('responses as r', 'dj.response_id', '=', 'r.id')
            ->whereIn('dj.pertanyaan_id', $questionIds)
            ->select('dj.pertanyaan_id', 'dj.jawaban_text', 'dj.id as dj_id')
            ->get()
            ->groupBy('pertanyaan_id');

        // Group by Categories
        $categoriesData = [];
        foreach ($questions->groupBy('kategori') as $kategoriName => $catQuestions) {
            $catName = $kategoriName ?: 'Umum';
            $catStats = [];

            foreach ($catQuestions as $q) {
                $analysis = [];

                if (in_array($q->tipe, ['radio', 'dropdown'])) {
                    $counts = $allCounts->get($q->id)?->pluck('total', 'opsi_jawaban_id') ?? collect();
                    foreach ($q->opsiJawabans as $opt) {
                        $analysis['distribution'][] = [
                            'label' => $opt->label,
                            'count' => $counts[$opt->id] ?? 0,
                            'score' => $opt->nilai,
                        ];
                    }
                    $analysis['average'] = round($allAverages->get($q->id)?->average ?? 0, 2);
                    $analysis['total_responses'] = array_sum(array_column($analysis['distribution'] ?? [], 'count'));
                } elseif ($q->tipe === 'checkbox') {
                    $rawAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $optionCounts = [];
                    $scoreSum = 0;
                    $scoreCount = 0;
                    foreach ($rawAnswers as $raw) {
                        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                        if (is_array($decoded)) {
                            foreach ($decoded as $val) {
                                $optionCounts[$val] = ($optionCounts[$val] ?? 0) + 1;
                                $score = $q->opsiJawabans->firstWhere('id', (int) $val)?->nilai;
                                if ($score !== null) {
                                    $scoreSum += (float) $score;
                                    $scoreCount++;
                                }
                            }
                        } elseif ($raw) {
                            $optionCounts[$raw] = ($optionCounts[$raw] ?? 0) + 1;
                            $score = $q->opsiJawabans->firstWhere('label', $raw)?->nilai;
                            if ($score !== null) {
                                $scoreSum += (float) $score;
                                $scoreCount++;
                            }
                        }
                    }
                    foreach ($q->opsiJawabans as $opt) {
                        $count = ($optionCounts[$opt->id] ?? 0) + ($optionCounts[$opt->label] ?? 0);
                        $analysis['distribution'][] = [
                            'label' => $opt->label,
                            'count' => $count,
                            'score' => $opt->nilai,
                        ];
                    }
                    $analysis['average'] = $scoreCount > 0 ? round($scoreSum / $scoreCount, 2) : 0;
                    $analysis['total_responses'] = $rawAnswers->filter(fn ($a) => $a !== null && $a !== '' && $a !== '[]')->count();
                } elseif ($q->tipe === 'matrix') {
                    $rawAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $rowSums = [];
                    $rowCounts = [];
                    foreach ($rawAnswers as $raw) {
                        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                        if (is_array($decoded)) {
                            foreach ($decoded as $rowLabel => $val) {
                                $rowSums[$rowLabel] = ($rowSums[$rowLabel] ?? 0) + (float) $val;
                                $rowCounts[$rowLabel] = ($rowCounts[$rowLabel] ?? 0) + 1;
                            }
                        }
                    }
                    $radarLabels = [];
                    $radarValues = [];
                    foreach ($rowSums as $label => $sum) {
                        $radarLabels[] = $label;
                        $radarValues[] = $rowCounts[$label] > 0 ? round($sum / $rowCounts[$label], 2) : 0;
                    }
                    if (! empty($radarLabels)) {
                        $analysis['radar_data'] = [
                            'labels' => $radarLabels,
                            'datasets' => [[
                                'label' => 'Rata-rata Skor',
                                'data' => $radarValues,
                                'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                                'borderColor' => '#3b82f6',
                                'pointBackgroundColor' => '#3b82f6',
                            ]],
                        ];
                        // Dynamic max from columns count or meta
                        $metaColumns = $q->meta['columns'] ?? [];
                        $scaleMax = count($metaColumns) > 0 ? count($metaColumns) : 5;
                        $analysis['scale_max'] = $scaleMax;
                        $analysis['columns'] = $metaColumns;

                        // Per-row averages table
                        $rowAverages = [];
                        foreach ($rowSums as $label => $sum) {
                            $avg = $rowCounts[$label] > 0 ? round($sum / $rowCounts[$label], 2) : 0;
                            $rowAverages[] = [
                                'label' => $label,
                                'average' => $avg,
                                'count' => $rowCounts[$label] ?? 0,
                                'percent' => $scaleMax > 0 ? round(($avg / $scaleMax) * 100, 1) : 0,
                            ];
                        }
                        $analysis['row_averages'] = $rowAverages;
                    }
                } elseif ($q->tipe === 'scale' || ($q->tipe === 'number' && ($q->meta['jenis_data'] ?? '') === 'scale')) {
                    $scaleMin = (int) ($q->meta['scale_min'] ?? 1);
                    $scaleMax = (int) ($q->meta['scale_max'] ?? 5);
                    $analysis['scale_min'] = $scaleMin;
                    $analysis['scale_max'] = $scaleMax;
                    $analysis['scale_label_min'] = $q->meta['scale_label_min'] ?? '';
                    $analysis['scale_label_max'] = $q->meta['scale_label_max'] ?? '';

                    // Distribution: count per scale value
                    $scaleAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $validScaleAnswers = $scaleAnswers
                        ->filter(fn ($a) => $a !== null && $a !== '' && is_numeric($a))
                        ->map(fn ($a) => (float) $a);

                    $analysis['average'] = $validScaleAnswers->count() > 0
                        ? round($validScaleAnswers->avg(), 2)
                        : 0;

                    $dist = [];
                    for ($v = $scaleMin; $v <= $scaleMax; $v++) {
                        $count = $scaleAnswers->filter(fn ($a) => (int) $a === $v)->count();
                        $dist[] = [
                            'label' => (string) $v,
                            'count' => $count,
                        ];
                    }
                    $analysis['distribution'] = $dist;
                    $analysis['total_responses'] = $validScaleAnswers->count();
                } else {
                    $qAnswers = $allAnswersRaw->get($q->id, collect())
                        ->filter(fn ($ans) => ! empty($ans->jawaban_text))
                        ->sortByDesc('dj_id')
                        ->take(10)
                        ->pluck('jawaban_text')
                        ->values()
                        ->toArray();

                    $analysis['recent_responses'] = $qAnswers;
                    $analysis['total_responses'] = $allAnswersRaw->get($q->id, collect())->count();
                }

                // Parse acuan – stored as JSON in DB
                $acuan = $q->acuan;
                if (is_string($acuan)) {
                    $acuan = json_decode($acuan, true) ?? [];
                }

                $catStats[] = [
                    'question_id' => $q->id,
                    'section_id' => $q->section_id,
                    'teks' => $q->teks,
                    'tipe' => $q->tipe,
                    'kategori' => $q->kategori,
                    'acuan' => $acuan,
                    'indikator' => $q->meta['indikator'] ?? [],
                    'analysis' => $analysis,
                ];
            }

            $categoriesData[] = [
                'name' => $catName,
                'statistics' => $catStats,
            ];
        }

        // Radar Data for Competency
        $competencyData = null;
        $competencyQuestions = $questions->filter(fn ($q) => $q->kategori === 'Kompetensi Lulusan');
        if ($competencyQuestions->count() > 2) {
            $labels = [];
            $values = [];
            foreach ($competencyQuestions as $cq) {
                $labels[] = $cq->teks;
                $values[] = round($allAverages->get($cq->id)?->average ?? 0, 2);
            }
            $competencyData = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Rata-rata Skor Kompetensi',
                    'data' => $values,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => '#3b82f6',
                    'pointBackgroundColor' => '#3b82f6',
                ]],
            ];
        }

        return [
            'kuesioner_title' => $kuesioner->judul,
            'categories' => $categoriesData,
            'radar_data' => $competencyData,
            'sections' => $kuesioner->sections->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
            ]),
            'has_responses' => $hasResponses,
        ];
    }

    /**
     * Build export data (columns, questionMap, dataRows) for a kuesioner.
     *
     * @return array{columns: string[], questionMap: array, dataRows: array[]}
     */
    public function buildExportData(
        Kuesioner $kuesioner,
        ?string $tahunLulus = null,
        ?string $prodi = null,
    ): array {
        $responsesQuery = Response::where('kuesioner_id', $kuesioner->id)
            ->with([
                'user.programStudi',
                'user.alumniProfile' => fn ($q) => $q->with(['careers' => fn ($cq) => $cq->with(['employment', 'education'])]),
                'detailJawabans.pertanyaan.opsiJawabans',
            ]);

        if ($tahunLulus || $prodi) {
            $responsesQuery->whereHas('user', function ($q) use ($tahunLulus, $prodi) {
                if ($tahunLulus) {
                    $q->where('tahun_lulus', $tahunLulus);
                }
                if ($prodi) {
                    $q->whereHas('programStudi', function ($pq) use ($prodi) {
                        $pq->where('nama', $prodi);
                    });
                }
            });
        }

        $responses = $responsesQuery->get();

        // --- Build column headers & question map ---
        $columns = [
            'No', 'Nama Alumni', 'NIM', 'NIK', 'NPWP', 'Program Studi', 'Angkatan', 'Tahun Lulus',
            'Jenis Kelamin', 'Email', 'No HP', 'LinkedIn',
            'Status Kerja', 'Instansi/Perusahaan', 'Jabatan', 'Sektor Industri',
            'Tanggal Mengisi',
        ];

        $questionMap = [];

        foreach ($kuesioner->sections as $section) {
            foreach ($section->pertanyaans as $q) {
                $metaRows = $q->meta['rows'] ?? [];
                $metaColumns = $q->meta['columns'] ?? [];

                if ($q->tipe === 'matrix' && ! empty($metaRows)) {
                    foreach ($metaRows as $row) {
                        $columns[] = $q->teks.' ['.$row.']';
                        $questionMap[$q->id]['matrix_cols'][$row] = count($columns) - 1;
                        $columns[] = $q->teks.' ['.$row.'] [Skor]';
                        $questionMap[$q->id]['matrix_score_cols'][$row] = count($columns) - 1;
                    }
                    $questionMap[$q->id]['type'] = 'matrix';
                    $questionMap[$q->id]['columns'] = $metaColumns;
                } else {
                    $columns[] = $q->teks;
                    $questionMap[$q->id]['type'] = $q->tipe;
                    $questionMap[$q->id]['index'] = count($columns) - 1;

                    if (in_array($q->tipe, ['radio', 'dropdown', 'checkbox', 'scale'])) {
                        $columns[] = $q->teks.' [Skor]';
                        $questionMap[$q->id]['score_index'] = count($columns) - 1;
                    }
                }
            }
        }

        // --- Build data rows ---
        $dataRows = [];
        $rowNum = 1;

        foreach ($responses as $response) {
            $row = array_fill(0, count($columns), '-');
            $profile = $response->user?->alumniProfile;

            $currentCareer = null;
            if ($profile && $profile->careers) {
                $flatCareers = collect($this->careerService->flattenCareers($profile->careers));
                $currentCareer = $flatCareers->where('is_current', true)->first();
            }

            $row[0] = $rowNum++;
            $user = $response->user;
            $row[1] = $user?->name ?? '-';
            $row[2] = $user?->nomor_induk ?? '-';
            $row[3] = $profile?->nik_masked ?? '-';
            $row[4] = $profile?->npwp_masked ?? '-';
            $row[5] = $user?->programStudi?->nama ?? '-';
            $row[6] = $profile?->angkatan ?? '-';
            $row[7] = $user?->tahun_lulus ?? '-';
            $row[8] = $profile?->jenis_kelamin === 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'P' ? 'Perempuan' : '-');
            $row[9] = $user?->email ?? '-';
            $row[10] = $user?->no_telepon ?? '-';
            $row[11] = $user?->linkedin ?? '-';
            $row[12] = $currentCareer['status'] ?? '-';
            $row[13] = $currentCareer['nama_perusahaan'] ?? '-';
            $row[14] = $currentCareer['jabatan'] ?? '-';
            $row[15] = $currentCareer['sektor_industri'] ?? '-';
            $row[16] = $response->submitted_at
                ? $response->submitted_at->format('Y-m-d H:i')
                : ($response->created_at ? $response->created_at->format('Y-m-d H:i') : '-');

            $details = $response->detailJawabans->keyBy('pertanyaan_id');

            foreach ($questionMap as $qId => $meta) {
                $answer = $details->get($qId);
                if (! $answer) {
                    continue;
                }

                if ($meta['type'] === 'matrix') {
                    $matrixData = is_string($answer->jawaban_text) ? json_decode($answer->jawaban_text, true) : $answer->jawaban_text;
                    if (is_array($matrixData)) {
                        $colLabels = $meta['columns'] ?? [];
                        foreach ($matrixData as $rowLabel => $val) {
                            if (isset($meta['matrix_cols'][$rowLabel])) {
                                $valInt = (int) $val;
                                if ($valInt > 0 && isset($colLabels[$valInt - 1])) {
                                    $row[$meta['matrix_cols'][$rowLabel]] = $colLabels[$valInt - 1];
                                } else {
                                    $row[$meta['matrix_cols'][$rowLabel]] = $val;
                                }
                                if (isset($meta['matrix_score_cols'][$rowLabel])) {
                                    $row[$meta['matrix_score_cols'][$rowLabel]] = is_numeric($val) ? (float) $val : '-';
                                }
                            }
                        }
                    }
                } elseif ($meta['type'] === 'checkbox') {
                    $checkData = is_string($answer->jawaban_text) ? json_decode($answer->jawaban_text, true) : $answer->jawaban_text;
                    if (is_array($checkData)) {
                        $labels = [];
                        $scores = [];
                        foreach ($checkData as $optId) {
                            $opt = $answer->pertanyaan->opsiJawabans->find($optId);
                            $labels[] = $opt ? $opt->label : $optId;
                            if ($opt && $opt->nilai !== null) {
                                $scores[] = (float) $opt->nilai;
                            }
                        }
                        $row[$meta['index']] = implode(', ', $labels);
                        if (isset($meta['score_index'])) {
                            $row[$meta['score_index']] = ! empty($scores) ? array_sum($scores) : '-';
                        }
                    } else {
                        $row[$meta['index']] = $answer->jawaban_text;
                        if (isset($meta['score_index'])) {
                            $opt = $answer->pertanyaan->opsiJawabans->firstWhere('label', $answer->jawaban_text);
                            $row[$meta['score_index']] = $opt?->nilai ?? '-';
                        }
                    }
                } elseif (in_array($meta['type'], ['radio', 'dropdown'])) {
                    $opt = null;
                    if (! empty($answer->jawaban_text)) {
                        $row[$meta['index']] = $answer->jawaban_text;
                        $opt = $answer->pertanyaan->opsiJawabans->firstWhere('label', $answer->jawaban_text);
                    } elseif ($answer->opsi_jawaban_id) {
                        $opt = $answer->pertanyaan->opsiJawabans->find($answer->opsi_jawaban_id);
                        $row[$meta['index']] = $opt ? $opt->label : $answer->opsi_jawaban_id;
                    }
                    if (isset($meta['score_index'])) {
                        $row[$meta['score_index']] = $opt?->nilai ?? '-';
                    }
                } elseif ($meta['type'] === 'scale') {
                    $row[$meta['index']] = $answer->jawaban_text ?: '-';
                    if (isset($meta['score_index'])) {
                        $row[$meta['score_index']] = is_numeric($answer->jawaban_text) ? (float) $answer->jawaban_text : '-';
                    }
                } else {
                    $row[$meta['index']] = $answer->jawaban_text ?: '-';
                }
            }

            $dataRows[] = $row;
        }

        return [
            'columns' => $columns,
            'questionMap' => $questionMap,
            'dataRows' => $dataRows,
        ];
    }
}
