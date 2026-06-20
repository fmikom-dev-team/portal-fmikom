<?php

namespace App\Modules\Fast\Services\Admin;

use App\Models\JenisSurat;
use App\Models\SuratTemplate;
use App\Modules\Fast\DTOs\SuratDataContract;
use App\Modules\Fast\Template\Resolvers\TemplatePlaceholderSynchronizer;
use App\Modules\Fast\Support\TemplateAdminSupport;
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
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'             => ['required', 'string', 'max:255'],
            'kode_surat'       => ['nullable', 'string', 'max:50'],
            'kode_klasifikasi' => ['nullable', 'string', 'max:50'],
            'category_id'      => ['nullable', 'exists:surat_categories,id'],
            'deskripsi'        => ['nullable', 'string'],
            'allowed_role_id'  => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateAllowedCreatorRoleIds())],
            'approval_role_id' => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateApprovalRoleIds())],
            'perlu_approval'   => ['nullable', 'boolean'],
            'is_active'        => ['nullable', 'boolean'],
        ]);

        $jenisSurat = JenisSurat::create([
            'nama'             => $validated['nama'],
            'slug'             => Str::slug($validated['nama']) . '-' . time(),
            'kode_surat'       => $validated['kode_surat'] ?? null,
            'kode_klasifikasi' => $validated['kode_klasifikasi'] ?? null,
            'category_id'      => $validated['category_id'] ?? null,
            'deskripsi'        => $validated['deskripsi'] ?? null,
            'allowed_role_id'  => $validated['allowed_role_id'] ?? null,
            'approval_role_id' => $validated['approval_role_id'] ?? null,
            'alur_pengajuan'   => 'submission',
            'field_config'     => [],
            'perlu_approval'   => $request->boolean('perlu_approval', false),
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return to_route('admin.templates.index', [
            'jenis_surat_id' => $jenisSurat->id,
        ]);
    }

    public function update(Request $request, JenisSurat $jenisSurat): RedirectResponse
    {
        $request->validate([
            'template_body'              => ['required', 'string'],
            'name'                       => ['nullable', 'string', 'max:255'],
            'jenis_surat_nama'           => ['nullable', 'string', 'max:255'],
            'field_config'               => ['nullable', 'array'],
            'field_config.*.name'        => ['nullable', 'string'],
            'field_config.*.label'       => ['nullable', 'string'],
            'field_config.*.type'        => ['nullable', 'string'],
            'field_config.*.required'    => ['nullable', 'boolean'],
            'field_config.*.repeatable'  => ['nullable', 'boolean'],
            'field_config.*.add_label'   => ['nullable', 'string'],
            'field_config.*.item_label'  => ['nullable', 'string'],
            'field_config.*.placeholder' => ['nullable', 'string'],
            'field_config.*.help'        => ['nullable', 'string'],
            'field_config.*.options'     => ['nullable', 'array'],
            'field_config.*.sumber_data' => ['nullable', 'in:data_pemohon,data_kampus,data_sistem'],
            'field_config.*.editable_role' => ['nullable', 'in:mahasiswa,admin,sistem'],
            'field_config.*.mode_form_pemohon' => ['nullable', 'in:editable,readonly,hidden'],
            'allowed_role_id'            => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateAllowedCreatorRoleIds())],
            'approval_role_id'           => ['nullable', 'integer', Rule::in($this->templateAdminSupport->templateApprovalRoleIds())],
            'layout'                     => ['nullable', 'array'],
        ]);

        if ($request->filled('field_config')) {
            $fieldConfig = collect(SuratDataContract::filterDynamicFieldConfig($request->input('field_config', [])))
                ->map(fn (array $config): array => SuratDataContract::normalizeDynamicFieldConfigItem($config))
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
                    'field_config' => 'Key field dinamis harus unik. Duplikat: ' . implode(', ', $duplicatedNames),
                ]);
            }

            $jenisSurat->field_config = $fieldConfig;
        }

        $jenisSurat->fill([
            'nama'             => $request->input('name') ?: $request->input('jenis_surat_nama') ?: $jenisSurat->nama,
            'allowed_role_id'  => $request->input('allowed_role_id', $jenisSurat->allowed_role_id),
            'approval_role_id' => $request->input('approval_role_id', $jenisSurat->approval_role_id),
        ]);
        $jenisSurat->save();

        $templateBody = (string) $request->input('template_body');
        $templateHeader = (string) $request->input('template_header', '');
        $templateFooter = (string) $request->input('template_footer', '');
        $template = $jenisSurat->template()->first();
        $templateName = $request->input('name') ?: $request->input('jenis_surat_nama') ?: $jenisSurat->nama;

        if (! $template) {
            $nextVersion = (int) SuratTemplate::query()
                ->where('jenis_surat_id', $jenisSurat->id)
                ->max('version') + 1;

            $template = SuratTemplate::create([
                'jenis_surat_id'   => $jenisSurat->id,
                'name'             => $templateName,
                'slug'             => sprintf('template-%s-v%d', $jenisSurat->slug ?: Str::slug($jenisSurat->nama), $nextVersion),
                'format'           => 'html',
                'template_header'  => $templateHeader,
                'template_body'    => $templateBody,
                'template_footer'  => $templateFooter,
                'subject'          => $templateName,
                'version'          => max(1, $nextVersion),
                'is_active'        => true,
                'source_reference' => null,
                'css_style'        => '',
            ]);
        } else {
            $template->fill([
                'name'            => $templateName,
                'template_header'  => $templateHeader,
                'template_body'    => $templateBody,
                'template_footer'  => $templateFooter,
                'subject'          => $templateName,
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

        return to_route('admin.templates.index', [
            'jenis_surat_id' => $jenisSurat->id,
        ])->with('success', 'Template surat berhasil disimpan.');
    }

    public function duplicate(JenisSurat $jenisSurat): RedirectResponse
    {
        return DB::transaction(function () use ($jenisSurat): RedirectResponse {
            $newName = $jenisSurat->nama . ' (Salinan)';

            $copy = $jenisSurat->replicate();
            $copy->fill([
                'nama'             => $newName,
                'slug'             => Str::slug($newName) . '-' . time(),
                'kode_surat'       => $jenisSurat->kode_surat ? $jenisSurat->kode_surat . '-COPY' : null,
                'kode_klasifikasi' => $jenisSurat->kode_klasifikasi,
                'is_active'        => false,
            ])->save();

            $template = $jenisSurat->template()->first();

            if ($template) {
                $copiedTemplate = $template->replicate();
                $copiedTemplate->fill([
                    'jenis_surat_id' => $copy->id,
                    'name'           => $newName,
                    'subject'        => $newName,
                    'slug'           => Str::slug($newName) . '-tmpl-' . time(),
                    'is_active'      => false,
                    'version'        => 1,
                ])->save();

                if ($copiedTemplate) {
                    TemplatePlaceholderSynchronizer::syncTemplate(
                        $copiedTemplate,
                        $copy->field_config ?? [],
                    );
                }
            }

            return to_route('admin.templates.index', [
                'jenis_surat_id' => $copy->id,
            ])->with('success', 'Template surat berhasil diduplikasi.');
        });
    }
}
