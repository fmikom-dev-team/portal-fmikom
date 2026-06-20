<?php

namespace App\Modules\Fast\Support;

use App\Models\JenisSurat;
use App\Models\Role;
use App\Models\SuratCategory;
use App\Models\TemplateGlobalSetting;
use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

class TemplateAdminSupport
{
    protected const APPROVAL_ROLE_SLUGS = ['admin', 'dekan', 'kaprodi'];

    protected const CREATOR_ROLE_SLUGS = ['mahasiswa', 'dosen', 'admin'];

    protected const DEFAULT_GLOBAL_SETTINGS = [
        [
            'key'   => 'kode_prefix_nomor_surat',
            'label' => 'Prefix Nomor Surat',
            'value' => 'B',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'kode_fakultas_nomor_surat',
            'label' => 'Kode Fakultas Nomor Surat',
            'value' => 'Ybk.041.10',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'nama_instansi_footer',
            'label' => 'Nama Instansi (Footer)',
            'value' => 'Universitas Nahdlatul Ulama Al Ghazali Cilacap',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'alamat_footer',
            'label' => 'Alamat (Footer)',
            'value' => 'Jl. Kemerdekaan Barat No.17, Cilacap Tengah',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'telepon',
            'label' => 'Telepon',
            'value' => '(0282) 695415, 695407',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'website',
            'label' => 'Website',
            'value' => 'https://unugha.ac.id',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'email',
            'label' => 'Email',
            'value' => 'fmikom@unugha.ac.id',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'fax',
            'label' => 'Fax',
            'value' => '(0282) 695407',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'logo_kop_position',
            'label' => 'Posisi Logo Kop',
            'value' => 'top',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_family_all',
            'label' => 'Font Semua Bagian',
            'value' => 'Times New Roman',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_family_kop',
            'label' => 'Font Kop',
            'value' => 'Times New Roman',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_family_body',
            'label' => 'Font Isi Surat',
            'value' => 'Times New Roman',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_family_footer',
            'label' => 'Font Footer',
            'value' => 'Times New Roman',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_size_kop_instansi',
            'label' => 'Ukuran Font Kop Instansi',
            'value' => '17pt',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_size_kop_fakultas',
            'label' => 'Ukuran Font Kop Fakultas',
            'value' => '13pt',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_size_footer_instansi',
            'label' => 'Ukuran Font Footer Instansi',
            'value' => '8.8pt',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'font_size_footer_detail',
            'label' => 'Ukuran Font Footer Detail',
            'value' => '7.0pt',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'kop_border_thickness',
            'label' => 'Ketebalan Garis Kop',
            'value' => '2px',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'footer_border_thickness',
            'label' => 'Ketebalan Garis Footer',
            'value' => '2px',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'line_height_paragraf',
            'label' => 'Line Height Paragraf',
            'value' => '1.45',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'line_height_tabel',
            'label' => 'Line Height Tabel',
            'value' => '1.65',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'line_height_header',
            'label' => 'Line Height Header',
            'value' => '1.65',
            'tipe'  => 'text',
        ],
        [
            'key'   => 'line_height_yth',
            'label' => 'Line Height Yth',
            'value' => '1.05',
            'tipe'  => 'text',
        ],
    ];

    public function ensureGlobalSettingsExist(): void
    {
        foreach (self::DEFAULT_GLOBAL_SETTINGS as $setting) {
            $model = TemplateGlobalSetting::firstOrNew(['key' => $setting['key']]);

            if (! $model->exists || blank($model->label)) {
                $model->label = $setting['label'];
            }

            if (! $model->exists || blank($model->tipe)) {
                $model->tipe = $setting['tipe'];
            }

            if (! $model->exists || blank($model->value)) {
                $model->value = $setting['value'];
            }

            $model->save();
        }
    }

    public function listJenisSurats(): EloquentCollection
    {
        return JenisSurat::query()
            ->with(['category', 'template'])
            ->orderBy('nama')
            ->get();
    }

    public function listCategories(): EloquentCollection
    {
        return SuratCategory::orderBy('urutan')->get(['id', 'nama']);
    }

    public function listApprovalRoles(): EloquentCollection
    {
        return Role::query()
            ->whereIn('slug', $this->approvalRoleSlugs())
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug']);
    }

    public function listCreatorRoles(): EloquentCollection
    {
        return Role::query()
            ->whereIn('slug', $this->creatorRoleSlugs())
            ->orderBy('nama')
            ->get(['id', 'nama', 'slug']);
    }

    public function listGlobalSettings(): array
    {
        return TemplateGlobalSetting::orderBy('id')
            ->get(['key', 'label', 'value', 'tipe'])
            ->toArray();
    }

