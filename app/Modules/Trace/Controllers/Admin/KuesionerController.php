<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tracer\Kuesioner;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Actions\Trace\SaveKuesionerSectionsAction;
use App\Models\Tracer\ActivityLog;

class KuesionerController extends Controller
{
    public function index(Request $request)
    {
        $query = Kuesioner::withCount('responses');

        if ($request->filled('search')) {
            $search = $request->input('search');
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

        return Inertia::render('Modules/Trace/Admin/Quissioner', [
            'kuesioners' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'status', 'year']),
            'activeCount' => Inertia::defer(fn () => Kuesioner::where('status', 'active')->count())
        ]);
    }

    public function create()
    {
        return Inertia::render('Modules/Trace/Admin/QuissionerDetail', [
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

    public function edit($id)
    {
        return $this->show($id);
    }

    public function store(Request $request, SaveKuesionerSectionsAction $saveAction)
    {
        $validated = $request->validate($this->kuesionerRules());

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

                return redirect()->route('quesionnaires.index')->with('success', 'Kuesioner berhasil dibuat');
            });
        } catch (\Exception $e) {
            \Log::error('Kuesioner Save Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan kuesioner: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id, SaveKuesionerSectionsAction $saveAction)
    {
        $kuesioner = Kuesioner::findOrFail($id);

        $validated = $request->validate($this->kuesionerRules());

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

                return redirect()->route('quesionnaires.index')->with('success', 'Kuesioner berhasil diperbarui');
            });
        } catch (\Exception $e) {
            \Log::error('Kuesioner Update Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal memperbarui kuesioner: ' . $e->getMessage()])->withInput();
        }
    }

    public function duplicate($id)
    {
        $kuesioner = Kuesioner::with('sections.pertanyaans.opsiJawabans')->findOrFail($id);

        try {
            return DB::transaction(function () use ($kuesioner) {
                $newKuesioner = $kuesioner->replicate();
                $newKuesioner->judul = $kuesioner->judul . ' (Salinan)';
                $newKuesioner->save();

                foreach ($kuesioner->sections as $section) {
                    $newSection = $section->replicate();
                    $newSection->kuesioner_id = $newKuesioner->id;
                    $newSection->save();

                    foreach ($section->pertanyaans as $pertanyaan) {
                        $newPertanyaan = $pertanyaan->replicate();
                        $newPertanyaan->section_id = $newSection->id;
                        $newPertanyaan->kuesioner_id = $newKuesioner->id;
                        $newPertanyaan->save();

                        foreach ($pertanyaan->opsiJawabans as $option) {
                            $newOption = $option->replicate();
                            $newOption->pertanyaan_id = $newPertanyaan->id;
                            $newOption->save();
                        }
                    }
                }

                return redirect()->route('quesionnaires.index')->with('success', 'Kuesioner berhasil diduplikasi');
            });
        } catch (\Exception $e) {
            \Log::error('Kuesioner Duplicate Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menduplikasi kuesioner: ' . $e->getMessage()]);
        }
    }

    protected function kuesionerRules(): array
    {
        return [
            'judul'        => 'required|string',
            'subtitle'     => 'nullable|string',
            'kategori'     => 'nullable|string|in:Alumni,Stakeholder',
            'tahun'        => 'nullable|integer|min:2000|max:' . (date('Y') + 1),
            'date_mulai'   => 'nullable|date',
            'date_selesai' => 'nullable|date|after_or_equal:date_mulai',
            'deskripsi'    => 'nullable|string',
            'status'       => 'required|string|in:draft,active,published,closed',
            'sections'     => 'nullable|array',
        ];
    }

    protected function resolveTipeKuesioner(string $kategori): string
    {
        return strtolower($kategori) === 'stakeholder' ? 'stakeholder' : 'alumni';
    }

    public function show($id)
    {
        $kuesioner = Kuesioner::with([
            'sections.pertanyaans.opsiJawabans',
        ])->findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/QuissionerDetail', [
            'kuesioner' => $kuesioner,
        ]);
    }

    public function analyticsPage($id)
    {
        $kuesioner = Kuesioner::with([
            'sections.pertanyaans.opsiJawabans',
        ])->findOrFail($id);

        return Inertia::render('Modules/Trace/Admin/QuestionnaireAnalytics', [
            'kuesioner'   => $kuesioner,
            'kuesionerId' => $id,
        ]);
    }

    public function destroy($id)
    {
        $kuesioner = Kuesioner::findOrFail($id);
        ActivityLog::record('kuesioner.deleted', "Menghapus kuesioner: {$kuesioner->judul}", $kuesioner);
        $kuesioner->delete();

        return redirect()->back()->with('success', 'Kuesioner berhasil dihapus');
    }
}
