<?php

namespace App\Modules\Trace\Controllers\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Tracer\Kuesioner;
use App\Models\Tracer\Response;
use App\Models\User;
use App\Modules\Trace\Actions\SubmitKuesionerResponseAction;
use App\Modules\Trace\Services\AuditLogService;
use App\Modules\Trace\Services\TraceCacheService;
use App\Notifications\Trace\KuesionerResponseSubmitted;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

/**
 * AlumniKuesionerController
 *
 * Handles questionnaire interactions for alumni users including browsing
 * available questionnaires and submitting responses.
 */
class AlumniKuesionerController extends Controller
{
    /**
     * Display list of available questionnaires for the alumni.
     */
    public function index(Request $request): InertiaResponse
    {
        $readOnly = (bool) $request->attributes->get('trace_alumni_read_only', false);
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
            'count' => $kuesioners->count(),
        ]);

        return Inertia::render('Modules/Trace/Alumni/Quiz', [
            'kuesioners' => $kuesioners,
            'readOnly' => $readOnly,
        ]);
    }

    /**
     * Display a specific questionnaire for filling out.
     */
    public function show(int $id): InertiaResponse|RedirectResponse
    {
        try {
            $readOnly = (bool) request()->attributes->get('trace_alumni_read_only', false);
            $kuesioner = Kuesioner::whereIn('status', ['active', 'published'])
                ->with([
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
                'user_id' => auth()->id(),
                'kuesioner_id' => $id,
                'has_previous_response' => (bool) $response,
            ]);

            return Inertia::render('Modules/Trace/Alumni/FillQuiz', [
                'kuesioner' => $kuesioner,
                'hasResponded' => (bool) $response,
                'existingAnswers' => $existingAnswers,
                'readOnly' => $readOnly,
            ]);

        } catch (ModelNotFoundException $e) {
            Log::warning('Questionnaire not found', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $id,
            ]);

            return redirect()->route('module.trace.kuesioner')->with('error', 'Kuesioner tidak ditemukan.');
        }
    }

    /**
     * Store the questionnaire response.
     */
    public function store(Request $request, int $id): RedirectResponse
    {
        abort_if($request->attributes->get('trace_alumni_read_only', false), 403, 'Mode alumni untuk admin hanya dapat melihat data.');

        $action = new SubmitKuesionerResponseAction;

        try {
            $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

            // Validasi status & tanggal juga di store,
            // bukan hanya di show(), agar tidak bisa bypass via API langsung.
            $redirect = $this->checkKuesionerAvailability($kuesioner);
            if ($redirect) {
                return $redirect;
            }

            $rules = $action->buildValidationRules($kuesioner);
            $validated = Validator::make($request->all(), $rules)->validate();
            $answers = $validated['answers'];

            try {
                $action->execute($kuesioner, $answers, auth()->id());
            } catch (\RuntimeException $e) {
                if ($e->getMessage() === 'already_responded') {
                    Log::warning('Duplicate questionnaire submission attempt', [
                        'user_id' => auth()->id(),
                        'kuesioner_id' => $id,
                    ]);

                    return redirect()->back()->withErrors([
                        'error' => 'Anda sudah mengisi kuesioner ini. Jawaban tidak dapat diubah lagi.',
                    ]);
                }

                throw $e; // lempar ulang jika bukan duplikat
            }

            TraceCacheService::forgetQuestionnaireCaches($kuesioner->id);
            TraceCacheService::forgetDashboardCaches(userId: auth()->id());

            AuditLogService::log('kuesioner_submitted', 'Response', null, [
                'kuesioner_id' => $kuesioner->id,
                'kuesioner_judul' => $kuesioner->judul,
                'total_answers' => count($answers),
            ]);

            // Notify admins about new kuesioner response
            $totalResponses = Response::where('kuesioner_id', $kuesioner->id)->count();
            $admins = User::where('user_type', 'admin')->get();
            Notification::send($admins, new KuesionerResponseSubmitted(
                auth()->user()->name,
                $kuesioner->judul,
                $kuesioner->id,
                $totalResponses,
            ));

            Log::info('Questionnaire response submitted', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $id,
                'answer_count' => count($answers),
            ]);

            return redirect()->route('module.trace.kuesioner')
                ->with('success', 'Kuesioner berhasil dikirim. Terima kasih atas partisipasi Anda!');

        } catch (ValidationException $e) {
            Log::warning('Questionnaire validation failed', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $id,
                'errors' => $e->errors(),
            ]);

            return redirect()->back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            Log::error('Questionnaire store error', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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
     */
    private function checkKuesionerAvailability(Kuesioner $kuesioner): ?RedirectResponse
    {
        $now = now();

        if ($kuesioner->date_mulai && $kuesioner->date_mulai > $now) {
            Log::warning('Questionnaire not yet open', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $kuesioner->id,
            ]);

            return redirect()->route('module.trace.kuesioner')->with('error', 'Kuesioner ini belum dibuka.');
        }

        if ($kuesioner->date_selesai && $kuesioner->date_selesai < $now) {
            Log::warning('Questionnaire closed', [
                'user_id' => auth()->id(),
                'kuesioner_id' => $kuesioner->id,
            ]);

            return redirect()->route('module.trace.kuesioner')->with('error', 'Kuesioner ini sudah ditutup.');
        }

        return null;
    }
}
