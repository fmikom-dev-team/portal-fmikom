<?php

namespace App\Modules\Fast\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Models\User;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LetterController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
        protected SuratTemplateRendererService $templateService,
    ) {}

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
                'id' => $jenisSurat->id,
                'nama' => $jenisSurat->nama,
                'slug' => $jenisSurat->slug,
                'deskripsi' => $jenisSurat->deskripsi,
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
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole']);

        abort_if(! $jenisSurat->is_active || $jenisSurat->template === null, 404);

        $formData = [
            'jenis_surat_id' => $jenisSurat->id,
            'subject_user_id' => null,
            'keperluan' => '',
            ...SuratDataContract::adminManualFieldDefaults(),
            'kepada_yth' => [],
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
                        'subject_user_id' => isset($payload['subject_user_id'])
                            ? (int) $payload['subject_user_id']
                            : null,
                        'keperluan' => (string) ($payload['keperluan'] ?? ''),
                        ...array_replace(
                            SuratDataContract::adminManualFieldDefaults(),
                            Arr::only($manualData, SuratDataContract::adminManualScalarFields()),
                        ),
                        'kepada_yth' => is_array($manualData['kepada_yth'] ?? null)
                            ? $manualData['kepada_yth']
                            : [],
                        'data' => array_replace($formData['data'], $dynamicData),
                    ]
                );
            }
        }

        return Inertia::render('admin/letters/Form', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $formData,
            'subjectOptions' => $this->subjectOptions($formData['subject_user_id'] ?? null),
        ]);
    }

    public function searchSubjects(Request $request): JsonResponse
    {
        $search = $request->string('q')->trim()->toString();

        $subjects = $this->subjectUserQuery()
            ->with('programStudi:id,nama')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('nomor_induk', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->limit($search === '' ? 10 : 20)
            ->get(['id', 'name', 'email', 'nomor_induk', 'program_studi_id']);

        return response()->json([
            'data' => $subjects
                ->map(fn (User $user): array => $this->serializeSubjectOption($user))
                ->values()
                ->all(),
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
        [$jenisSurat, $payload, $previewDocumentHtml] = $this->buildPreviewDocument($request);
        $subjectUserId = isset($payload['subject_user_id']) ? (int) $payload['subject_user_id'] : 0;
        $subjectUser = $subjectUserId > 0
            ? User::query()->with('programStudi:id,nama')->find($subjectUserId)
            : null;

        return Inertia::render('admin/letters/Preview', [
            'jenisSurat' => $this->serializeJenisSurat($jenisSurat),
            'formData' => $payload,
            'subjectSummary' => $subjectUser ? [
                'id' => $subjectUser->id,
                'name' => $subjectUser->name,
                'email' => $subjectUser->email,
                'nomor_induk' => $subjectUser->nomor_induk,
                'program_studi' => data_get($subjectUser, 'programStudi.nama'),
            ] : null,
            'renderedHtml' => $previewDocumentHtml,
            'previewDocumentUrl' => route('admin.surat.preview-html', absolute: false),
        ]);
    }

    public function previewHtml(Request $request): HttpResponse
    {
        [, , $previewDocumentHtml] = $this->buildPreviewDocument($request);

        return response($previewDocumentHtml, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
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
                'subject_user_id' => $surat->subject_user_id ?? $surat->pemohon_id,
                'keperluan' => $surat->keperluan,
                ...array_replace(
                    SuratDataContract::adminManualFieldDefaults(),
                    Arr::only($manualData, SuratDataContract::adminManualScalarFields()),
                ),
                'kepada_yth' => is_array($manualData['kepada_yth'] ?? null) ? $manualData['kepada_yth'] : [],
                'data' => Arr::except($existingData, SuratDataContract::adminManualDataKeys()),
            ],
            'subjectOptions' => $this->subjectOptions($surat->subject_user_id ?? $surat->pemohon_id),
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
            'subject_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query): void {
                    $query
                        ->where('is_active', true)
                        ->where('status_approval', 'approved')
                        ->whereIn('user_type', ['mahasiswa', 'dosen', 'alumni'])
                        ->whereNotExists(function ($subQuery): void {
                            $subQuery
                                ->selectRaw('1')
                                ->from('user_module_roles')
                                ->join('modules', 'modules.id', '=', 'user_module_roles.module_id')
                                ->join('roles', 'roles.id', '=', 'user_module_roles.role_id')
                                ->whereColumn('user_module_roles.user_id', 'users.id')
                                ->where('user_module_roles.is_active', true)
                                ->where('modules.code', 'FAST')
                                ->whereIn('roles.slug', ['admin', 'kaprodi', 'dekan']);
                        });
                }),
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
            'subject_user_id' => (int) $validated['subject_user_id'],
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
     * @return array<int, array{id: int, name: string, email: string|null, nomor_induk: string|null, program_studi: string|null}>
     */
    protected function subjectOptions(int|string|null $selectedUserId = null): array
    {
        $selectedUserId = (int) $selectedUserId;

        return $this->subjectUserQuery()
            ->with('programStudi:id,nama')
            ->when($selectedUserId > 0, fn ($query) => $query->whereKey($selectedUserId))
            ->orderBy('name')
            ->limit($selectedUserId > 0 ? 1 : 10)
            ->get(['id', 'name', 'email', 'nomor_induk', 'program_studi_id'])
            ->map(fn (User $user): array => $this->serializeSubjectOption($user))
            ->values()
            ->all();
    }

    protected function serializeSubjectOption(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'nomor_induk' => $user->nomor_induk,
            'program_studi' => data_get($user, 'programStudi.nama'),
        ];
    }

    protected function subjectUserQuery()
    {
        return User::query()
            ->where('is_active', true)
            ->where('status_approval', 'approved')
            ->whereIn('user_type', ['mahasiswa', 'dosen', 'alumni'])
            ->whereDoesntHave('moduleRoles', function ($moduleRoleQuery): void {
                $moduleRoleQuery
                    ->where('is_active', true)
                    ->whereHas('module', fn ($moduleQuery) => $moduleQuery->where('code', 'FAST'))
                    ->whereHas('role', fn ($roleQuery) => $roleQuery->whereIn('slug', ['admin', 'kaprodi', 'dekan']));
            });
    }

    /**
     * @return array{0: JenisSurat, 1: array<string, mixed>, 2: string}
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

        $operator = $request->user()?->loadMissing('programStudi');
        $subjectUserId = isset($payload['subject_user_id']) ? (int) $payload['subject_user_id'] : 0;
        $subjectUser = $subjectUserId > 0
            ? User::query()->with('programStudi')->find($subjectUserId)
            : null;
        $contextUser = $subjectUser ?? $operator;

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
                'pemohon_program_studi_id' => $contextUser?->program_studi_id,
                'surat' => [
                    'nomor_surat' => 'AUTO/GENERATED/AFTER/APPROVAL',
                    'keperluan' => $payload['keperluan'],
                    'tanggal_pengajuan' => now(),
                    'tanggal_kebutuhan' => $payload['tanggal_kebutuhan'] ?? null,
                    'type' => 'surat_keluar',
                ],
                'user' => [
                    'name' => $contextUser?->name,
                    'email' => $contextUser?->email,
                    'nim_nip' => $contextUser?->nim_nip,
                    'nomor_induk' => $contextUser?->nomor_induk,
                    'no_telepon' => $contextUser?->no_telepon,
                    'programStudi' => [
                        'nama' => data_get($contextUser, 'programStudi.nama'),
                    ],
                ],
            ],
            'pdf',
        );

        return [
            $jenisSurat,
            $payload,
            $this->templateService->wrapDocumentHtml(
                'Preview '.$jenisSurat->nama,
                $rendered['html'],
                $jenisSurat->template,
            ),
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
