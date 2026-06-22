<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trace\StoreKuesionerRequest;
use Illuminate\Http\Request;
use App\Models\Tracer\Kuesioner;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\DB;
use App\Modules\Trace\Actions\SaveKuesionerSectionsAction;
use App\Modules\Trace\Actions\DuplicateKuesionerAction;
use App\Models\Tracer\ActivityLog;

class KuesionerController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $query = Kuesioner::withCount('responses');

        if ($request->filled('search')) {
            $search = str_replace(['%', '_', '\\'], ['\\%', '\\_', '\\\\'], $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%' . $search . '%')
                  ->orWhere('subtitle', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('year')) {
            $query->where('tahun', $request->input('year'));
        }

        return Inertia::render('Modules/Trace/Admin/Questionnaire', [
            'kuesioners' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'year']),
            'activeCount' => Inertia::defer(fn () => Kuesioner::where('status', 'active')->count())
        ]);
    }

    public function create(): InertiaResponse
    {
        return Inertia::render('Modules/Trace/Admin/QuestionnaireDetail', [
            'kuesioner' => [
                'id' => null,
                'judul' => '',
                'subtitle' => '',
                'kategori' => 'Alumni',
                'tahun' => date('Y'),
                'deskripsi' => '',
                'status' => 'active',
                'sections' => []
            ],
        ]);
    }

    public function edit($id): InertiaResponse
    {
        return $this->show($id);
    }

    public function store(StoreKuesionerRequest $request, SaveKuesionerSectionsAction $saveAction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($request, $validated, $saveAction) {
                $kuesioner = Kuesioner::create([
                    'judul'          => $validated['judul'],
                    'subtitle'       => $validated['subtitle'],
                    'kategori'       => $validated['kategori'],
                    'tahun'          => !empty($validated['tahun']) ? (int)$validated['tahun'] : null,
                    'date_mulai'     => $validated['date_mulai'],
                    'date_selesai'   => $validated['date_selesai'],
                    'deskripsi'      => $validated['deskripsi'],
                    'status'         => $validated['status'],
                    'tipe_kuesioner' => $this->resolveTipeKuesioner($validated['kategori'] ?? ''),
                    'created_by'     => $request->user()->id,
                ]);

                $saveAction->execute($kuesioner, $request->input('sections', []));
                ActivityLog::record('kuesioner.created', "Membuat kuesioner: {$kuesioner->judul}", $kuesioner);

                return redirect()->route('module.trace.admin.questionnaires.index')->with('success', 'Kuesioner berhasil dibuat');
            });
        } catch (\Exception $e) {
            \Log::error('Kuesioner Save Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan kuesioner: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(StoreKuesionerRequest $request, $id, SaveKuesionerSectionsAction $saveAction): RedirectResponse
    {
        $kuesioner = Kuesioner::findOrFail($id);

        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($kuesioner, $request, $validated, $saveAction) {
                $kuesioner->update([
                    'judul'          => $validated['judul'],
                    'subtitle'       => $validated['subtitle'],
                    'kategori'       => $validated['kategori'],
                    'tahun'          => !empty($validated['tahun']) ? (int)$validated['tahun'] : null,
                    'date_mulai'     => $validated['date_mulai'],
                    'date_selesai'   => $validated['date_selesai'],
                    'deskripsi'      => $validated['deskripsi'],
                    'status'         => $validated['status'],
                    'tipe_kuesioner' => $this->resolveTipeKuesioner($validated['kategori'] ?? ''),
                ]);

                $saveAction->execute($kuesioner, $request->input('sections', []));
                ActivityLog::record('kuesioner.updated', "Memperbarui kuesioner: {$kuesioner->judul}", $kuesioner);

                return redirect()->route('module.trace.admin.questionnaires.index')->with('success', 'Kuesioner berhasil diperbarui');
            });
        } catch (\Exception $e) {
            \Log::error('Kuesioner Update Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui kuesioner: ' . $e->getMessage()])->withInput();
        }
    }

    public function duplicate($id, DuplicateKuesionerAction $action): RedirectResponse
    {
        $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

        try {
            $action->execute($kuesioner);

            return redirect()->route('module.trace.admin.questionnaires.index')
                ->with('success', 'Kuesioner berhasil diduplikasi');
        } catch (\Exception $e) {
            \Log::error('Kuesioner Duplicate Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menduplikasi kuesioner: ' . $e->getMessage()]);
        }
    }



    protected function resolveTipeKuesioner(string $kategori): string
    {
        return strtolower($kategori) === 'stakeholder' ? 'stakeholder' : 'alumni';
    }

    public function show($id): InertiaResponse
    {
        $kuesioner = Kuesioner::with([
            'sections.pertanyaans.opsiJawabans',
        ])->findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/QuestionnaireDetail', [
            'kuesioner' => $kuesioner,
        ]);
    }

    public function analyticsPage($id): InertiaResponse
    {
        $kuesioner = Kuesioner::with([
            'sections.pertanyaans.opsiJawabans',
        ])->findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/QuestionnaireAnalytics', [
            'kuesioner'   => $kuesioner,
            'kuesionerId' => $id,
        ]);
    }

    public function destroy($id): RedirectResponse
    {
        $kuesioner = Kuesioner::findOrFail($id);
        ActivityLog::record('kuesioner.deleted', "Menghapus kuesioner: {$kuesioner->judul}", $kuesioner);
        $kuesioner->delete();

        return redirect()->back()->with('success', 'Kuesioner berhasil dihapus');
    }
}
