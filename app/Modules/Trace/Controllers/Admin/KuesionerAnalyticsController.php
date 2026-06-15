<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Pertanyaan;
use App\Models\Tracer\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class KuesionerAnalyticsController extends Controller
{
    public function getAnalytics(Request $request, $id)
    {
        $cacheKey = "kuesioner_analytics_{$id}";

        $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($id) {
            $kuesioner = Kuesioner::with(['sections.pertanyaans.opsiJawabans'])->findOrFail($id);

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
                        ];
                    }
                } elseif ($q->tipe === 'checkbox') {
                    $rawAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $optionCounts = [];
                    foreach ($rawAnswers as $raw) {
                        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                        if (is_array($decoded)) {
                            foreach ($decoded as $val) {
                                $optionCounts[$val] = ($optionCounts[$val] ?? 0) + 1;
                            }
                        } elseif ($raw) {
                            $optionCounts[$raw] = ($optionCounts[$raw] ?? 0) + 1;
                        }
                    }
                    foreach ($q->opsiJawabans as $opt) {
                        $count = ($optionCounts[$opt->id] ?? 0) + ($optionCounts[$opt->label] ?? 0);
                        $analysis['distribution'][] = [
                            'label' => $opt->label,
                            'count' => $count,
                        ];
                    }
                } elseif ($q->tipe === 'matrix') {
                    $rawAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $rowSums = [];
                    $rowCounts = [];
                    foreach ($rawAnswers as $raw) {
                        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
                        if (is_array($decoded)) {
                            foreach ($decoded as $rowLabel => $val) {
                                $rowSums[$rowLabel]  = ($rowSums[$rowLabel] ?? 0) + (float)$val;
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
                    if (!empty($radarLabels)) {
                        $analysis['radar_data'] = [
                            'labels'   => $radarLabels,
                            'datasets' => [[
                                'label'           => 'Rata-rata Skor',
                                'data'            => $radarValues,
                                'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                                'borderColor'     => '#3b82f6',
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
                                'label'   => $label,
                                'average' => $avg,
                                'count'   => $rowCounts[$label] ?? 0,
                                'percent' => $scaleMax > 0 ? round(($avg / $scaleMax) * 100, 1) : 0,
                            ];
                        }
                        $analysis['row_averages'] = $rowAverages;
                    }
                } elseif ($q->tipe === 'scale' || ($q->tipe === 'number' && ($q->meta['jenis_data'] ?? '') === 'scale')) {
                    $analysis['average'] = round($allAverages->get($q->id)?->average ?? 0, 2);
                    $scaleMin = (int)($q->meta['scale_min'] ?? 1);
                    $scaleMax = (int)($q->meta['scale_max'] ?? 5);
                    $analysis['scale_min'] = $scaleMin;
                    $analysis['scale_max'] = $scaleMax;
                    $analysis['scale_label_min'] = $q->meta['scale_label_min'] ?? '';
                    $analysis['scale_label_max'] = $q->meta['scale_label_max'] ?? '';

                    // Distribution: count per scale value
                    $scaleAnswers = $allAnswersRaw->get($q->id, collect())->pluck('jawaban_text');
                    $dist = [];
                    for ($v = $scaleMin; $v <= $scaleMax; $v++) {
                        $count = $scaleAnswers->filter(fn($a) => (int)$a === $v)->count();
                        $dist[] = [
                            'label' => (string)$v,
                            'count' => $count,
                        ];
                    }
                    $analysis['distribution'] = $dist;
                    $analysis['total_responses'] = $scaleAnswers->filter(fn($a) => $a !== null && $a !== '')->count();
                } else {
                    $qAnswers = $allAnswersRaw->get($q->id, collect())
                        ->filter(fn($ans) => !empty($ans->jawaban_text))
                        ->sortByDesc('dj_id')
                        ->take(10)
                        ->pluck('jawaban_text')
                        ->values()
                        ->toArray();

                    $analysis['recent_responses'] = $qAnswers;
                    $analysis['total_responses']  = $allAnswersRaw->get($q->id, collect())->count();
                }

                // Parse acuan â€” stored as JSON in DB
                $acuan = $q->acuan;
                if (is_string($acuan)) {
                    $acuan = json_decode($acuan, true) ?? [];
                }

                $catStats[] = [
                    'question_id' => $q->id,
                    'section_id'  => $q->section_id,
                    'teks'        => $q->teks,
                    'tipe'        => $q->tipe,
                    'kategori'    => $q->kategori,
                    'acuan'       => $acuan,
                    'analysis'    => $analysis,
                ];
            }

            $categoriesData[] = [
                'name'       => $catName,
                'statistics' => $catStats,
            ];
        }

        // Radar Data for Competency
        $competencyData     = null;
        $competencyQuestions = $questions->filter(fn($q) => $q->kategori === 'Kompetensi Lulusan');
        if ($competencyQuestions->count() > 2) {
            $labels = [];
            $values = [];
            foreach ($competencyQuestions as $cq) {
                $labels[] = $cq->teks;
                $values[] = round($allAverages->get($cq->id)?->average ?? 0, 2);
            }
            $competencyData = [
                'labels'   => $labels,
                'datasets' => [[
                    'label'           => 'Rata-rata Skor Kompetensi',
                    'data'            => $values,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor'     => '#3b82f6',
                    'pointBackgroundColor' => '#3b82f6',
                ]],
            ];
        }

            return [
                'kuesioner_title'  => $kuesioner->judul,
                'categories'       => $categoriesData,
                'radar_data'       => $competencyData,
                'sections'         => $kuesioner->sections->map(fn($s) => [
                    'id'    => $s->id,
                    'title' => $s->title,
                ]),
                'has_responses'    => $hasResponses,
            ];
        });

        // Add live response count (always fresh, not cached)
        $data['total_responses'] = Response::where('kuesioner_id', $id)->count();
        $data['last_updated'] = now()->toISOString();

        return response()->json($data);
    }

    public function liveStats($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        $totalResponses = Response::where('kuesioner_id', $id)->count();
        $todayResponses = Response::where('kuesioner_id', $id)
            ->whereDate('created_at', today())
            ->count();
        $lastResponse = Response::where('kuesioner_id', $id)
            ->latest()
            ->first();

        return response()->json([
            'total_responses' => $totalResponses,
            'today_responses' => $todayResponses,
            'last_response_at' => $lastResponse?->created_at?->toISOString(),
            'cache_status' => Cache::has("kuesioner_analytics_{$id}") ? 'cached' : 'fresh',
        ]);
    }

    public function getRespondents(Request $request, $id)
    {
        $tahunLulus = $request->query('tahun_lulus');
        $prodi      = $request->query('prodi');

        $query = \App\Models\Tracer\Response::where('kuesioner_id', $id)
            ->with(['user.alumniProfile'])
            ->latest();

        if ($tahunLulus || $prodi) {
            $query->whereHas('user', function ($q) use ($tahunLulus, $prodi) {
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

        return response()->json($query->paginate(20));
    }

    public function export(Request $request, $id)
    {
        $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

        $tahunLulus = $request->query('tahun_lulus');
        $prodi      = $request->query('prodi');

        $responsesQuery = \App\Models\Tracer\Response::where('kuesioner_id', $id)
            ->with([
                'user.alumniProfile' => fn($q) => $q->with(['careers' => fn($cq) => $cq->with(['employment', 'education'])]),
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
                $metaRows    = $q->meta['rows'] ?? [];
                $metaColumns = $q->meta['columns'] ?? [];

                if ($q->tipe === 'matrix' && !empty($metaRows)) {
                    foreach ($metaRows as $row) {
                        $columns[] = $q->teks . ' [' . $row . ']';
                        $questionMap[$q->id]['matrix_cols'][$row] = count($columns) - 1;
                    }
                    $questionMap[$q->id]['type']    = 'matrix';
                    $questionMap[$q->id]['columns'] = $metaColumns;
                } else {
                    $columns[] = $q->teks;
                    $questionMap[$q->id]['type']  = $q->tipe;
                    $questionMap[$q->id]['index'] = count($columns) - 1;
                }
            }
        }

        // --- Build data rows ---
        $dataRows = [];
        $rowNum = 1;

        foreach ($responses as $response) {
            $row     = array_fill(0, count($columns), '-');
            $profile = $response->user?->alumniProfile;

            $currentCareer = null;
            if ($profile && $profile->careers) {
                $careerService = app(\App\Services\CareerService::class);
                $flatCareers   = collect($careerService->flattenCareers($profile->careers));
                $currentCareer = $flatCareers->where('is_current', true)->first();
            }

            $row[0]  = $rowNum++;
            $row[1]  = $profile?->nama_lengkap ?? $response->user->name;
            $row[2]  = $profile?->nim ?? '-';
            $row[3]  = $profile?->nik ?? '-';
            $row[4]  = $profile?->npwp ?? '-';
            $row[5]  = $profile?->program_studi ?? '-';
            $row[6]  = $profile?->angkatan ?? '-';
            $row[7]  = $profile?->tahun_lulus ?? '-';
            $row[8]  = $profile?->jenis_kelamin === 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'P' ? 'Perempuan' : '-');
            $row[9]  = $response->user->email;
            $row[10] = $profile?->no_hp ?? '-';
            $row[11] = $profile?->linkedin_url ?? '-';
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
                if (!$answer) continue;

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
                            }
                        }
                    }
                } elseif ($meta['type'] === 'checkbox') {
                    $checkData = is_string($answer->jawaban_text) ? json_decode($answer->jawaban_text, true) : $answer->jawaban_text;
                    if (is_array($checkData)) {
                        $labels = [];
                        foreach ($checkData as $optId) {
                            $opt      = $answer->pertanyaan->opsiJawabans->find($optId);
                            $labels[] = $opt ? $opt->label : $optId;
                        }
                        $row[$meta['index']] = implode(', ', $labels);
                    } else {
                        $row[$meta['index']] = $answer->jawaban_text;
                    }
                } elseif (in_array($meta['type'], ['radio', 'dropdown'])) {
                    if (!empty($answer->jawaban_text)) {
                        $row[$meta['index']] = $answer->jawaban_text;
                    } elseif ($answer->opsi_jawaban_id) {
                        $opt = $answer->pertanyaan->opsiJawabans->find($answer->opsi_jawaban_id);
                        $row[$meta['index']] = $opt ? $opt->label : $answer->opsi_jawaban_id;
                    }
                } else {
                    $row[$meta['index']] = $answer->jawaban_text ?: '-';
                }
            }

            $dataRows[] = $row;
        }

        // --- Generate XLSX with PhpSpreadsheet ---
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Responden');

        // Write header row
        foreach ($columns as $colIdx => $colName) {
            $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . '1';
            $sheet->setCellValue($cellCoord, $colName);
        }

        // Style header row
        $lastColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
        $headerRange = 'A1:' . $lastColLetter . '1';

        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                'wrapText'   => true,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(35);

        // Write data rows
        foreach ($dataRows as $rowIdx => $rowData) {
            $excelRow = $rowIdx + 2;
            foreach ($rowData as $colIdx => $value) {
                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . $excelRow;
                $sheet->setCellValue($cellCoord, $value);
            }

            // Zebra striping
            if ($rowIdx % 2 === 1) {
                $rowRange = 'A' . $excelRow . ':' . $lastColLetter . $excelRow;
                $sheet->getStyle($rowRange)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }
        }

        // Style data area
        $lastDataRow = count($dataRows) + 1;
        if ($lastDataRow > 1) {
            $dataRange = 'A2:' . $lastColLetter . $lastDataRow;
            $sheet->getStyle($dataRange)->applyFromArray([
                'font' => ['size' => 10],
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E5E7EB'],
                    ],
                ],
            ]);

            // Center the "No" column
            $sheet->getStyle('A2:A' . $lastDataRow)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // Auto-width columns
        foreach (range(0, count($columns) - 1) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1);
            $maxLen = mb_strlen($columns[$colIdx]);
            foreach ($dataRows as $rowData) {
                $cellLen = mb_strlen((string)($rowData[$colIdx] ?? ''));
                if ($cellLen > $maxLen) $maxLen = $cellLen;
            }
            $width = min(max($maxLen + 3, 8), 40);
            $sheet->getColumnDimension($colLetter)->setWidth($width);
        }

        // Freeze header row & auto-filter
        $sheet->freezePane('A2');
        $sheet->setAutoFilter($headerRange);

        // --- Output as download ---
        $filename = 'Export_' . str_replace(' ', '_', $kuesioner->judul) . '_' . date('Ymd_His') . '.xlsx';

        $tempFile = tempnam(sys_get_temp_dir(), 'export_');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($tempFile);
        $spreadsheet->disconnectWorksheets();

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
