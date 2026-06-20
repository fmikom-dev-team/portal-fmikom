<?php

namespace App\Modules\Fast\Controllers\Shared\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratCategory;
use App\Modules\Fast\Workflow\Actions\SuratWorkflowService;
use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SubmissionController extends Controller
{
    public function __construct(
        protected SuratWorkflowService $workflow,
    ) {
    }

    /**
     * Halaman "Ajukan Surat": pilih jenis surat, isi form, upload, lalu submit.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $jenisSurats = $this->visibleSubmissionJenisSuratQuery($user)
            ->orderBy('nama')
            ->get(['id', 'category_id', 'nama', 'slug', 'deskripsi', 'field_config']);

        $categoryIds = $jenisSurats->pluck('category_id')->filter()->unique()->values();

        $categories = SuratCategory::query()
            ->where('is_active', true)
            ->when($categoryIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $categoryIds))
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug', 'deskripsi']);

        $visibleJenisSuratCount = $jenisSurats->count();

        $diprosesStatuses = [
            Surat::STATUS_PENDING,
            Surat::STATUS_VALIDATED_ADMIN,
            Surat::STATUS_REVISION_REQUESTED,
            Surat::STATUS_APPROVED_KAPRODI,
            Surat::STATUS_APPROVED_DEKAN,
        ];

        return Inertia::render($this->pageName(), [
            'summary' => (function () use ($user, $diprosesStatuses, $visibleJenisSuratCount): array {
                $counts = Surat::query()
                    ->where('pemohon_id', $user->id)
                    ->selectRaw("
                        SUM(CASE WHEN status IN (" . implode(',', array_fill(0, count($diprosesStatuses), '?')) . ") THEN 1 ELSE 0 END) as diproses,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as selesai,
                        SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as ditolak,
                        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as dibatalkan
                    ", array_merge($diprosesStatuses, [Surat::STATUS_FINISHED, Surat::STATUS_REJECTED_ADMIN, Surat::STATUS_REJECTED_APPROVER, Surat::STATUS_CANCELLED]))
                    ->first();

                $dibatalkan = (int) ($counts->dibatalkan ?? 0);

                return [
                    'total' => $visibleJenisSuratCount,
                    'jenisSuratCount' => $visibleJenisSuratCount,
                    'diproses' => (int) ($counts->diproses ?? 0),
                    'selesai' => (int) ($counts->selesai ?? 0),
                    'ditolak' => (int) ($counts->ditolak ?? 0),
                    'dibatalkan' => $dibatalkan,
                ];
            })(),
            'categories' => $categories->map(fn (SuratCategory $c): array => [
                'id'        => $c->id,
                'nama'      => $c->nama,
                'slug'      => $c->slug,
                'deskripsi' => $c->deskripsi,
            ])->values(),
            'jenisSurats' => $jenisSurats->map(fn (JenisSurat $j): array => [
                'id'          => $j->id,
                'categoryId'  => $j->category_id,
                'nama'        => $j->nama,
                'slug'        => $j->slug,
                'deskripsi'   => $j->deskripsi,
                'fieldConfig' => collect($this->normalizeFieldConfig($j->field_config ?? []))
                    ->map(fn (array $f): array => [
                        'name'        => $f['name'],
                        'label'       => (string) $f['label'],
                        'type'        => $f['type'],
                        'required'    => (bool) $f['required'],
                        'placeholder' => (string) ($f['placeholder'] ?? ''),
                        'help'        => (string) ($f['help'] ?? ''),
                        'options'     => collect($f['options'] ?? [])
                            ->map(fn ($o) => is_array($o)
                                ? ['label' => (string) ($o['label'] ?? $o['value'] ?? ''), 'value' => (string) ($o['value'] ?? '')]
                                : ['label' => (string) $o, 'value' => (string) $o]
                            )->filter(fn ($o) => $o['value'] !== '')->values()->all(),
                        'sumber_data' => (string) ($f['sumber_data'] ?? 'data_pemohon'),
                        'editable_role' => (string) ($f['editable_role'] ?? 'mahasiswa'),
                        'mode_form_pemohon' => (string) ($f['mode_form_pemohon'] ?? 'editable'),
                    ])->values()->all(),
            ])->values(),
            'selectedJenisId' => $request->integer('jenis') ?: null,
            'userType' => [
                'value' => $user->userTypeSlug(),
                'label' => $user->roleDisplayName(),
            ],
            'endpoints' => [
                'basePath' => $this->basePath(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $validated = Validator::make($request->all(), [
            'jenis_surat_id' => [
                'required',
                'integer',
                Rule::exists('jenis_surats', 'id')->where(function ($query) use ($user) {
                    $this->applyVisibleJenisSuratFilter($query, $user);
                }),
            ],
            'keperluan' => ['required', 'string', 'min:10', 'max:1000'],
            'tanggal_kebutuhan' => ['required', 'date', 'after_or_equal:today'],
            'field_data' => ['nullable', 'array'],
            'lampiran' => ['nullable', 'array'],
            'lampiran.*' => ['file', 'max:4096', 'mimes:pdf,jpg,jpeg,png,doc,docx'],
        ], [
            'jenis_surat_id.required'        => 'Jenis surat wajib dipilih.',
            'keperluan.required'             => 'Keperluan wajib diisi.',
            'keperluan.min'                  => 'Keperluan minimal 10 karakter.',
            'keperluan.max'                  => 'Keperluan maksimal 1000 karakter.',
            'tanggal_kebutuhan.required'     => 'Tanggal kebutuhan wajib diisi.',
            'tanggal_kebutuhan.date'         => 'Format tanggal kebutuhan tidak valid.',
            'tanggal_kebutuhan.after_or_equal' => 'Tanggal kebutuhan tidak boleh di masa lalu.',
        ])->validate();

    /** @var JenisSurat $jenisSurat */
        $jenisSurat = $this->visibleSubmissionJenisSuratQuery($user)
            ->findOrFail($validated['jenis_surat_id']);

        $dynamicRules = [];
        $dynamicMessages = [];

        foreach ($this->normalizeFieldConfig($jenisSurat->field_config ?? []) as $field) {
            if ((string) ($field['mode_form_pemohon'] ?? 'editable') !== 'editable') {
                continue;
            }

            $key = 'field_data.'.$field['name'];
            $rules = [($field['required'] ?? false) ? 'required' : 'nullable'];

            $options = collect($field['options'] ?? [])
                ->map(fn ($option) => is_array($option) ? ($option['value'] ?? null) : $option)
                ->filter(fn ($option) => filled($option))
                ->values();

            switch ($field['type']) {
                case 'number':
                    $rules[] = 'numeric';
                    break;
                case 'date':
                    $rules[] = 'date';
                    break;
                case 'email':
                    $rules[] = 'email';
                    break;
                case 'checkbox':
                    $rules[] = 'boolean';
                    break;
                case 'checkbox-group':
                case 'multiselect':
                    $rules[] = 'array';
                    if ($options->isNotEmpty()) {
                        $dynamicRules[$key.'.*'] = [Rule::in($options->all())];
                    }
                    break;
                case 'select':
                case 'radio':
                    $rules[] = 'string';
                    if ($options->isNotEmpty()) {
                        $rules[] = Rule::in($options->all());
                    }
                    break;
                default:
                    $rules[] = 'string';
                    break;
            }

            $dynamicRules[$key] = $rules;
            $dynamicMessages[$key.'.required'] = ($field['label'] ?? $field['name']).' wajib diisi.';
        }

        Validator::make($request->all(), $dynamicRules, $dynamicMessages)->validate();

        $this->workflow->submit(
            $user,
            [
                'jenis_surat_id' => (int) $validated['jenis_surat_id'],
                'keperluan' => (string) $validated['keperluan'],
                'tanggal_kebutuhan' => $validated['tanggal_kebutuhan'],
                'data' => $request->input('field_data', []),
            ],
            $request->file('lampiran', []),
        );

        return redirect()
            ->route($this->dashboardRouteName())
            ->with('success', 'Pengajuan surat berhasil dikirim.');
    }

    protected function pageName(): string
    {
        return 'fast/user/Ajukan';
    }

    protected function basePath(): string
    {
        return '/fast/user';
    }

    protected function dashboardRouteName(): string
    {
        return 'fast.user.dashboard';
    }

    protected function visibleSubmissionJenisSuratQuery($user): Builder
    {
        $roleSlug = $user->userTypeSlug();

        return JenisSurat::query()
            ->where('is_active', true)
            ->where('alur_pengajuan', 'submission')
            ->whereHas('template')
            ->when($roleSlug !== '', fn (Builder $query) => $query->where(fn (Builder $roleQuery) => $roleQuery
                ->whereNull('allowed_role_id')
                ->orWhereHas('allowedRole', fn (Builder $allowedRoleQuery) => $allowedRoleQuery->where('slug', $roleSlug))
            ));
    }

    protected function applyVisibleJenisSuratFilter($query, $user): void
    {
        $roleSlug = $user->userTypeSlug();

        $query->where('is_active', true)
            ->where('alur_pengajuan', 'submission')
            ->when(
                method_exists($query, 'whereHas'),
                fn ($builder) => $builder->whereHas('template'),
                fn ($builder) => $builder->whereExists(function ($templateQuery) {
                    $templateQuery->selectRaw('1')
                        ->from('surat_templates')
                        ->whereColumn('surat_templates.jenis_surat_id', 'jenis_surats.id')
                        ->where('surat_templates.is_active', true);
                })
            )
            ->when($roleSlug !== '', fn ($q) => $q->where(function ($roleQuery) use ($roleSlug) {
                $roleQuery->whereNull('allowed_role_id')
                    ->orWhereExists(function ($exists) use ($roleSlug): void {
                        $exists->selectRaw('1')
                            ->from('roles')
                            ->whereColumn('roles.id', 'jenis_surats.allowed_role_id')
                            ->where('roles.slug', $roleSlug);
                    });
            }));
    }

    /**
     * @param  array<int, mixed>  $fieldConfig
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeFieldConfig(array $fieldConfig): array
    {
        return collect(SuratDataContract::filterDynamicFieldConfig($fieldConfig))
            ->map(fn (array $field): array => SuratDataContract::normalizeDynamicFieldConfigItem($field))
            ->values()
            ->all();
    }

}



