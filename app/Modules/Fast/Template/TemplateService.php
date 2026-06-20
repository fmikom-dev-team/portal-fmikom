<?php

namespace App\Modules\Fast\Template;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\User;
use App\Modules\Fast\Template\Renderers\SuratKomponenRenderer;
use App\Modules\Fast\Template\Parsers\TemplatePlaceholderReplacer;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

class TemplateService
{
    protected const DOCUMENT_STYLES = <<<'CSS'
/* @page { */
    /* margin: 45mm 15mm 30mm 15mm;
    /* margin: 0; */
    /* size: A4 portrait; */ */
/* } */

body {
    background: #fff;
    color: #0f172a;
    font-family: var(--font-family-body, "Times New Roman", serif);
    font-size: 12pt;
    margin: 0;
    padding: 0;
}

.page {
    box-sizing: border-box;
    min-height: 0;
    padding: 0;
}

h1 {
    font-size: 14pt;
    letter-spacing: 0.04em;
    margin-bottom: 8px;
    text-transform: uppercase;
}

p {
    font-size: 12pt;
    line-height: 1.5;
    margin: 0 0 10px;
}

ul,
ol {
    font-size: 12pt;
    line-height: 1.5;
    margin: 0 0 12px 20px;
}

table {
    font-size: 12pt;
    line-height: 1.5;
    width: 100%;
}

td,
th,
span,
div {
    font-size: inherit;
}
CSS;

    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, mixed>}
     */
    public function renderForSurat(Surat $surat, bool $embedQrInTemplate = true, string $renderMode = 'preview'): array
    {
        $surat->loadMissing([
            'pemohon.programStudi',
            'approvedBy.programStudi',
            'jenisSurat.template.placeholders',
            'dataEntries',
        ]);

        $template = $surat->jenisSurat?->template;
        abort_if($template === null, 404, 'Template surat belum tersedia untuk jenis surat ini.');

        $placeholderValues = $this->resolvePlaceholderValues($surat, $template);

        // Tentukan apakah QR aktif
        $suratFinished = $surat->status === 'finished'
            || (!($surat->jenisSurat->perlu_approval ?? true) && !empty($surat->qr_token));

        $rendered = $this->renderTemplate(
            $template,
            $placeholderValues,
            $surat->qr_token,
            $suratFinished,
            $embedQrInTemplate,
            $renderMode
        );

        return [
            'template'     => $template,
            'html'         => $rendered['full'],
            'body'         => $rendered['body'],
            'footer'       => $rendered['footer'],
            'header'       => $rendered['header'],
            'placeholders' => $placeholderValues,
        ];
    }


    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, mixed>}
     */
    public function renderJenisSuratPreview(
        JenisSurat $jenisSurat,
        array $data,
        array $context = [],
        string $renderMode = 'pdf'
    ): array
    {
        $jenisSurat->loadMissing('template.placeholders');

        $template = $jenisSurat->template;
        abort_if($template === null, 404, 'Template surat belum tersedia untuk jenis surat ini.');

        $placeholderValues = $this->resolvePreviewPlaceholderValues($template, $data, $context);

        // Preview mengikuti mode render yang dipakai PDF agar hasil visual konsisten.
        $html = $this->renderTemplate($template, $placeholderValues, null, false, true, $renderMode);

        return [
            'template'     => $template,
            'html'         => $html['full'],
            'placeholders' => $placeholderValues,
        ];
    }


    /**
     * @return array{template: SuratTemplate, html: string, placeholders: array<string, string>}
     */
    public function renderTemplatePreview(SuratTemplate $template, string $renderMode = 'pdf'): array
    {
        $template->loadMissing('placeholders');

        $placeholderValues = $template->placeholders
            ->mapWithKeys(fn ($placeholder): array => [
                $placeholder->placeholder_key => $this->normalizePlaceholderValue(
                    $placeholder->placeholder_key,
                    match ($placeholder->source_type) {
                        'system' => $this->resolveSystemValue(
                            (string) $placeholder->source_key,
                            $placeholder->default_value
                        ),
                        default => $placeholder->default_value,
                    },
                    '[' . $placeholder->label . ']'
                ),
            ])
            ->all();

        // Preview admin mengikuti mode render yang dipakai PDF agar hasil visual konsisten.
        $html = $this->renderTemplate($template, $placeholderValues, null, false, true, $renderMode);

        return [
            'template'     => $template,
            'html'         => $html['full'],
            'placeholders' => $placeholderValues,
        ];
    }


    public function wrapDocumentHtml(string $title, string $html, ?SuratTemplate $template = null): string
    {
        $safeTitle = e($title);

        // Margin dari global settings
        $settings     = \App\Models\TemplateGlobalSetting::allAsArray();
        $marginTop    = $settings['margin_top']    ?? '15mm';
        $marginRight  = $settings['margin_right']  ?? '15mm';
        $marginBottom = $settings['margin_bottom'] ?? '12mm';
        $marginLeft   = $settings['margin_left']   ?? '15mm';

        $fontFamilyKop    = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_kop')
        );
        $fontFamilyBody   = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_body')
        );
        $fontFamilyFooter = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_footer')
        );
        $fontVars = ":root { --font-family-kop: {$fontFamilyKop}; --font-family-body: {$fontFamilyBody}; --font-family-footer: {$fontFamilyFooter}; }";

        $pageStyle  = "@page { margin: {$marginTop} {$marginRight} {$marginBottom} {$marginLeft}; size: A4 portrait; }";
        $styles     = $this->documentStyles();

        // CSS custom per template (margin, indent, dll)
        $customCss = $template?->css_style ? "
        /* Custom CSS per template */
        {$template->css_style}" : '';

                return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>{$safeTitle}</title>
            <style>
                {$fontVars}
                @page { margin: 0; size: A4 portrait; }
                {$styles}
                {$customCss}
                html, body {
                    background: #fff;
                    color: #0f172a;
                    margin: 0;
                    padding: 0;
                    font-family: var(--font-family-body, "Times New Roman", serif);
                    font-size: 12pt;
                }
                .preview-sheet {
                    box-sizing: border-box;
                    position: relative;
                    width: 100%;
                    min-height: 297mm;
                    padding: {$marginTop} {$marginRight} {$marginBottom} {$marginLeft};
                    display: flex;
                    flex-direction: column;
                }
                .preview-sheet__header {
                    width: 100%;
                    box-sizing: border-box;
                }
                .preview-sheet__body {
                    width: 100%;
                    box-sizing: border-box;
                    flex: 1 1 auto;
                }
                .preview-sheet__footer {
                    width: 100%;
                    box-sizing: border-box;
                    margin-top: auto;
                }
                .preview-sheet__footer > * {
                    margin-top: 0;
                }
                .preview-sheet__header,
                .preview-sheet__header * {
                    font-family: var(--font-family-kop, "Times New Roman", serif);
                }
                .preview-sheet__body,
                .preview-sheet__body * {
                    font-family: var(--font-family-body, "Times New Roman", serif);
                }
                .preview-sheet__footer,
                .preview-sheet__footer * {
                    font-family: var(--font-family-footer, "Times New Roman", serif);
                }
                .preview-sheet__body p,
                .preview-sheet__body td,
                .preview-sheet__body th,
                .preview-sheet__body li,
                .preview-sheet__body div {
                    font-weight: 500;
                }
            </style>
        </head>
        <body>
            <div class="page preview-sheet">
                {$html}
            </div>
        </body>
        </html>
        HTML;
    }

    public function documentStyles(): string
    {
        return self::DOCUMENT_STYLES;
    }


    /**
     * @param  array<string, mixed>  $placeholderValues
     */
    protected function renderTemplate(
        SuratTemplate $template,
        array $placeholderValues,
        ?string $qrToken = null,
        bool $suratFinished = false,
        bool $embedQrInTemplate = true,
        string $renderMode = 'preview'
    ): array {//:string
        $body     = (string) ($template->template_body ?? '');
        $settings = \App\Models\TemplateGlobalSetting::allAsArray();

        $qrSvg    = '';
        $qrActive = false;

        if (!empty($qrToken)) {
            $qrActive = $suratFinished;
            if ($qrActive) {
                $baseUrl = config('app.url') ?? 'http://localhost';
                $verifyUrl = rtrim((string) $baseUrl, '/') . '/verifikasi-qr/' . $qrToken;
                $qrSvg     = static::generateQrSvg($verifyUrl);
            }
        }

        $placeholderValues['__qr_svg']    = $qrSvg;
        $placeholderValues['__qr_active'] = $qrActive && $embedQrInTemplate;
        $placeholderValues['__qr_hidden'] = ! $embedQrInTemplate;
        $placeholderValues['__render_mode'] = $renderMode;

        if (\App\Modules\Fast\Template\Renderers\SuratKomponenRenderer::isKomponenJson($body)) {
            $komponen     = json_decode($body, true) ?? [];
            $renderedBody = \App\Modules\Fast\Template\Renderers\SuratKomponenRenderer::render($komponen, $placeholderValues);
        } else {
            $renderedBody = $this->replacePlaceholders($body, $placeholderValues);
        }
        $renderedBody = '<div class="surat-content" style="flex: 1 0 auto;">' . $renderedBody . '</div>';

        $header = !empty(trim((string) ($template->template_header ?? '')))
            ? $this->replacePlaceholders((string) $template->template_header, $placeholderValues)
            : \App\Modules\Fast\Template\Renderers\SuratKomponenRenderer::renderKop($settings, $placeholderValues);

        $footer = !empty(trim((string) ($template->template_footer ?? '')))
            ? $this->replacePlaceholders((string) $template->template_footer, $placeholderValues)
            : \App\Modules\Fast\Template\Renderers\SuratKomponenRenderer::renderFooter($settings);

        // return implode("\n", array_filter([$header, $renderedBody, $footer]));
        return [
            'header' => $header,
            'body'   => $renderedBody,
            'footer' => $footer,
            'full'   => <<<HTML
<div class="preview-sheet__header">{$header}</div>
<div class="preview-sheet__body">{$renderedBody}</div>
<div class="preview-sheet__footer">{$footer}</div>
HTML,
        ];
    }


    protected static function generateQrSvg(string $content): string
    {
        try {
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(128),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $svg = $writer->writeString($content);

            // Deklarasi XML membuat sebagian renderer PDF menampilkannya
            // sebagai teks mentah saat SVG di-inline ke HTML.
            $svg = preg_replace('/^\s*<\?xml[^>]+>\s*/i', '', $svg) ?? $svg;

            return trim($svg);
        } catch (\Throwable $e) {
            return '';
        }
    }


    /**
     * @return array<string, mixed>
     */
    public function resolvePlaceholderValues(Surat $surat, SuratTemplate $template): array
    {
        $suratData = $this->extractSuratData($surat);
        $payload   = $this->buildImplicitPlaceholderPayload(
            $suratData,
            [
                'approval_role_slug' => $surat->finalApprovalRoleSlug(),
                'surat' => [
                    'nomor_surat' => $surat->nomor_surat,
                    'keperluan' => $surat->keperluan,
                    'status' => $surat->status,
                    'tanggal_pengajuan' => $surat->tanggal_pengajuan,
                    'tanggal_kebutuhan' => $surat->tanggal_kebutuhan,
                    'tanggal_selesai' => $surat->tanggal_selesai,
                    'generated_at' => $surat->generated_at,
                ],
                'user' => [
                    'name' => $surat->pemohon?->name,
                    'email' => $surat->pemohon?->email,
                    'nim_nip' => $surat->pemohon?->nim_nip,
                    'nomor_induk' => $surat->pemohon?->nomor_induk,
                    'no_telepon' => $surat->pemohon?->no_telepon,
                    'programStudi' => [
                        'nama' => $surat->pemohon?->programStudi?->nama,
                    ],
                ],
                'signer' => $this->resolveSignerContext(
                    $surat->finalApprovalRoleSlug(),
                    $surat->pemohon?->program_studi_id,
                    $surat->approvedBy,
                ),
            ]
        );

        foreach ($template->placeholders as $placeholder) {
            $key       = (string) $placeholder->placeholder_key;
            $sourceKey = (string) $placeholder->source_key;

            $value = match ($placeholder->source_type) {
                'surat'      => data_get($surat, $sourceKey),
                'user'       => data_get($surat->pemohon, $sourceKey),
                'surat_data' => Arr::get($suratData, $sourceKey),
                'computed'   => $this->resolveComputedValue($surat, $suratData, $sourceKey),
                'system'     => $this->resolveSystemValue($sourceKey, $placeholder->default_value),
                default      => $placeholder->default_value,
            };

            $payload[$key] = $this->normalizePlaceholderValue(
                $key,
                $value,
                $payload[$key] ?? $placeholder->default_value
            );
        }

        if (is_array(Arr::get($suratData, 'kepada_yth'))) {
            $payload['__kepada_yth_items'] = Arr::get($suratData, 'kepada_yth');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    public function resolvePreviewPlaceholderValues(SuratTemplate $template, array $data, array $context = []): array
    {
        $template->loadMissing('placeholders');

        $suratContext = Arr::get($context, 'surat', []);
        $userContext  = Arr::get($context, 'user', []);
        $kaprodiContext = Arr::get(
            $context,
            'signer_kaprodi',
            $this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id'))
        );
        $dekanContext = Arr::get(
            $context,
            'signer_dekan',
            $this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id'))
        );
        $payload      = $this->buildImplicitPlaceholderPayload($data, [
            ...$context,
            'signer' => Arr::get($context, 'signer', $this->resolveSignerContext(
                Arr::get($context, 'approval_role_slug'),
                Arr::get($context, 'pemohon_program_studi_id'),
            )),
        ]);

        foreach ($template->placeholders as $placeholder) {
            $key       = (string) $placeholder->placeholder_key;
            $sourceKey = (string) $placeholder->source_key;

            $value = match ($placeholder->source_type) {
                'surat'      => data_get($suratContext, $sourceKey, Arr::get($context, $sourceKey)),
                'user'       => data_get($userContext, $sourceKey, Arr::get($context, $sourceKey)),
                'surat_data' => Arr::get($data, $sourceKey),
                'computed'   => $this->resolveComputedPreviewValue($data, $context, $sourceKey),
                'system'     => Arr::get($context, $sourceKey, $this->resolveSystemValue($sourceKey, $placeholder->default_value)),
                default      => Arr::get($context, $sourceKey, $placeholder->default_value),
            };

            $payload[$key] = $this->normalizePlaceholderValue(
                $key,
                $value,
                $payload[$key] ?? $placeholder->default_value
            );
        }

        if (is_array(Arr::get($data, 'kepada_yth'))) {
            $payload['__kepada_yth_items'] = Arr::get($data, 'kepada_yth');
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected function buildImplicitPlaceholderPayload(array $data, array $context = []): array
    {
        $suratContext = Arr::get($context, 'surat', []);
        $userContext  = Arr::get($context, 'user', []);
        $kaprodiContext = Arr::get(
            $context,
            'signer_kaprodi',
            $this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id'))
        );
        $dekanContext = Arr::get(
            $context,
            'signer_dekan',
            $this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id'))
        );

        $payload = [
            ...$this->flattenContextPlaceholders($data),
            ...$this->flattenContextPlaceholders($suratContext),
            ...$this->flattenContextPlaceholders($userContext),
            ...$this->flattenContextPlaceholders(Arr::get($context, 'signer', []), 'signer'),
        ];

        $aliases = [
            'nama_pemohon' => Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($userContext, 'name'),
            'nama_mahasiswa' => Arr::get($data, 'nama_mahasiswa')
                ?? Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($userContext, 'name'),
            'nama_mhs' => Arr::get($data, 'nama_mhs')
                ?? Arr::get($data, 'nama_mahasiswa')
                ?? Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($userContext, 'name'),
            'nama_dosen' => Arr::get($data, 'nama_dosen')
                ?? Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($userContext, 'name'),
            'nim_mahasiswa' => Arr::get($data, 'nim_mahasiswa')
                ?? Arr::get($data, 'nim_pemohon')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($userContext, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk'),
            'nip_dosen' => Arr::get($data, 'nip_dosen')
                ?? Arr::get($data, 'nim_nip')
                ?? Arr::get($data, 'nomor_induk')
                ?? Arr::get($userContext, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk'),
            'nip_kaprodi' => Arr::get($data, 'nip_kaprodi')
                ?? Arr::get($kaprodiContext, 'nomor_induk'),
            'nip_dekan' => Arr::get($data, 'nip_dekan')
                ?? Arr::get($dekanContext, 'nomor_induk'),
            'nomor_induk_mahasiswa' => Arr::get($data, 'nomor_induk_mahasiswa')
                ?? Arr::get($data, 'nim_mahasiswa')
                ?? Arr::get($data, 'nim_pemohon')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($userContext, 'nomor_induk')
                ?? Arr::get($userContext, 'nim_nip'),
            'nomor_induk_dosen' => Arr::get($data, 'nomor_induk_dosen')
                ?? Arr::get($data, 'nip_dosen')
                ?? Arr::get($data, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk')
                ?? Arr::get($userContext, 'nim_nip'),
            'nomor_induk_kaprodi' => Arr::get($data, 'nomor_induk_kaprodi')
                ?? Arr::get($data, 'nip_kaprodi')
                ?? Arr::get($kaprodiContext, 'nomor_induk'),
            'nomor_induk_dekan' => Arr::get($data, 'nomor_induk_dekan')
                ?? Arr::get($data, 'nip_dekan')
                ?? Arr::get($dekanContext, 'nomor_induk'),
            'program_studi_mahasiswa' => Arr::get($data, 'program_studi_mahasiswa')
                ?? Arr::get($data, 'program_studi_pemohon')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'program_studi_dosen' => Arr::get($data, 'program_studi_dosen')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'program_studi_kaprodi' => Arr::get($data, 'program_studi_kaprodi')
                ?? Arr::get($kaprodiContext, 'program_studi'),
            'program_studi_dekan' => Arr::get($data, 'program_studi_dekan')
                ?? Arr::get($dekanContext, 'program_studi'),
            'nama_kaprodi' => Arr::get($data, 'nama_kaprodi')
                ?? Arr::get($kaprodiContext, 'name'),
            'nama_dekan' => Arr::get($data, 'nama_dekan')
                ?? Arr::get($dekanContext, 'name'),
            'email_pemohon' => Arr::get($userContext, 'email'),
            'nim_pemohon' => Arr::get($data, 'nim_pemohon')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($userContext, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk'),
            'nomor_induk_pemohon' => Arr::get($data, 'nomor_induk_pemohon')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($data, 'nik')
                ?? Arr::get($userContext, 'nomor_induk')
                ?? Arr::get($userContext, 'nim_nip'),
            'program_studi_pemohon' => Arr::get($data, 'program_studi_pemohon')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'nama_prodi' => Arr::get($data, 'nama_prodi')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($data, 'program_studi_pemohon')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'prodi' => Arr::get($data, 'prodi')
                ?? Arr::get($data, 'nama_prodi')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'telepon_pemohon' => Arr::get($userContext, 'no_telepon'),
            'nama' => Arr::get($data, 'nama')
                ?? Arr::get($data, 'nama_pemohon')
                ?? Arr::get($userContext, 'name'),
            'nim' => Arr::get($data, 'nim')
                ?? Arr::get($data, 'nik')
                ?? Arr::get($userContext, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk'),
            'nik' => Arr::get($data, 'nik')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($userContext, 'nim_nip')
                ?? Arr::get($userContext, 'nomor_induk'),
            'nomor_induk' => Arr::get($data, 'nomor_induk')
                ?? Arr::get($data, 'nim')
                ?? Arr::get($data, 'nik')
                ?? Arr::get($userContext, 'nomor_induk')
                ?? Arr::get($userContext, 'nim_nip'),
            'program_studi' => Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'nama_prodi' => Arr::get($data, 'nama_prodi')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'prodi' => Arr::get($data, 'prodi')
                ?? Arr::get($data, 'nama_prodi')
                ?? Arr::get($data, 'program_studi')
                ?? Arr::get($userContext, 'programStudi.nama'),
            'nama_penanda_tangan' => Arr::get($context, 'signer.name'),
            'email_penanda_tangan' => Arr::get($context, 'signer.email'),
            'nik_penanda_tangan' => Arr::get($context, 'signer.nomor_induk'),
            'nomor_induk_penanda_tangan' => Arr::get($context, 'signer.nomor_induk'),
            'jabatan_penanda_tangan' => Arr::get($context, 'signer.jabatan'),
            'program_studi_penanda_tangan' => Arr::get($context, 'signer.program_studi'),
            'lahir' => Arr::get($data, 'lahir')
                ?? Arr::get($data, 'tempat_tanggal_lahir'),
            'tanggal_surat' => Arr::get($context, 'tanggal_surat'),
            'tanggal_surat_panjang' => $this->resolveComputedPreviewValue($data, $context, 'tanggal_surat_panjang'),
            'kota_surat' => Arr::get($context, 'kota_surat', $this->resolveSystemValue('kota_surat')),
        ];

        foreach ($aliases as $key => $value) {
            if (filled($value) || ! array_key_exists($key, $payload)) {
                $payload[$key] = $this->normalizePlaceholderValue($key, $value, $payload[$key] ?? '');
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    protected function flattenContextPlaceholders(array $source, string $prefix = ''): array
    {
        $flattened = [];

        foreach ($source as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $flatKey = $prefix === '' ? $key : $prefix . '.' . $key;

            if (is_array($value)) {
                $flattened += $this->flattenContextPlaceholders($value, $flatKey);
                continue;
            }

            $flattened[$flatKey] = $this->normalizePlaceholderValue($flatKey, $value, '');

            if ($prefix === '') {
                $flattened[$key] = $this->normalizePlaceholderValue($key, $value, '');
            }
        }

        return $flattened;
    }


    /**
     * @return array<string, mixed>
     */
    protected function extractSuratData(Surat $surat): array
    {
        $dataFromEntries = $surat->dataEntries
            ->mapWithKeys(function ($entry): array {
                $value   = $entry->field_value;
                $decoded = json_decode((string) $value, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = $decoded;
                }
                return [$entry->field_name => $value];
            })
            ->all();

        $isiSurat   = json_decode((string) $surat->isi_surat, true);
        $nestedData = is_array($isiSurat) ? Arr::get($isiSurat, 'data', []) : [];

        return array_replace(
            is_array($nestedData) ? $nestedData : [],
            $dataFromEntries,
        );
    }


    protected function resolveComputedValue(Surat $surat, array $suratData, string $sourceKey): mixed
    {
        return match ($sourceKey) {
            'email_pemohon'             => Arr::get($surat->pemohon, 'email'),
            'nama_mahasiswa'            => Arr::get($surat->pemohon, 'name'),
            'nama_dosen'                => Arr::get($surat->pemohon, 'name'),
            'nim_mahasiswa'             => Arr::get($surat->pemohon, 'nim_nip') ?? Arr::get($surat->pemohon, 'nomor_induk'),
            'nip_dosen'                 => Arr::get($surat->pemohon, 'nim_nip') ?? Arr::get($surat->pemohon, 'nomor_induk'),
            'nomor_induk_mahasiswa'     => Arr::get($surat->pemohon, 'nomor_induk') ?? Arr::get($surat->pemohon, 'nim_nip'),
            'nomor_induk_dosen'         => Arr::get($surat->pemohon, 'nomor_induk') ?? Arr::get($surat->pemohon, 'nim_nip'),
            'program_studi_mahasiswa'   => Arr::get($surat->pemohon, 'programStudi.nama'),
            'program_studi_dosen'       => Arr::get($surat->pemohon, 'programStudi.nama'),
            'nim_pemohon'               => Arr::get($surat->pemohon, 'nim_nip') ?? Arr::get($surat->pemohon, 'nomor_induk'),
            'nomor_induk_pemohon'       => Arr::get($surat->pemohon, 'nomor_induk') ?? Arr::get($surat->pemohon, 'nim_nip'),
            'program_studi_pemohon'     => Arr::get($surat->pemohon, 'programStudi.nama'),
            'telepon_pemohon'           => Arr::get($surat->pemohon, 'no_telepon'),
            'nama_kaprodi'              => $this->resolveSignerContext('kaprodi', $surat->pemohon?->program_studi_id)['name'] ?? null,
            'nip_kaprodi'               => $this->resolveSignerContext('kaprodi', $surat->pemohon?->program_studi_id)['nomor_induk'] ?? null,
            'nomor_induk_kaprodi'       => $this->resolveSignerContext('kaprodi', $surat->pemohon?->program_studi_id)['nomor_induk'] ?? null,
            'program_studi_kaprodi'     => $this->resolveSignerContext('kaprodi', $surat->pemohon?->program_studi_id)['program_studi'] ?? null,
            'nama_dekan'                => $this->resolveSignerContext('dekan', $surat->pemohon?->program_studi_id)['name'] ?? null,
            'nip_dekan'                 => $this->resolveSignerContext('dekan', $surat->pemohon?->program_studi_id)['nomor_induk'] ?? null,
            'nomor_induk_dekan'         => $this->resolveSignerContext('dekan', $surat->pemohon?->program_studi_id)['nomor_induk'] ?? null,
            'program_studi_dekan'       => $this->resolveSignerContext('dekan', $surat->pemohon?->program_studi_id)['program_studi'] ?? null,
            'nama_penanda_tangan'       => $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['name'] ?? null,
            'email_penanda_tangan'      => $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['email'] ?? null,
            'nik_penanda_tangan'        => $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['nomor_induk'] ?? null,
            'nomor_induk_penanda_tangan'=> $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['nomor_induk'] ?? null,
            'jabatan_penanda_tangan'    => $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['jabatan'] ?? null,
            'program_studi_penanda_tangan' => $this->resolveSignerContext($surat->finalApprovalRoleSlug(), $surat->pemohon?->program_studi_id, $surat->approvedBy)['program_studi'] ?? null,
            'semester_terbilang'      => $this->numberToWords(Arr::get($suratData, 'semester')),
            'tanggal_surat_panjang'   => $this->formatDateLong($surat->tanggal_selesai ?? $surat->created_at ?? now()),
            'tanggal_yudisium_panjang'=> $this->formatDateLong(Arr::get($suratData, 'tanggal_yudisium')),
            'tanggal_mulai_panjang'   => $this->formatDateLong(Arr::get($suratData, 'tanggal_mulai')),
            'tanggal_selesai_panjang' => $this->formatDateLong(Arr::get($suratData, 'tanggal_selesai')),
            'tanggal_kegiatan_panjang'=> $this->formatDateLong(Arr::get($suratData, 'tanggal_kegiatan')),
            'tanggal_sidang_panjang'  => $this->formatDateLong(Arr::get($suratData, 'tanggal_sidang')),
            'kelas_info'              => filled(Arr::get($suratData, 'kelas')) ? 'kelas ' . Arr::get($suratData, 'kelas') : '',
            'dosen_pengampu_info'     => filled(Arr::get($suratData, 'dosen_pengampu')) ? ' dengan dosen pengampu ' . Arr::get($suratData, 'dosen_pengampu') : '',
            'judul_tugas_akhir_kalimat' => filled(Arr::get($suratData, 'judul_tugas_akhir')) ? 'Judul tugas akhir: ' . Arr::get($suratData, 'judul_tugas_akhir') : '',
            'ruang_sidang_info'       => filled(Arr::get($suratData, 'ruang_sidang')) ? ' di ruang ' . Arr::get($suratData, 'ruang_sidang') : '',
            default                   => null,
        };
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, mixed>  $context
     */
    protected function resolveComputedPreviewValue(array $data, array $context, string $sourceKey): mixed
    {
        return match ($sourceKey) {
            'email_pemohon'             => Arr::get($context, 'user.email'),
            'nama_mahasiswa'            => Arr::get($context, 'user.name'),
            'nama_dosen'                => Arr::get($context, 'user.name'),
            'nim_mahasiswa'             => Arr::get($context, 'user.nim_nip') ?? Arr::get($context, 'user.nomor_induk'),
            'nip_dosen'                 => Arr::get($context, 'user.nim_nip') ?? Arr::get($context, 'user.nomor_induk'),
            'nomor_induk_mahasiswa'     => Arr::get($context, 'user.nomor_induk') ?? Arr::get($context, 'user.nim_nip'),
            'nomor_induk_dosen'         => Arr::get($context, 'user.nomor_induk') ?? Arr::get($context, 'user.nim_nip'),
            'program_studi_mahasiswa'   => Arr::get($context, 'user.programStudi.nama'),
            'program_studi_dosen'       => Arr::get($context, 'user.programStudi.nama'),
            'nim_pemohon'               => Arr::get($data, 'nim_pemohon') ?? Arr::get($data, 'nim') ?? Arr::get($context, 'user.nim_nip') ?? Arr::get($context, 'user.nomor_induk'),
            'nomor_induk_pemohon'       => Arr::get($data, 'nomor_induk_pemohon') ?? Arr::get($data, 'nim') ?? Arr::get($data, 'nik') ?? Arr::get($context, 'user.nomor_induk') ?? Arr::get($context, 'user.nim_nip'),
            'program_studi_pemohon'     => Arr::get($data, 'program_studi_pemohon') ?? Arr::get($data, 'program_studi') ?? Arr::get($context, 'user.programStudi.nama'),
            'telepon_pemohon'           => Arr::get($context, 'user.no_telepon'),
            'nama_kaprodi'              => Arr::get($context, 'signer_kaprodi.name') ?? Arr::get($this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id')), 'name'),
            'nip_kaprodi'               => Arr::get($context, 'signer_kaprodi.nomor_induk') ?? Arr::get($this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id')), 'nomor_induk'),
            'nomor_induk_kaprodi'       => Arr::get($context, 'signer_kaprodi.nomor_induk') ?? Arr::get($this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id')), 'nomor_induk'),
            'program_studi_kaprodi'     => Arr::get($context, 'signer_kaprodi.program_studi') ?? Arr::get($this->resolveSignerContext('kaprodi', Arr::get($context, 'pemohon_program_studi_id')), 'program_studi'),
            'nama_dekan'                => Arr::get($context, 'signer_dekan.name') ?? Arr::get($this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id')), 'name'),
            'nip_dekan'                 => Arr::get($context, 'signer_dekan.nomor_induk') ?? Arr::get($this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id')), 'nomor_induk'),
            'nomor_induk_dekan'         => Arr::get($context, 'signer_dekan.nomor_induk') ?? Arr::get($this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id')), 'nomor_induk'),
            'program_studi_dekan'       => Arr::get($context, 'signer_dekan.program_studi') ?? Arr::get($this->resolveSignerContext('dekan', Arr::get($context, 'pemohon_program_studi_id')), 'program_studi'),
            'nama_penanda_tangan'       => Arr::get($context, 'signer.name'),
            'email_penanda_tangan'      => Arr::get($context, 'signer.email'),
            'nik_penanda_tangan'        => Arr::get($context, 'signer.nomor_induk'),
            'nomor_induk_penanda_tangan'=> Arr::get($context, 'signer.nomor_induk'),
            'jabatan_penanda_tangan'    => Arr::get($context, 'signer.jabatan'),
            'program_studi_penanda_tangan' => Arr::get($context, 'signer.program_studi'),
            'semester_terbilang'      => $this->numberToWords(Arr::get($data, 'semester')),
            'tanggal_surat_panjang'   => $this->formatDateLong(Arr::get($context, 'tanggal_surat', now())),
            'tanggal_yudisium_panjang'=> $this->formatDateLong(Arr::get($data, 'tanggal_yudisium')),
            'tanggal_mulai_panjang'   => $this->formatDateLong(Arr::get($data, 'tanggal_mulai')),
            'tanggal_selesai_panjang' => $this->formatDateLong(Arr::get($data, 'tanggal_selesai')),
            'tanggal_kegiatan_panjang'=> $this->formatDateLong(Arr::get($data, 'tanggal_kegiatan')),
            'tanggal_sidang_panjang'  => $this->formatDateLong(Arr::get($data, 'tanggal_sidang')),
            'kelas_info'              => filled(Arr::get($data, 'kelas')) ? 'kelas ' . Arr::get($data, 'kelas') : '',
            'dosen_pengampu_info'     => filled(Arr::get($data, 'dosen_pengampu')) ? ' dengan dosen pengampu ' . Arr::get($data, 'dosen_pengampu') : '',
            'judul_tugas_akhir_kalimat' => filled(Arr::get($data, 'judul_tugas_akhir')) ? 'Judul tugas akhir: ' . Arr::get($data, 'judul_tugas_akhir') : '',
            'ruang_sidang_info'       => filled(Arr::get($data, 'ruang_sidang')) ? ' di ruang ' . Arr::get($data, 'ruang_sidang') : '',
            default                   => null,
        };
    }

    protected function resolveSystemValue(string $sourceKey, mixed $fallback = null): mixed
    {
        return match ($sourceKey) {
            'kop_logo_data_uri'     => $this->resolveLogoDataUri($fallback),
            'kota_surat'            => \DB::table('template_global_settings')->where('key', 'kota_surat')->value('value') ?? $fallback ?? 'Cilacap',
            'tanggal_surat_panjang' => now()->locale('id')->isoFormat('D MMMM YYYY'),
            'tanggal_surat'         => now()->locale('id')->isoFormat('D MMMM YYYY'),
            default                 => \DB::table('template_global_settings')->where('key', $sourceKey)->value('value') ?? $fallback,
        };
    }

    protected function resolveLogoDataUri(mixed $fallback = null): string
    {
        $path = public_path('images/kop-logo-temp.png');

        if (!File::exists($path)) {
            return (string) ($fallback ?? '');
        }

        $mimeType = File::mimeType($path) ?: 'image/png';
        $encoded  = base64_encode((string) File::get($path));

        return "data:{$mimeType};base64,{$encoded}";
    }


    protected function normalizePlaceholderValue(string $key, mixed $value, mixed $fallback = ''): string
    {
        $candidate = $value;

        if (blank($candidate)) {
            $candidate = $fallback;
        }

        return TemplatePlaceholderReplacer::stringifyValue($key, $candidate);
    }


    /**
     * @param  array<string, mixed>  $placeholderValues
     */
    protected function replacePlaceholders(string $content, array $placeholderValues): string
    {
        return TemplatePlaceholderReplacer::replace($content, $placeholderValues);
    }



    protected function formatDateLong(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->locale('id')->translatedFormat('d F Y');
        }

        if (blank($value)) {
            return '';
        }

        return now()->parse((string) $value)->locale('id')->translatedFormat('d F Y');
    }

    /**
     * @return array{name: string, email: string, nomor_induk: string, jabatan: string, program_studi: string}
     */
    protected function resolveSignerContext(?string $approvalRoleSlug, ?int $preferredProgramStudiId = null, ?User $approvedBy = null): array
    {
        static $cache = [];

        $cacheKey = implode(':', [
            (string) ($approvalRoleSlug ?? ''),
            (string) ($preferredProgramStudiId ?? ''),
            (string) ($approvedBy?->id ?? ''),
        ]);

        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $signer = null;

        if ($approvedBy !== null && $approvalRoleSlug !== null && $approvedBy->hasRole($approvalRoleSlug)) {
            $signer = $approvedBy->loadMissing('programStudi');
        }

        if ($signer === null && filled($approvalRoleSlug)) {
            $query = User::query()
                ->with(['programStudi'])
                ->where('is_active', true)
                ->where('status_approval', 'approved')
                ->where('user_type', $approvalRoleSlug);

            if ($approvalRoleSlug === 'kaprodi' && $preferredProgramStudiId !== null) {
                $query->orderByRaw(
                    'CASE WHEN program_studi_id = ? THEN 0 WHEN program_studi_id IS NULL THEN 2 ELSE 1 END',
                    [$preferredProgramStudiId]
                );
            }

            $signer = $query
                ->orderBy('id')
                ->first();
        }

        return $cache[$cacheKey] = [
            'name' => (string) ($signer?->name ?? ''),
            'email' => (string) ($signer?->email ?? ''),
            'nomor_induk' => (string) ($signer?->nomor_induk ?? $signer?->nim_nip ?? ''),
            'jabatan' => $this->resolveSignerJabatan($approvalRoleSlug),
            'program_studi' => (string) ($signer?->programStudi?->nama ?? ''),
        ];
    }

    protected function resolveSignerJabatan(?string $approvalRoleSlug): string
    {
        return match ($approvalRoleSlug) {
            'kaprodi' => 'Kaprodi',
            'dekan' => 'Dekan',
            default => '',
        };
    }

    protected function numberToWords(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '';
        }

        $number = (int) $value;

        $words = [
            0  => 'Nol',    1  => 'Satu',    2  => 'Dua',
            3  => 'Tiga',   4  => 'Empat',   5  => 'Lima',
            6  => 'Enam',   7  => 'Tujuh',   8  => 'Delapan',
            9  => 'Sembilan',10 => 'Sepuluh', 11 => 'Sebelas',
            12 => 'Dua Belas', 13 => 'Tiga Belas', 14 => 'Empat Belas',
        ];

        return $words[$number] ?? Str::headline((string) $number);
    }
}