    /**
     * @return array<int, int>
     */
    public function templateApprovalRoleIds(): array
    {
        return Role::query()
            ->whereIn('slug', $this->approvalRoleSlugs())
            ->orderBy('nama')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<int, int>
     */
    public function templateAllowedCreatorRoleIds(): array
    {
        return Role::query()
            ->whereIn('slug', $this->creatorRoleSlugs())
            ->orderBy('nama')
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    protected function approvalRoleSlugs(): array
    {
        return self::APPROVAL_ROLE_SLUGS;
    }

    /**
     * @return array<int, string>
     */
    protected function creatorRoleSlugs(): array
    {
        return self::CREATOR_ROLE_SLUGS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function decodeTemplateComponents(string $body): array
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    public function buildLayoutCss(array $layout): ?string
    {
        $marginTop = $layout['margin_top'] ?? 12;
        $marginRight = $layout['margin_right'] ?? 15;
        $marginBottom = $layout['margin_bottom'] ?? 25;
        $marginLeft = $layout['margin_left'] ?? 15;
        $bodyIndent = $layout['body_indent'] ?? null;
        $paragraphIndent = $layout['paragraph_indent'] ?? null;
        $tableIndent = $layout['table_indent'] ?? null;

        $css = [
            '@page {',
            sprintf('    margin: %smm %smm %smm %smm;', $marginTop, $marginRight, $marginBottom, $marginLeft),
            '}',
        ];

        if ($bodyIndent !== null && $bodyIndent !== '') {
            $css[] = '.surat-content {';
            $css[] = sprintf('    padding-left: %smm;', $bodyIndent);
            $css[] = sprintf('    padding-right: %smm;', $bodyIndent);
            $css[] = '}';
        }

        if ($paragraphIndent !== null && $paragraphIndent !== '') {
            $css[] = '.surat-paragraf {';
            $css[] = sprintf('    text-indent: %smm;', $paragraphIndent);
            $css[] = '}';
        }

        if ($tableIndent !== null && $tableIndent !== '') {
            $css[] = '.surat-tabel {';
            $css[] = sprintf('    margin-left: %smm;', $tableIndent);
            $css[] = '}';
        }

        return implode("\n", $css);
    }

    public function serializeJenisSurat(JenisSurat $jenisSurat): array
    {
        $jenisSurat->loadMissing(['category', 'template.placeholders', 'approvalRole', 'allowedRole']);

        return [
            'id'               => $jenisSurat->id,
            'nama'             => $jenisSurat->nama,
            'slug'             => $jenisSurat->slug,
            'kode_surat'       => $jenisSurat->kode_surat,
            'kode_klasifikasi' => $jenisSurat->kode_klasifikasi,
            'deskripsi'        => $jenisSurat->deskripsi,
            'is_active'        => $jenisSurat->is_active,
            'perlu_approval'   => $jenisSurat->perlu_approval,
            'created_at'       => optional($jenisSurat->created_at)?->toISOString(),
            'updated_at'       => optional($jenisSurat->updated_at)?->toISOString(),
            'category' => [
                'id'   => $jenisSurat->category?->id,
                'nama' => $jenisSurat->category?->nama,
            ],
            'approval_role_slug' => $jenisSurat->approvalRole?->slug,
            'approval_role' => [
                'id'   => $jenisSurat->approvalRole?->id,
                'nama' => $jenisSurat->approvalRole?->nama,
                'slug' => $jenisSurat->approvalRole?->slug,
            ],
            'allowed_role' => [
                'id'   => $jenisSurat->allowedRole?->id,
                'nama' => $jenisSurat->allowedRole?->nama,
            ],
            'field_config' => collect(SuratDataContract::filterDynamicFieldConfig($jenisSurat->field_config ?? []))
                ->map(fn (array $field): array => SuratDataContract::normalizeDynamicFieldConfigItem($field))
                ->values()
                ->all(),
            'template' => $jenisSurat->template ? [
                'id'                  => $jenisSurat->template->id,
                'name'                => $jenisSurat->template->name,
                'source_reference'     => $jenisSurat->template->source_reference,
                'subject'             => $jenisSurat->template->subject,
                'template_header'     => $jenisSurat->template->template_header,
                'template_body'       => $jenisSurat->template->template_body,
                'template_footer'     => $jenisSurat->template->template_footer,
                'template_components' => $this->decodeTemplateComponents((string) $jenisSurat->template->template_body),
                'version'             => $jenisSurat->template->version,
                'css_style'           => $jenisSurat->template->css_style,
                'created_at'          => optional($jenisSurat->template->created_at)?->toISOString(),
                'updated_at'          => optional($jenisSurat->template->updated_at)?->toISOString(),
                'preview_url'         => route('admin.templates.preview', $jenisSurat->id, absolute: false),
                'placeholders'        => $jenisSurat->template->placeholders->map(fn ($placeholder): array => [
                    'key'           => $placeholder->placeholder_key,
                    'label'         => $placeholder->label,
                    'source_type'   => $placeholder->source_type,
                    'source_key'    => $placeholder->source_key,
                    'is_required'   => $placeholder->is_required,
                    'default_value' => $placeholder->default_value,
                ])->values(),
            ] : null,
        ];
    }
}
