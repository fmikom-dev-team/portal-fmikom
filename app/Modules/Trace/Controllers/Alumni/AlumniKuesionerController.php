<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Response;
use App\Models\Tracer\Pertanyaan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Services\AuditLogService;
use App\Models\Tracer\ActivityLog;

/**
 * AlumniKuesionerController
 *
 * Handles questionnaire interactions for alumni users including browsing
 * available questionnaires and submitting responses.
 *
 * @package App\Modules\Trace\Controllers\Alumni
 */
class AlumniKuesionerController extends Controller
{
    /**
     * Display list of available questionnaires for the alumni.
     *
     * @return InertiaResponse
     */
    public function index(): InertiaResponse
    {
        $now = now();

        $kuesioners = Kuesioner::whereIn('status', ['active', 'published'])
            ->where(function ($query) {
                $query->where('kategori', 'Alumni')
                    ->orWhere('tipe_kuesioner', 'alumni');
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('date_mulai')
                    ->orWhere('date_mulai', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('date_selesai')
                    ->orWhere('date_selesai', '>=', $now);
            })
            ->withCount(['responses' => function ($query) {
                $query->where('user_id', auth()->id());
            }])
            ->withCount('sections')
            ->latest()
            ->get();

        Log::info('Alumni questionnaire list viewed', [
            'user_id' => auth()->id(),
            'count'   => $kuesioners->count(),
        ]);

        return Inertia::render('Modules/Trace/Alumni/Quiz', [
            'kuesioners' => $kuesioners,
        ]);
    }

    /**
     * Display a specific questionnaire for filling out.
     *
     * @param  int  $id
     * @return InertiaResponse|RedirectResponse
     */
    public function show(int $id): InertiaResponse|RedirectResponse
    {
        try {
            $kuesioner = Kuesioner::with([
                'sections.pertanyaans.opsiJawabans',
            ])->findOrFail($id);

            // Validasi status & tanggal
            $redirect = $this->checkKuesionerAvailability($kuesioner);
            if ($redirect) {
                return $redirect;
            }

            // Load jawaban yang sudah ada (jika pernah mengisi)
            $response = Response::where('kuesioner_id', $id)
                ->where('user_id', auth()->id())
                ->with('detailJawabans')
                ->first();

            $existingAnswers = [];
            if ($response) {
                foreach ($response->detailJawabans as $detail) {
                    if ($detail->opsi_jawaban_id) {
                        $existingAnswers[$detail->pertanyaan_id] = $detail->opsi_jawaban_id;
                    } else {
                        $decoded = json_decode($detail->jawaban_text, true);
                        $existingAnswers[$detail->pertanyaan_id] = (json_last_error() === JSON_ERROR_NONE)
                            ? $decoded
                            : $detail->jawaban_text;
                    }
                }
            }

            Log::info('Questionnaire detail viewed', [
                'user_id'              => auth()->id(),
                'kuesioner_id'         => $id,
                'has_previous_response' => (bool) $response,
            ]);

            return Inertia::render('Modules/Trace/Alumni/FillQuiz', [
                'kuesioner'      => $kuesioner,
                'hasResponded'   => (bool) $response,
                'existingAnswers' => $existingAnswers,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Questionnaire not found', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $id,
            ]);

            return redirect()->route('tracer')->with('error', 'Kuesioner tidak ditemukan.');
        }
    }

    /**
     * Store the questionnaire response.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return RedirectResponse
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        try {
            $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

            // FIX #1 — Validasi status & tanggal juga di store,
            // bukan hanya di show(), agar tidak bisa bypass via API langsung.
            $redirect = $this->checkKuesionerAvailability($kuesioner);
            if ($redirect) {
                return $redirect;
            }

            // FIX #2 — Satu validasi saja, langsung bangun rules lengkap.
            // IMPORTANT: Semua pertanyaan harus punya rules (minimal 'nullable')
            // agar Validator mengembalikan semua jawaban di $validated['answers'].
            $rules = [
                'answers' => ['required', 'array'],
            ];

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

            $validated = Validator::make($request->all(), $rules)->validate();
            $answers   = $validated['answers'];

            // FIX #4 — Pindahkan duplicate check ke dalam transaksi
            // dan gunakan lockForUpdate() agar aman dari race condition.
            // Sebelumnya check & insert dipisah sehingga dua request
            // bersamaan bisa lolos keduanya.
            try {
                DB::transaction(function () use ($kuesioner, $answers) {
                    $alreadyResponded = DB::table('responses')
                        ->where('kuesioner_id', $kuesioner->id)
                        ->where('user_id', auth()->id())
                        ->lockForUpdate()
                        ->exists();

                    if ($alreadyResponded) {
                        // Melempar exception akan membuat transaksi otomatis di-rollback
                        throw new \RuntimeException('already_responded');
                    }

                    $responseId = DB::table('responses')->insertGetId([
                        'kuesioner_id' => $kuesioner->id,
                        'user_id'      => auth()->id(),
                        'submitted_at' => now(),
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);

                    // Pre-load semua pertanyaan + opsi sekaligus (hindari N+1)
                    $pertanyaans = Pertanyaan::whereIn('id', array_keys($answers))
                        ->with('opsiJawabans')
                        ->get()
                        ->keyBy('id');

                    $detailRows = [];

                    foreach ($answers as $pertanyaanId => $jawaban) {
                        $pertanyaan  = $pertanyaans->get($pertanyaanId);
                        $opsiId      = null;
                        $jawabanText = is_array($jawaban) ? json_encode($jawaban) : $jawaban;

                        if ($pertanyaan && in_array($pertanyaan->tipe, ['radio', 'dropdown', 'checkbox'])) {
                            if (is_array($jawaban)) {
                                $jawabanText = json_encode($jawaban);
                            } elseif (is_numeric($jawaban)) {
                                $opsi = $pertanyaan->opsiJawabans->find($jawaban);
                                if ($opsi) {
                                    $opsiId      = $opsi->id;
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
                            'response_id'     => $responseId,
                            'pertanyaan_id'   => $pertanyaanId,
                            'opsi_jawaban_id' => $opsiId,
                            'jawaban_text'    => $jawabanText,
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ];
                    }

                    if (! empty($detailRows)) {
                        DB::table('detail_jawabans')->insert($detailRows);
                    }
                });

            } catch (\RuntimeException $e) {
                // Tangani already_responded terpisah agar pesan error tepat
                if ($e->getMessage() === 'already_responded') {
                    Log::warning('Duplicate questionnaire submission attempt', [
                        'user_id'      => auth()->id(),
                        'kuesioner_id' => $id,
                    ]);

                    return redirect()->back()->withErrors([
                        'error' => 'Anda sudah mengisi kuesioner ini. Jawaban tidak dapat diubah lagi.',
                    ]);
                }

                throw $e; // lempar ulang jika bukan duplikat
            }

            // Invalidate analytics cache so admin sees fresh data
            Cache::forget("kuesioner_analytics_{$kuesioner->id}");

            AuditLogService::log('kuesioner_submitted', 'Response', null, [
                'kuesioner_id'    => $kuesioner->id,
                'kuesioner_judul' => $kuesioner->judul,
                'total_answers'   => count($answers),
            ]);

            ActivityLog::record('kuesioner.submitted', "Mengisi kuesioner: {$kuesioner->judul}", $kuesioner);

            Log::info('Questionnaire response submitted', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $id,
                'answer_count' => count($answers),
            ]);

            return redirect()->route('tracer')
                ->with('success', 'Kuesioner berhasil dikirim. Terima kasih atas partisipasi Anda!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Questionnaire validation failed', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $id,
                'errors'       => $e->errors(),
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Questionnaire store error', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $id,
                'error'        => $e->getMessage(),
                'trace'        => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses kuesioner.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Periksa apakah kuesioner tersedia (status aktif & dalam rentang tanggal).
     * Mengembalikan RedirectResponse jika tidak tersedia, null jika aman.
     *
     * @param  Kuesioner  $kuesioner
     * @return RedirectResponse|null
     */
    private function checkKuesionerAvailability(Kuesioner $kuesioner): ?RedirectResponse
    {
        $now = now();

        if ($kuesioner->date_mulai && $kuesioner->date_mulai > $now) {
            Log::warning('Questionnaire not yet open', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $kuesioner->id,
            ]);

            return redirect()->route('tracer')->with('error', 'Kuesioner ini belum dibuka.');
        }

        if ($kuesioner->date_selesai && $kuesioner->date_selesai < $now) {
            Log::warning('Questionnaire closed', [
                'user_id'      => auth()->id(),
                'kuesioner_id' => $kuesioner->id,
            ]);

            return redirect()->route('tracer')->with('error', 'Kuesioner ini sudah ditutup.');
        }

        return null;
    }
}