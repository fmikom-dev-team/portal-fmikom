<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
        protected SuratTemplateRendererService $templateService,
    ) {
    }

    public function create(): Response
    {
        $jenisSurats = JenisSurat::query()
            ->with(['category', 'template'])
            ->where('is_active', true)
            ->whereHas('template')
            ->orderBy('nama')
            ->get();

        return Inertia::render('admin/letters/Create', [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $jenisSurat): array => [
                'id'       => $jenisSurat->id,
                'nama'     => $jenisSurat->nama,
                'slug'     => $jenisSurat->slug,
                'deskripsi'=> $jenisSurat->deskripsi,
                'category' => [
                    'id'   => $jenisSurat->category?->id,
                    'nama' => $jenisSurat->category?->nama,
                ],
                'template' => [
                    'id'   => $jenisSurat->template?->id,
                    'name' => $jenisSurat->template?->name,
                ],
            ])->values(),
            'categories' => \App\Models\SuratCategory::orderBy('urutan')
                                ->get(['id', 'nama']),
        ]);
    }

    public function selectType(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        return redirect()->route('admin.surat.form', $validated['jenis_surat_id']);
    }

    public function form(JenisSurat $jenisSurat): Response
    {
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole']);

        abort_if(! $jenisSurat->is_active || $jenisSurat->template === null, 404);

        return Inertia::render('admin/letters/Form', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => [
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan' => '',
                ...SuratDataContract::adminManualFieldDefaults(),
                'data' => $this->initialDynamicData($jenisSurat),
            ],
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        [$jenisSurat, $payload] = $this->validatedPayload($request);

        $request->session()->put('admin_surat_preview', [
            'jenisSuratId' => $jenisSurat->id,
            'payload' => $payload,
        ]);

        return redirect()->route('admin.surat.preview-page');
    }

    public function previewPage(Request $request): Response
    {
        $previewState = $request->session()->get('admin_surat_preview');

        abort_if(! is_array($previewState), 404, 'Data preview surat tidak ditemukan.');

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) ($previewState['jenisSuratId'] ?? 0));

        $payload = $previewState['payload'] ?? [];
        abort_if(! is_array($payload), 404, 'Payload preview surat tidak valid.');

        $user = $request->user()?->loadMissing('programStudi');

        $previewData = $payload['data'];

        $previewData = array_replace(
            $previewData,
            SuratDataContract::extractManualDataFromValidatedPayload($payload),
        );

        $rendered = $this->templateService->renderJenisSuratPreview(
            $jenisSurat,
            $previewData,
            [
                'approval_role_slug' => $this->approvalRoleSlug($jenisSurat),
                'tanggal_surat' => now(),
                'kota_surat' => \DB::table('template_global_settings')->where('key', 'kota_surat')->value('value') ?? 'Cilacap',    
                'pemohon_program_studi_id' => $user?->program_studi_id,
                'surat' => [
                    'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
                    'keperluan' => $payload['keperluan'],
                    'tanggal_pengajuan' => now(),
                    'tanggal_kebutuhan' => $payload['tanggal_kebutuhan'] ?? null,
                    'type' => 'surat_keluar',
                ],
                'user' => [
                    'name' => $user?->name,
                    'email' => $user?->email,
                    'nim_nip' => $user?->nim_nip,
                    'nomor_induk' => $user?->nomor_induk,
                    'no_telepon' => $user?->no_telepon,
                    'programStudi' => [
                        'nama' => $user?->programStudi?->nama,
                    ],
                ],
            ],
            'pdf',
        );

        return Inertia::render('admin/letters/Preview', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $payload,
            'renderedHtml' => $rendered['html'],
            'previewDocumentHtml' => $this->templateService->wrapDocumentHtml(
                'Preview '.$jenisSurat->nama,
                $rendered['html'],
                $jenisSurat->template,
            ),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [, $payload] = $this->validatedPayload($request);
        $user = $request->user();

        abort_if($user === null, 403);

        $surat = $this->workflow->createOutgoing($user, $payload);

        if ($surat->status === Surat::STATUS_FINISHED) {
            return redirect()
                ->route('admin.dashboard')
                ->with('success', 'Surat berhasil dibuat dan PDF langsung digenerate.')
                ->with('generated_surat_id', $surat->id);
        }

        return redirect()
            ->route('admin.dashboard')
            ->with('success', 'Surat berhasil dibuat dan diteruskan ke '.($surat->finalApprovalRoleSlug() === 'dekan' ? 'Dekan' : 'Kaprodi').' untuk persetujuan.');
    }

    public function generate(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();

        abort_if($user === null, 403);

        $surat = Surat::query()->findOrFail($id);

        abort_if($surat->type !== 'surat_keluar', 404);

        $generated = $this->workflow->generateDocument($surat, $user, true);

        return redirect()
            ->route('admin.surat.generated-document', $generated->id)
            ->with('success', 'PDF surat berhasil digenerate.');
    }

    public function edit(Request $request, int $id): Response
    {
        $surat = Surat::query()
            ->with(['jenisSurat.category', 'jenisSurat.template', 'jenisSurat.approvalRole', 'dataEntries', 'approvalFlows'])
            ->findOrFail($id);

        abort_unless(in_array($surat->status, [Surat::STATUS_PENDING, Surat::STATUS_REVISION_REQUESTED], true), 403);
        abort_unless(auth()->user()?->hasRole('admin'), 403);
        if ($surat->status === Surat::STATUS_REVISION_REQUESTED) {
            abort_unless($surat->latestRevisionRequestFlow() !== null, 403);
        }

        $jenisSurat = $surat->jenisSurat;
        $existingData = $this->workflow->extractExistingData($surat);
        $manualData = SuratDataContract::extractManualDataFromValidatedPayload($existingData);
        $returnTo = $this->safeReturnTo((string) $request->query('return_to', '/admin/surat/'.$surat->id), '/admin/surat/'.$surat->id);

        return Inertia::render('admin/letters/Edit', [
            'surat' => [
                'id' => $surat->id,
                'keperluan' => $surat->keperluan,
                'status' => $surat->status,
            ],
            'returnTo' => $returnTo,
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => [
                'jenis_surat_id' => $jenisSurat->id,
                'keperluan' => $surat->keperluan,
                ...array_replace(
                    SuratDataContract::adminManualFieldDefaults(),
                    Arr::only($manualData, SuratDataContract::adminManualScalarFields()),
                ),
                'kepada_yth' => is_array($manualData['kepada_yth'] ?? null) ? $manualData['kepada_yth'] : [],
                'data' => Arr::except($existingData, SuratDataContract::adminManualDataKeys()),
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $user = $request->user();

        abort_if($user === null, 403);

        [, $payload] = $this->validatedPayload($request);

        $updatedSurat = $surat->status === Surat::STATUS_PENDING
            ? $this->workflow->editPending($surat, $user, $payload)
            : $this->workflow->editRejected($surat, $user, $payload);

        $returnTo = $this->safeReturnTo((string) $request->input('return_to', '/admin/surat/'.$updatedSurat->id), '/admin/surat/'.$updatedSurat->id);

        return redirect()
            ->to($returnTo)
            ->with('success', $surat->status === Surat::STATUS_PENDING
                ? 'Surat berhasil diperbarui dan divalidasi admin.'
                : 'Surat berhasil diperbarui dan diteruskan kembali untuk persetujuan.');
    }

    /**
     * @return array{0: JenisSurat, 1: array{jenis_surat_id: int, keperluan: string, data: array<string, mixed>}}
     */
    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'keperluan' => ['required', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'form_data' => ['nullable', 'array'],
            ...SuratDataContract::adminManualValidationRules(),
        ]);

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) $validated['jenis_surat_id']);

        abort_if($jenisSurat->template === null, 404, 'Template surat belum tersedia.');

        $rawDynamicData = $validated['form_data'] ?? $validated['data'] ?? [];
        $payload = [
            'jenis_surat_id' => (int) $validated['jenis_surat_id'],
            'keperluan' => (string) $validated['keperluan'],
            'data' => $this->workflow->validateDynamicData($jenisSurat, $rawDynamicData),
        ];

        $payload = array_replace(
            $payload,
            SuratDataContract::extractManualDataFromValidatedPayload($validated),
        );
        $payload['data'] = SuratDataContract::mergeManualDataIntoDynamicPayload($payload['data'], $payload);

        return [$jenisSurat, $payload];
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeJenisSurat(JenisSurat $jenisSurat): array
    {
        return [
            'id' => $jenisSurat->id,
            'nama' => $jenisSurat->nama,
            'slug' => $jenisSurat->slug,
            'deskripsi' => $jenisSurat->deskripsi,
            'approval_role_slug' => $this->approvalRoleSlug($jenisSurat),
            'approval_role' => [
                'id' => $jenisSurat->approvalRole?->id,
                'nama' => $jenisSurat->approvalRole?->nama,
                'slug' => $jenisSurat->approvalRole?->slug,
            ],
            'category' => [
                'id' => $jenisSurat->category?->id,
                'nama' => $jenisSurat->category?->nama,
            ],
            'template' => [
                'id' => $jenisSurat->template?->id,
                'name' => $jenisSurat->template?->name,
                'subject' => $jenisSurat->template?->subject,
            ],
            'field_config' => collect(SuratDataContract::filterDynamicFieldConfig($jenisSurat->field_config ?? []))
                ->map(fn (array $field): array => SuratDataContract::normalizeDynamicFieldConfigItem($field))
                ->values()
                ->all(),
        ];
    }

    protected function approvalRoleSlug(JenisSurat $jenisSurat): ?string
    {
        return $jenisSurat->approvalRole?->slug;
    }

    /**
     * @return array<string, mixed>
     */
    protected function initialDynamicData(JenisSurat $jenisSurat): array
    {
        return collect(SuratDataContract::filterDynamicFieldConfig($jenisSurat->field_config ?? []))
            ->mapWithKeys(function (array $field): array {
                $type = strtolower((string) ($field['type'] ?? 'text'));

                if ($type === 'repeatable') {
                    return [(string) $field['name'] => ['']];
                }

                if (in_array($type, ['checkbox-group', 'multiselect'], true)) {
                    return [(string) $field['name'] => []];
                }

                if ($type === 'checkbox') {
                    return [(string) $field['name'] => false];
                }

                return [(string) $field['name'] => ''];
            })
            ->all();
    }

    protected function safeReturnTo(string $returnTo, string $fallback): string
    {
        $returnTo = trim($returnTo);

        if ($returnTo === '') {
            return $fallback;
        }

        if (! str_starts_with($returnTo, '/')) {
            return $fallback;
        }

        if (preg_match('#^//|^[a-zA-Z][a-zA-Z0-9+.-]*:#', $returnTo) === 1) {
            return $fallback;
        }

        return $returnTo;
    }
}


