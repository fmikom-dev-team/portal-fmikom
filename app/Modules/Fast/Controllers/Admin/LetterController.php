<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Models\User;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Services\Shared\OutgoingLetterAttachmentService;
use App\Modules\Fast\Support\FastUserIdentitySearch;
use App\Modules\Fast\Support\TemplateAdminSupport;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
        protected SuratTemplateRendererService $templateService,
        protected TemplateAdminSupport $templateAdminSupport,
        protected OutgoingLetterAttachmentService $outgoingAttachmentService,
    ) {}

    public function create(): Response
    {
        $this->authorize('create', Surat::class);

        $jenisSurats = JenisSurat::query()
            ->with(['category', 'template'])
            ->where('is_active', true)
            ->whereHas('template')
            ->orderBy('nama')
            ->get();

        return Inertia::render('admin/letters/Create', [
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $jenisSurat): array => [
                'id' => $jenisSurat->id,
                'nama' => $jenisSurat->nama,
                'slug' => $jenisSurat->slug,
                'deskripsi' => $jenisSurat->deskripsi,
                'letter_mode' => $this->templateAdminSupport->resolveLetterMode($jenisSurat),
                'letter_mode_label' => $this->templateAdminSupport->letterModeLabel(
                    $this->templateAdminSupport->resolveLetterMode($jenisSurat),
                ),
                'requires_subject_user' => $this->templateAdminSupport->resolveLetterMode($jenisSurat) === TemplateAdminSupport::LETTER_MODE_PERSONAL,
                'category' => [
                    'id' => $jenisSurat->category?->id,
                    'nama' => $jenisSurat->category?->nama,
                ],
                'template' => [
                    'id' => $jenisSurat->template?->id,
                    'name' => $jenisSurat->template?->name,
                ],
            ])->values(),
            'categories' => SuratCategory::orderBy('urutan')
                ->get(['id', 'nama']),
        ]);
    }

    public function selectType(Request $request): RedirectResponse
    {
        $this->authorize('create', Surat::class);

        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        return redirect()->route('admin.surat.form', $validated['jenis_surat_id']);
    }

    public function form(Request $request, JenisSurat $jenisSurat): Response
    {
        $this->authorize('create', Surat::class);

        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole']);

        abort_if(! $jenisSurat->is_active || $jenisSurat->template === null, 404);

        $formData = [
            'jenis_surat_id' => $jenisSurat->id,
            'subject_name' => '',
            'keperluan' => '',
            ...SuratDataContract::adminManualFieldDefaults(),
            'kepada_yth' => [],
            'lampiran_mahasiswa' => [],
            'lampiran_columns' => $this->outgoingAttachmentService->defaultAttachmentColumns(),
            'lampiran_rows' => [],
            'data' => $this->initialDynamicData($jenisSurat),
        ];

        $previewState = $request->session()->get('admin_surat_preview');
        if (
            $request->boolean('resume_preview')
            && is_array($previewState)
            && (int) ($previewState['jenisSuratId'] ?? 0) === $jenisSurat->id
        ) {
            $payload = $previewState['payload'] ?? [];

            if (is_array($payload)) {
                $manualData = SuratDataContract::extractManualDataFromValidatedPayload($payload);
                $dynamicData = is_array($payload['data'] ?? null) ? $payload['data'] : [];

                $formData = array_replace(
                    $formData,
                    [
                        'subject_name' => (string) ($payload['subject_name'] ?? ''),
                        'keperluan' => (string) ($payload['keperluan'] ?? ''),
                        ...array_replace(
                            SuratDataContract::adminManualFieldDefaults(),
                            Arr::only($manualData, SuratDataContract::adminManualScalarFields()),
                        ),
                        'kepada_yth' => is_array($manualData['kepada_yth'] ?? null)
                            ? $manualData['kepada_yth']
                            : [],
                        'lampiran_mahasiswa' => $this->outgoingAttachmentService->normalizeStudentRows(
                            $manualData['lampiran_mahasiswa'] ?? [],
                        ),
                        'lampiran_columns' => $this->outgoingAttachmentService->normalizeAttachmentColumns(
                            $manualData['lampiran_columns'] ?? [],
                        ) ?: $this->outgoingAttachmentService->defaultAttachmentColumns(),
                        'lampiran_rows' => $this->outgoingAttachmentService->normalizeAttachmentRows(
                            $manualData['lampiran_rows'] ?? [],
                            $this->outgoingAttachmentService->normalizeAttachmentColumns($manualData['lampiran_columns'] ?? [])
                                ?: $this->outgoingAttachmentService->defaultAttachmentColumns(),
                        ),
                        'data' => array_replace($formData['data'], $dynamicData),
                    ]
                );
            }
        }

        return Inertia::render('admin/letters/Form', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $formData,
        ]);
    }

    public function searchSubjects(Request $request): JsonResponse
    {
        $this->authorize('create', Surat::class);

        $search = $request->string('q')->trim()->toString();

        $subjects = $this->subjectUserQuery()
            ->with('programStudi:id,nama')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    FastUserIdentitySearch::apply($searchQuery, $search);
                    $searchQuery->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($search === '' ? 10 : 20)
            ->get(FastUserIdentitySearch::selectColumns(['id', 'name', 'email', 'nomor_induk', 'program_studi_id']));

        return response()->json([
            'data' => $subjects
                ->map(fn (User $user): array => $this->serializeSubjectOption($user))
                ->values()
                ->all(),
        ]);
    }

    public function preview(Request $request): RedirectResponse
    {
        $this->authorize('create', Surat::class);

        [$jenisSurat, $payload] = $this->validatedPayload($request);

        $request->session()->put('admin_surat_preview', [
            'jenisSuratId' => $jenisSurat->id,
            'payload' => $payload,
        ]);

        return redirect()->route('admin.surat.preview-page');
    }

    public function previewPage(Request $request): Response
    {
        $this->authorize('create', Surat::class);

        [$jenisSurat, $payload, $previewDocumentHtml, $previewAttachmentHtml] = $this->buildPreviewDocument($request);

        return Inertia::render('admin/letters/Preview', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $payload,
            'subjectSummary' => $this->buildSubjectSummary($payload),
            'renderedHtml' => $previewDocumentHtml,
            'renderedAttachmentHtml' => $previewAttachmentHtml,
            'previewDocumentUrl' => route('admin.surat.preview-html', absolute: false),
        ]);
    }

    public function previewHtml(Request $request): HttpResponse
    {
        $this->authorize('create', Surat::class);

        [, , $previewDocumentHtml] = $this->buildPreviewDocument($request);

        return response($previewDocumentHtml, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Surat::class);

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
        $this->authorize('generate', $surat);

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
        $this->authorize('update', $surat);

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
                'type' => $surat->type,
                'keperluan' => $surat->keperluan,
                'status' => $surat->status,
                'pemohon' => $surat->pemohon ? [
                    'id' => $surat->pemohon->id,
                    'name' => $surat->pemohon->name,
                    'email' => $surat->pemohon->email,
                    'nomor_induk' => $surat->pemohon->nomor_induk,
                ] : null,
            ],
            'returnTo' => $returnTo,
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => [
                'jenis_surat_id' => $jenisSurat->id,
                'subject_name' => (string) (Arr::get($existingData, 'subject_name') ?? Arr::get($existingData, 'nama') ?? ''),
                'keperluan' => $surat->keperluan,
                ...array_replace(
                    SuratDataContract::adminManualFieldDefaults(),
                    Arr::only($manualData, SuratDataContract::adminManualScalarFields()),
                ),
                'kepada_yth' => is_array($manualData['kepada_yth'] ?? null) ? $manualData['kepada_yth'] : [],
                'lampiran_mahasiswa' => $this->outgoingAttachmentService->normalizeStudentRows(
                    $manualData['lampiran_mahasiswa'] ?? [],
                ),
                'lampiran_columns' => $this->outgoingAttachmentService->extractAttachmentColumnsFromSurat($surat),
                'lampiran_rows' => $this->outgoingAttachmentService->extractAttachmentRowsFromSurat($surat),
                'data' => Arr::except($existingData, SuratDataContract::adminManualDataKeys()),
            ],
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $surat = Surat::query()->findOrFail($id);
        $user = $request->user();

        abort_if($user === null, 403);
        $this->authorize('update', $surat);

        [, $payload] = $this->validatedPayload($request, $surat);

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
    protected function validatedPayload(Request $request, ?Surat $existingSurat = null): array
    {
        $validated = $request->validate([
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'subject_name' => ['nullable', 'string', 'max:255'],
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

        $requiresSubjectUser = $existingSurat?->type === 'pengajuan'
            ? false
            : $this->jenisSuratRequiresSubjectUser($jenisSurat);
        $subjectName = trim((string) ($validated['subject_name'] ?? ''));

        if ($requiresSubjectUser && $subjectName === '') {
            throw ValidationException::withMessages([
                'subject_name' => 'Atas nama wajib diisi untuk jenis surat ini.',
            ]);
        }

        $rawDynamicData = $validated['form_data'] ?? $validated['data'] ?? [];
        $lampiranColumns = $this->validateLampiranColumnsPayload(Arr::get($validated, 'lampiran_columns', Arr::get($rawDynamicData, 'lampiran_columns', [])));
        $lampiranRows = $this->validateLampiranRowsPayload(
            Arr::get($validated, 'lampiran_rows', Arr::get($rawDynamicData, 'lampiran_rows', [])),
            $lampiranColumns,
        );
        $lampiranMahasiswa = $this->outgoingAttachmentService->normalizeStudentRows(
            Arr::get($validated, 'lampiran_mahasiswa', Arr::get($rawDynamicData, 'lampiran_mahasiswa', [])),
        );
        $lampiranMode = $this->normalizeLampiranMode(
            (string) Arr::get($validated, 'lampiran_mode', Arr::get($rawDynamicData, 'lampiran_mode', 'none')),
            $lampiranRows,
        );
        $payload = [
            'jenis_surat_id' => (int) $validated['jenis_surat_id'],
            'subject_name' => $subjectName,
            'keperluan' => (string) $validated['keperluan'],
            'data' => $this->workflow->validateDynamicData($jenisSurat, $rawDynamicData),
        ];

        $payload = array_replace(
            $payload,
            SuratDataContract::extractManualDataFromValidatedPayload($validated),
        );
        $payload['data'] = $this->mergeSubjectNameIntoDynamicPayload($payload['data'], $payload['subject_name'] ?? '');
        $payload['kepada_yth'] = $this->normalizeRecipientList($payload['kepada_yth'] ?? []);
        $payload['lampiran_mode'] = $lampiranMode;
        $payload['lampiran_mahasiswa'] = $lampiranMahasiswa;
        $payload['lampiran_columns'] = $lampiranColumns;
        $payload['lampiran_rows'] = $lampiranRows;
        $payload['data']['lampiran_mode'] = $lampiranMode;
        $payload['data']['lampiran_mahasiswa'] = $lampiranMahasiswa;
        $payload['data']['lampiran_columns'] = $lampiranColumns;
        $payload['data']['lampiran_rows'] = $lampiranRows;
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
            'requires_subject_user' => $this->jenisSuratRequiresSubjectUser($jenisSurat),
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

    protected function buildSubjectSummary(array $payload): ?array
    {
        $name = trim((string) ($payload['subject_name'] ?? Arr::get($payload, 'data.subject_name') ?? ''));

        if ($name === '') {
            return null;
        }

        return [
            'id' => null,
            'name' => $name,
            'email' => null,
            'nomor_induk' => null,
            'program_studi' => null,
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeRecipientList(mixed $values): array
    {
        if (! is_array($values)) {
            return [];
        }

        return collect($values)
            ->map(fn ($value): string => trim((string) $value))
            ->filter(fn (string $value): bool => $value !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function mergeSubjectNameIntoDynamicPayload(array $dynamicPayload, string $subjectName): array
    {
        $subjectName = trim($subjectName);

        if ($subjectName === '') {
            return $dynamicPayload;
        }

        foreach (['subject_name', 'nama', 'nama_pemohon', 'nama_mahasiswa', 'nama_dosen'] as $key) {
            if (! filled($dynamicPayload[$key] ?? null)) {
                $dynamicPayload[$key] = $subjectName;
            }
        }

        return $dynamicPayload;
    }

    /**
     * @return array{0: JenisSurat, 1: array<string, mixed>, 2: string, 3: string|null}
     */
    protected function buildPreviewDocument(Request $request): array
    {
        $previewState = $request->session()->get('admin_surat_preview');

        abort_if(! is_array($previewState), 404, 'Data preview surat tidak ditemukan.');

        $jenisSurat = JenisSurat::query()
            ->with(['category', 'template.placeholders', 'approvalRole'])
            ->where('is_active', true)
            ->findOrFail((int) ($previewState['jenisSuratId'] ?? 0));

        $payload = $previewState['payload'] ?? [];
        abort_if(! is_array($payload), 404, 'Payload preview surat tidak valid.');

        $previewData = $payload['data'];

        $previewData = array_replace(
            $previewData,
            SuratDataContract::extractManualDataFromValidatedPayload($payload),
        );

        $previewContext = [
            'approval_role_slug' => $this->approvalRoleSlug($jenisSurat),
            'tanggal_surat' => now(),
            'kota_surat' => \DB::table('template_global_settings')->where('key', 'kota_surat')->value('value') ?? 'Cilacap',
            'pemohon_program_studi_id' => null,
            'surat' => [
                'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
                'keperluan' => $payload['keperluan'],
                'tanggal_pengajuan' => now(),
                'tanggal_kebutuhan' => $payload['tanggal_kebutuhan'] ?? null,
                'type' => 'surat_keluar',
            ],
            'user' => [
                'name' => $payload['subject_name'] ?? Arr::get($previewData, 'subject_name') ?? Arr::get($previewData, 'nama'),
                'email' => null,
                'nim_nip' => Arr::get($previewData, 'nim') ?? Arr::get($previewData, 'nim_mahasiswa'),
                'nomor_induk' => Arr::get($previewData, 'nomor_induk') ?? Arr::get($previewData, 'nomor_induk_mahasiswa'),
                'no_telepon' => Arr::get($previewData, 'telepon_pemohon'),
                'programStudi' => [
                    'nama' => Arr::get($previewData, 'program_studi') ?? Arr::get($previewData, 'program_studi_mahasiswa'),
                ],
            ],
        ];

        $rendered = $this->templateService->renderJenisSuratPreview(
            $jenisSurat,
            $previewData,
            $previewContext,
            'pdf',
        );

        $attachmentColumns = $this->outgoingAttachmentService->normalizeAttachmentColumns($payload['lampiran_columns'] ?? []);
        $attachmentRows = $this->outgoingAttachmentService->normalizeAttachmentRows(
            $payload['lampiran_rows'] ?? [],
            $attachmentColumns,
        );

        return [
            $jenisSurat,
            $payload,
            $this->templateService->wrapDocumentHtml(
                'Preview '.$jenisSurat->nama,
                $rendered['html'],
                $jenisSurat->template,
            ),
            (($payload['lampiran_mode'] ?? 'none') === 'student_list' && $attachmentColumns !== [] && $attachmentRows !== [])
                ? $this->outgoingAttachmentService->buildPreviewHtmlForJenisSuratPreview(
                    $jenisSurat,
                    $previewData,
                    $previewContext,
                    $attachmentColumns,
                    $attachmentRows,
                )
                : null,
        ];
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

    protected function jenisSuratRequiresSubjectUser(JenisSurat $jenisSurat): bool
    {
        return $this->templateAdminSupport->resolveLetterMode($jenisSurat) === TemplateAdminSupport::LETTER_MODE_PERSONAL;
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

    protected function subjectUserQuery()
    {
        return User::query()
            ->select(['id', 'name', 'email', 'nomor_induk', 'program_studi_id']);
    }

    /**
     * @return array{id: int, name: string, email: string, nomor_induk: string|null, program_studi: string|null}
     */
    protected function serializeSubjectOption(User $user): array
    {
        $user->loadMissing('programStudi');

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nomor_induk' => $user->nomor_induk,
            'program_studi' => $user->programStudi?->nama,
        ];
    }

    /**
     * @return array<int, array{key: string, label: string, align: string, bold: bool}>
     */
    protected function validateLampiranColumnsPayload(mixed $columns): array
    {
        $normalizedColumns = $this->outgoingAttachmentService->normalizeAttachmentColumns($columns);

        if ($normalizedColumns === []) {
            return $this->outgoingAttachmentService->defaultAttachmentColumns();
        }

        return $normalizedColumns;
    }

    protected function validateLampiranRowsPayload(mixed $rows, array $columns): array
    {
        $normalizedRows = $this->outgoingAttachmentService->normalizeAttachmentRows($rows, $columns);

        foreach ($normalizedRows as $index => $row) {
            $hasAnyFilledCell = collect($row)->contains(fn (string $value): bool => trim($value) !== '');

            if (! $hasAnyFilledCell) {
                throw ValidationException::withMessages([
                    "lampiran_rows.{$index}" => 'Setiap baris lampiran minimal harus memiliki satu nilai.',
                ]);
            }
        }

        return $normalizedRows;
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     */
    protected function normalizeLampiranMode(string $mode, array $rows): string
    {
        $mode = strtolower(trim($mode));

        if ($rows !== []) {
            return 'student_list';
        }

        return in_array($mode, ['none', 'student_list'], true) ? $mode : 'none';
    }
}
