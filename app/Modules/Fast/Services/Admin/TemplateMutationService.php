<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Support\TemplateAdminSupport;
use App\Modules\Fast\Template\Resolvers\TemplatePlaceholderSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TemplateMutationService
{
    public function __construct(
        protected TemplateAdminSupport $templateAdminSupport,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'kode_surat' => ['nullable', 'string', 'max:50'],
            'kode_klasifikasi' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'exists:surat_categories,id'],
            'deskripsi' => ['nullable', 'string'],
            'letter_mode' => ['nullable', 'in:personal,institution'],
            'allowed_role_id' => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateAllowedCreatorRoleIds())],
            'approval_role_id' => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateApprovalRoleIds())],
            'perlu_approval' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $jenisSurat = JenisSurat::create([
            'nama' => $validated['nama'],
            'slug' => Str::slug($validated['nama']).'-'.time(),
            'kode_surat' => $validated['kode_surat'] ?? null,
            'kode_klasifikasi' => $validated['kode_klasifikasi'] ?? null,
            'category_id' => $validated['category_id'] ?? null,
            'deskripsi' => $validated['deskripsi'] ?? null,
            'allowed_role_id' => $validated['allowed_role_id'] ?? null,
            'approval_role_id' => $validated['approval_role_id'] ?? null,
            'alur_pengajuan' => 'submission',
            'letter_mode' => $validated['letter_mode'] ?? TemplateAdminSupport::LETTER_MODE_PERSONAL,
            'field_config' => [],
            'perlu_approval' => $request->boolean('perlu_approval', false),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return to_route($this->templatesIndexRouteName(), [
            'jenis_surat_id' => $jenisSurat->id,
        ]);
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        $request->validate([
            'template_body' => ['required', 'string'],
            'jenis_surat_nama' => ['nullable', 'string', 'max:255'],
            'kode_klasifikasi' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'exists:surat_categories,id'],
            'field_config' => ['nullable', 'array'],
            'field_config.*.name' => ['nullable', 'string'],
            'field_config.*.label' => ['nullable', 'string'],
            'field_config.*.type' => ['nullable', 'string'],
            'field_config.*.required' => ['nullable', 'boolean'],
            'field_config.*.repeatable' => ['nullable', 'boolean'],
            'field_config.*.add_label' => ['nullable', 'string'],
            'field_config.*.item_label' => ['nullable', 'string'],
            'field_config.*.placeholder' => ['nullable', 'string'],
            'field_config.*.help' => ['nullable', 'string'],
            'field_config.*.options' => ['nullable', 'array'],
            'field_config.*.sumber_data' => ['nullable', 'in:data_pemohon,data_kampus,data_sistem'],
            'field_config.*.editable_role' => ['nullable', 'in:mahasiswa,admin,sistem'],
            'field_config.*.mode_form_pemohon' => ['nullable', 'in:editable,readonly,hidden'],
            'allowed_role_id' => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateAllowedCreatorRoleIds())],
            'approval_role_id' => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateApprovalRoleIds())],
            'layout' => ['nullable', 'array'],
            'letter_mode' => ['nullable', 'in:personal,institution'],
        ]);

        $jenisSuratName = trim((string) $request->input('jenis_surat_nama', ''));
        $existingTemplate = $jenisSurat->template()->first();

        if ($jenisSuratName === '') {
            $jenisSuratName = $jenisSurat->nama;
        }

        // FAST currently uses one active template per letter type.
        // Keep template.name synchronized with jenis surat name for simpler admin UX
        // while preserving the existing database shape for compatibility.
        $templateName = $jenisSuratName;

        $rawFieldConfig = $request->input('field_config');

        if (is_array($rawFieldConfig)) {
            $fieldConfig = collect($rawFieldConfig)
                ->filter(fn ($field): bool => is_array($field))
                ->values()
                ->map(function (array $field, int $index): array {
                    $normalized = SuratDataContract::normalizeDynamicFieldConfigItem($field);
                    $isEmptyRow = $this->isEmptyDynamicFieldConfigRow($normalized);

                    if ($isEmptyRow) {
                        return [];
                    }

                    if (trim((string) ($normalized['name'] ?? '')) === '') {
                        $normalized['name'] = 'field_'.($index + 1);
                    }

                    return $normalized;
                })
                ->filter(fn (array $field): bool => $field !== [])
                ->map(fn (array $field): array => SuratDataContract::normalizeDynamicFieldConfigItem($field))
                ->filter(fn (array $field): bool => filled($field['name'] ?? null))
                ->values()
                ->all();

            $duplicatedNames = collect($fieldConfig)
                ->pluck('name')
                ->filter()
                ->countBy()
                ->filter(fn (int $count): bool => $count > 1)
                ->keys()
                ->values()
                ->all();

            if ($duplicatedNames !== []) {
                throw ValidationException::withMessages([
                    'field_config' => 'Key field duplikat.',
                ]);
            }

            $jenisSurat->forceFill(['field_config' => $fieldConfig]);
        }

        $jenisSurat->fill([
            'nama' => $jenisSuratName,
            'kode_klasifikasi' => $request->input('kode_klasifikasi'),
            'category_id' => $request->input('category_id') ?: null,
            'letter_mode' => $request->input('letter_mode', $jenisSurat->letter_mode ?: TemplateAdminSupport::LETTER_MODE_PERSONAL),
            'allowed_role_id' => $request->input('allowed_role_id') ?: null,
            'approval_role_id' => $request->input('approval_role_id') ?: null,
            'perlu_approval' => $request->boolean('perlu_approval', $jenisSurat->perlu_approval),
            'is_active' => $request->boolean('is_active', $jenisSurat->is_active),
        ]);
        $jenisSurat->save();

        $templateBody = (string) $request->input('template_body');
        $templateHeader = (string) $request->input('template_header', '');
        $templateFooter = (string) $request->input('template_footer', '');
        $letterMode = (string) $request->input('letter_mode', TemplateAdminSupport::LETTER_MODE_PERSONAL);
        $template = $existingTemplate;

        if (! $template) {
            $nextVersion = (int) SuratTemplate::query()
                ->where('jenis_surat_id', $jenisSurat->id)
                ->max('version') + 1;

            $template = SuratTemplate::create([
                'jenis_surat_id' => $jenisSurat->id,
                'name' => $templateName,
                'slug' => sprintf('template-%s-v%d', $jenisSurat->slug ?: Str::slug($jenisSurat->nama), $nextVersion),
                'format' => 'html',
                'template_header' => $templateHeader,
                'template_body' => $templateBody,
                'template_footer' => $templateFooter,
                'subject' => $letterMode,
                'version' => max(1, $nextVersion),
                'is_active' => true,
                'source_reference' => null,
                'css_style' => '',
            ]);
        } else {
            $template->fill([
                'name' => $templateName,
                'template_header' => $templateHeader,
                'template_body' => $templateBody,
                'template_footer' => $templateFooter,
                'subject' => $letterMode,
            ]);

            if ($template->isDirty(['template_body', 'template_header', 'template_footer'])) {
                $template->version = (int) $template->version + 1;
            }

            $template->save();
        }

        TemplatePlaceholderSynchronizer::syncTemplate($template, $jenisSurat->field_config ?? []);

        if ($request->filled('layout')) {
            $css = $this->templateAdminSupport->buildLayoutCss((array) $request->input('layout', []));

            if ($css !== null) {
                $template->fill([
                    'css_style' => $css,
                ])->save();
            }
        }

        return to_route($this->templatesIndexRouteName(), [
            'jenis_surat_id' => $jenisSurat->id,
        ])->with('success', 'Template surat berhasil disimpan.');
    }

    public function duplicate(JenisSurat $jenisSurat): RedirectResponse
    {
        return DB::transaction(function () use ($jenisSurat): RedirectResponse {
            $newName = $jenisSurat->nama.' (Salinan)';

            $copy = $jenisSurat->replicate();
            $copy->fill([
                'nama' => $newName,
                'slug' => Str::slug($newName).'-'.time(),
                'kode_surat' => $jenisSurat->kode_surat ? $jenisSurat->kode_surat.'-COPY' : null,
                'kode_klasifikasi' => $jenisSurat->kode_klasifikasi,
                'is_active' => false,
            ])->save();

            $template = $jenisSurat->template()->first();

            if ($template) {
                $copiedTemplate = $template->replicate();
                $copiedTemplate->fill([
                    'jenis_surat_id' => $copy->id,
                    'name' => $newName,
                    'subject' => in_array((string) $template->subject, [TemplateAdminSupport::LETTER_MODE_PERSONAL, TemplateAdminSupport::LETTER_MODE_INSTITUTION], true)
                        ? $template->subject
                        : TemplateAdminSupport::LETTER_MODE_PERSONAL,
                    'slug' => Str::slug($newName).'-tmpl-'.time(),
                    'is_active' => false,
                    'version' => 1,
                ])->save();

                if ($copiedTemplate) {
                    TemplatePlaceholderSynchronizer::syncTemplate(
                        $copiedTemplate,
                        $copy->field_config ?? [],
                    );
                }
            }

            return to_route($this->templatesIndexRouteName(), [
                'jenis_surat_id' => $copy->id,
            ])->with('success', 'Template surat berhasil diduplikasi.');
        });
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function isEmptyDynamicFieldConfigRow(array $field): bool
    {
        return trim((string) ($field['name'] ?? '')) === ''
            && trim((string) ($field['label'] ?? '')) === ''
            && trim((string) ($field['placeholder'] ?? '')) === ''
            && trim((string) ($field['help'] ?? '')) === ''
            && empty($field['options'] ?? [])
            && (bool) ($field['required'] ?? false) === false
            && (string) ($field['type'] ?? 'text') === 'text'
            && (string) ($field['sumber_data'] ?? 'data_pemohon') === 'data_pemohon'
            && (string) ($field['editable_role'] ?? 'mahasiswa') === 'mahasiswa'
            && in_array((string) ($field['mode_form_pemohon'] ?? 'readonly'), ['editable', 'readonly'], true);
    }

    protected function templatesIndexRouteName(): string
    {
        $role = strtolower((string) (auth()->user()?->getResolvedRoleSlug() ?? auth()->user()?->getGlobalRoleSlug() ?? 'admin'));

        return in_array($role, ['kaprodi', 'dekan'], true) ? "{$role}.admin.templates.index" : 'admin.templates.index';
    }
}
