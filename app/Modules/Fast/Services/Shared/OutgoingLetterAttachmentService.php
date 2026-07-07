<?php

namespace App\Modules\Fast\Services\Shared;

use App\Models\JenisSurat;
use App\Models\Surat;
use App\Models\SuratTemplate;
use App\Models\TemplateGlobalSetting;
use App\Modules\Fast\Template\Renderers\SuratKomponenRenderer;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use App\Support\FastStorage;
use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Symfony\Component\Process\Process;

class OutgoingLetterAttachmentService
{
    /**
     * @return array<int, array{key: string, label: string, align: string, bold: bool}>
     */
    public function defaultAttachmentColumns(): array
    {
        return [
            ['key' => 'col_1', 'label' => 'No', 'align' => 'center', 'bold' => true],
            ['key' => 'col_2', 'label' => 'Nama Mahasiswa', 'align' => 'left', 'bold' => true],
            ['key' => 'col_3', 'label' => 'NIM', 'align' => 'center', 'bold' => true],
            ['key' => 'col_4', 'label' => 'Program Studi', 'align' => 'left', 'bold' => true],
        ];
    }

    public function __construct(
        protected SuratTemplateRendererService $renderer,
    ) {}

    /**
     * @return array<int, array{nama: string, nim: string, prodi: string}>
     */
    public function normalizeStudentRows(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row): array {
                return [
                    'nama' => trim((string) ($row['nama'] ?? '')),
                    'nim' => trim((string) ($row['nim'] ?? '')),
                    'prodi' => trim((string) ($row['prodi'] ?? '')),
                ];
            })
            ->filter(function (array $row): bool {
                return $row['nama'] !== '' || $row['nim'] !== '' || $row['prodi'] !== '';
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{nama: string, nim: string, prodi: string}>
     */
    public function extractStudentRowsFromSurat(Surat $surat): array
    {
        $surat->loadMissing('dataEntries');

        $rawRows = $surat->dataEntries
            ->firstWhere('field_name', 'lampiran_mahasiswa')
            ?->field_value;

        if (is_string($rawRows)) {
            $decoded = json_decode($rawRows, true);

            return $this->normalizeStudentRows($decoded);
        }

        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return $this->normalizeStudentRows(Arr::get($isiSurat, 'data.lampiran_mahasiswa'));
    }

    public function hasStudentAttachment(Surat $surat): bool
    {
        $mode = $this->extractAttachmentMode($surat);

        return $mode === 'student_list' && $this->extractAttachmentRowsFromSurat($surat) !== [];
    }

    public function extractAttachmentMode(Surat $surat): string
    {
        $surat->loadMissing('dataEntries');

        $storedMode = $surat->dataEntries
            ->firstWhere('field_name', 'lampiran_mode')
            ?->field_value;

        $mode = strtolower(trim((string) $storedMode));

        if (in_array($mode, ['none', 'student_list'], true)) {
            return $mode;
        }

        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $fallbackMode = strtolower(trim((string) Arr::get($isiSurat, 'data.lampiran_mode', '')));

        return in_array($fallbackMode, ['none', 'student_list'], true)
            ? $fallbackMode
            : 'none';
    }

    public function attachmentOutputPath(Surat $surat): string
    {
        return sprintf('fast/generated/surat-%d-lampiran-daftar-mahasiswa.pdf', $surat->id);
    }

    public function attachmentFileName(Surat $surat): string
    {
        $slug = Str::slug((string) ($surat->jenisSurat?->nama ?: 'surat'));

        return sprintf('%s-%d-lampiran-daftar-mahasiswa.pdf', $slug, $surat->id);
    }

    /**
     * @return array<int, array{key: string, label: string, align: string, bold: bool}>
     */
    public function normalizeAttachmentColumns(mixed $columns): array
    {
        if (! is_array($columns)) {
            return [];
        }

        $normalized = collect($columns)
            ->filter(fn ($column): bool => is_array($column))
            ->map(function (array $column, int $index): array {
                $key = trim((string) ($column['key'] ?? ''));

                return [
                    'key' => $key !== '' ? $key : 'col_'.($index + 1),
                    'label' => trim((string) ($column['label'] ?? 'Kolom '.($index + 1))),
                    'align' => $this->normalizeAttachmentAlign($column['align'] ?? 'left'),
                    'bold' => $this->normalizeAttachmentTitleBold($column['bold'] ?? true),
                ];
            })
            ->filter(fn (array $column): bool => $column['label'] !== '')
            ->values()
            ->all();

        return $normalized;
    }

    /**
     * @param  array<int, array{key: string, label: string, align: string, bold: bool}>  $columns
     * @return array<int, array<string, string>>
     */
    public function normalizeAttachmentRows(mixed $rows, array $columns): array
    {
        if (! is_array($rows) || $columns === []) {
            return [];
        }

        $columnKeys = collect($columns)->pluck('key')->all();

        return collect($rows)
            ->filter(fn ($row): bool => is_array($row))
            ->map(function (array $row) use ($columnKeys): array {
                $normalized = [];

                foreach ($columnKeys as $key) {
                    $normalized[$key] = trim((string) ($row[$key] ?? ''));
                }

                return $normalized;
            })
            ->filter(function (array $row): bool {
                foreach ($row as $cell) {
                    if ($cell !== '') {
                        return true;
                    }
                }

                return false;
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string, align: string, bold: bool}>
     */
    public function extractAttachmentColumnsFromSurat(Surat $surat): array
    {
        $surat->loadMissing('dataEntries');

        $rawColumns = $surat->dataEntries
            ->firstWhere('field_name', 'lampiran_columns')
            ?->field_value;

        if (is_string($rawColumns)) {
            $decoded = json_decode($rawColumns, true);
            $normalized = $this->normalizeAttachmentColumns($decoded);

            if ($normalized !== []) {
                return $normalized;
            }
        }

        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $normalized = $this->normalizeAttachmentColumns(Arr::get($isiSurat, 'data.lampiran_columns'));

        if ($normalized !== []) {
            return $normalized;
        }

        return $this->defaultAttachmentColumns();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function extractAttachmentRowsFromSurat(Surat $surat): array
    {
        $columns = $this->extractAttachmentColumnsFromSurat($surat);
        $surat->loadMissing('dataEntries');

        $rawRows = $surat->dataEntries
            ->firstWhere('field_name', 'lampiran_rows')
            ?->field_value;

        if (is_string($rawRows)) {
            $decoded = json_decode($rawRows, true);
            $normalized = $this->normalizeAttachmentRows($decoded, $columns);

            if ($normalized !== []) {
                return $normalized;
            }
        }

        $isiSurat = json_decode((string) $surat->isi_surat, true);
        $normalized = $this->normalizeAttachmentRows(Arr::get($isiSurat, 'data.lampiran_rows'), $columns);

        if ($normalized !== []) {
            return $normalized;
        }

        return $this->mapLegacyStudentRowsToAttachmentRows(
            $this->extractStudentRowsFromSurat($surat),
            $columns,
        );
    }

    public function extractAttachmentTitle(Surat $surat): string
    {
        $surat->loadMissing('dataEntries');

        $storedTitle = $surat->dataEntries
            ->firstWhere('field_name', 'lampiran_judul')
            ?->field_value;

        $title = trim((string) $storedTitle);

        if ($title !== '') {
            return $title;
        }

        $isiSurat = json_decode((string) $surat->isi_surat, true);

        return trim((string) Arr::get($isiSurat, 'data.lampiran_judul', ''));
    }

    public function extractAttachmentOrientation(Surat $surat): string
    {
        return $this->extractAttachmentScalar($surat, 'lampiran_orientation', 'portrait', ['portrait', 'landscape']);
    }

    public function extractAttachmentTitleAlign(Surat $surat): string
    {
        return $this->extractAttachmentScalar($surat, 'lampiran_judul_align', 'center', ['left', 'center', 'right']);
    }

    public function extractAttachmentTitleBold(Surat $surat): bool
    {
        return $this->normalizeAttachmentTitleBold(
            $this->extractAttachmentScalar($surat, 'lampiran_judul_bold', '1'),
        );
    }

    /**
     * @return array{no: string, nama: string, nim: string, prodi: string}
     */
    public function extractAttachmentColumnLabels(Surat $surat): array
    {
        return [
            'no' => $this->extractAttachmentScalar($surat, 'lampiran_label_no', 'No'),
            'nama' => $this->extractAttachmentScalar($surat, 'lampiran_label_nama', 'Nama Mahasiswa'),
            'nim' => $this->extractAttachmentScalar($surat, 'lampiran_label_nim', 'NIM'),
            'prodi' => $this->extractAttachmentScalar($surat, 'lampiran_label_prodi', 'Program Studi'),
        ];
    }

    public function ensureGeneratedPdf(Surat $surat): ?string
    {
        $columns = $this->extractAttachmentColumnsFromSurat($surat);
        $rows = $this->extractAttachmentRowsFromSurat($surat);

        if ($this->extractAttachmentMode($surat) !== 'student_list' || $columns === [] || $rows === []) {
            FastStorage::delete([$this->attachmentOutputPath($surat)]);

            return null;
        }

        $outputPath = $this->attachmentOutputPath($surat);
        FastStorage::makeDirectory(dirname($outputPath), 'local');
        FastStorage::put(
            $outputPath,
            $this->renderPdfOutput($surat, $columns, $rows),
            'local',
        );

        return $outputPath;
    }

    /**
     * Build the attachment section that can be appended to the main surat PDF.
     *
     * @return array{html: string, orientation: string}|null
     */
    public function buildBundleAttachmentSection(Surat $surat): ?array
    {
        $columns = $this->extractAttachmentColumnsFromSurat($surat);
        $rows = $this->extractAttachmentRowsFromSurat($surat);

        if ($this->extractAttachmentMode($surat) !== 'student_list' || $columns === [] || $rows === []) {
            return null;
        }

        $surat->loadMissing([
            'jenisSurat.template.placeholders',
            'pemohon.programStudi',
            'subjectUser.programStudi',
            'approvedBy.programStudi',
            'dataEntries',
        ]);

        $orientation = $this->extractAttachmentOrientation($surat);

        return [
            'html' => $this->renderAttachmentBody(
                (string) ($surat->nomor_surat ?: '-'),
                $this->formatAttachmentDate($surat->tanggal_selesai ?? $surat->generated_at ?? $surat->created_at),
                $this->extractAttachmentTitle($surat),
                $orientation,
                $this->extractAttachmentTitleAlign($surat),
                $this->extractAttachmentTitleBold($surat),
                $columns,
                $rows,
            ),
            'orientation' => $orientation,
        ];
    }

    public function buildPreviewHtmlForSurat(Surat $surat, array $columns, array $rows): string
    {
        $surat->loadMissing([
            'jenisSurat.template.placeholders',
            'pemohon.programStudi',
            'subjectUser.programStudi',
            'approvedBy.programStudi',
            'dataEntries',
        ]);

        $rendered = $this->renderer->renderForSurat($surat, true, 'pdf');

        return $this->buildWrappedPreviewHtml(
            'Lampiran '.(string) ($surat->jenisSurat?->nama ?: 'Surat'),
            $surat->jenisSurat?->template,
            $this->renderAttachmentBody(
                (string) ($surat->nomor_surat ?: '-'),
                $this->formatAttachmentDate($surat->tanggal_selesai ?? $surat->generated_at ?? $surat->created_at),
                $this->extractAttachmentTitle($surat),
                $this->extractAttachmentOrientation($surat),
                $this->extractAttachmentTitleAlign($surat),
                $this->extractAttachmentTitleBold($surat),
                $columns,
                $rows,
            ),
            $this->extractAttachmentOrientation($surat),
        );
    }

    /**
     * @param  array<string, mixed>  $previewData
     * @param  array<string, mixed>  $context
     */
    public function buildPreviewHtmlForJenisSuratPreview(
        JenisSurat $jenisSurat,
        array $previewData,
        array $context,
        array $columns,
        array $rows,
        string $nomorSurat = 'AUTO/GENERATED/AFTER/APPROVAL',
    ): string {
        $jenisSurat->loadMissing('template.placeholders');

        $rendered = $this->renderer->renderJenisSuratPreview(
            $jenisSurat,
            $previewData,
            $context,
            'pdf',
        );

        return $this->buildWrappedPreviewHtml(
            'Lampiran '.$jenisSurat->nama,
            $jenisSurat->template,
            $this->renderAttachmentBody(
                $nomorSurat,
                $this->formatAttachmentDate($context['tanggal_surat'] ?? now()),
                trim((string) Arr::get($previewData, 'lampiran_judul', '')),
                $this->normalizeAttachmentOrientation(Arr::get($previewData, 'lampiran_orientation', 'portrait')),
                $this->normalizeAttachmentAlign(Arr::get($previewData, 'lampiran_judul_align', 'center')),
                $this->normalizeAttachmentTitleBold(Arr::get($previewData, 'lampiran_judul_bold', '1')),
                $columns,
                $rows,
            ),
            $this->normalizeAttachmentOrientation(Arr::get($previewData, 'lampiran_orientation', 'portrait')),
        );
    }

    protected function renderPdfOutput(Surat $surat, array $columns, array $rows): string
    {
        $surat->loadMissing([
            'jenisSurat.template.placeholders',
            'pemohon.programStudi',
            'subjectUser.programStudi',
            'approvedBy.programStudi',
            'dataEntries',
        ]);

        $rendered = $this->renderer->renderForSurat($surat, true, 'pdf');
        $bodyHtml = $this->renderAttachmentBody(
            (string) ($surat->nomor_surat ?: '-'),
            $this->formatAttachmentDate($surat->tanggal_selesai ?? $surat->generated_at ?? $surat->created_at),
            $this->extractAttachmentTitle($surat),
            $this->extractAttachmentOrientation($surat),
            $this->extractAttachmentTitleAlign($surat),
            $this->extractAttachmentTitleBold($surat),
            $columns,
            $rows,
        );
        $orientation = $this->extractAttachmentOrientation($surat);
        $html = $this->buildWrappedPreviewHtml(
            'Lampiran '.(string) ($surat->jenisSurat?->nama ?: 'Surat'),
            $surat->jenisSurat?->template,
            $bodyHtml,
            $orientation,
        );

        $browserPdf = $this->renderPdfOutputWithChrome($html);
        if ($browserPdf !== null) {
            return $browserPdf;
        }

        if (! class_exists(Mpdf::class)) {
            throw new \RuntimeException('Renderer PDF lampiran tidak tersedia di server ini.');
        }

        return $this->renderPdfOutputWithMpdf(
            $surat->jenisSurat?->template,
            $bodyHtml,
            $orientation,
        );
    }

    protected function renderPdfOutputWithChrome(string $html): ?string
    {
        $chromeBinary = $this->resolveChromeBinary();

        if ($chromeBinary === null) {
            return null;
        }

        $tempDir = $this->resolveBrowserTempDir();
        $profileDir = $tempDir.DIRECTORY_SEPARATOR.'profile';
        File::ensureDirectoryExists($profileDir);

        $htmlPath = $tempDir.DIRECTORY_SEPARATOR.'document.html';
        $pdfPath = $tempDir.DIRECTORY_SEPARATOR.'document.pdf';
        File::put($htmlPath, $html);

        $process = new Process([
            $chromeBinary,
            '--headless=new',
            '--disable-gpu',
            '--disable-extensions',
            '--no-first-run',
            '--no-default-browser-check',
            '--hide-scrollbars',
            '--run-all-compositor-stages-before-draw',
            '--virtual-time-budget=4000',
            '--user-data-dir='.$profileDir,
            '--allow-file-access-from-files',
            '--print-to-pdf='.$pdfPath,
            '--print-to-pdf-no-header',
            '--no-pdf-header-footer',
            'file:///'.str_replace('\\', '/', $htmlPath),
        ]);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
            return null;
        }

        return File::get($pdfPath);
    }

    /**
     * @param  array<int, array{key: string, label: string, align: string, bold: bool}>  $columnLabels
     * @param  array<int, array<string, string>>  $rows
     */
    protected function renderAttachmentBody(
        string $nomorSurat,
        string $tanggalSurat,
        string $judulLampiran,
        string $orientation,
        string $judulLampiranAlign,
        bool $judulLampiranBold,
        array $columnLabels,
        array $rows,
    ): string {
        $normalizedTitleAlign = $this->normalizeAttachmentAlign($judulLampiranAlign);
        $normalizedTitleWeight = $judulLampiranBold ? '700' : '400';
        $titleLineStyle = sprintf(
            'display:block; width:100%%; text-align:%s; font-size:12pt; line-height:1.4; font-weight:%s;',
            e($normalizedTitleAlign),
            e($normalizedTitleWeight),
        );

        $judulLampiranHtml = collect(preg_split('/\r\n|\r|\n/', $judulLampiran) ?: [])
            ->map(fn (string $line): string => trim($line))
            ->filter(fn (string $line): bool => $line !== '')
            ->map(fn (string $line): string => '<div style="'.$titleLineStyle.'">'.e(mb_strtoupper($line)).'</div>')
            ->implode('');
        $titleClasses = trim('lampiran-content-title lampiran-content-title--'.$judulLampiranAlign.($judulLampiranBold ? ' lampiran-content-title--bold' : ''));
        $titleStyle = sprintf(
            'margin:32px 0 24px; width:100%%; text-align:%s; font-size:12pt; line-height:1.4; font-weight:%s;',
            e($normalizedTitleAlign),
            e($normalizedTitleWeight),
        );
        $headersHtml = collect($columnLabels)->map(function (array $column): string {
            $align = $this->normalizeAttachmentAlign($column['align'] ?? 'left');
            $headerWeight = ! empty($column['bold']) ? '700' : '400';

            return sprintf(
                '<th style="border:1px solid #0f172a; padding:6px 8px; text-align:%s; vertical-align:middle; font-weight:%s;">%s</th>',
                e($align),
                e((string) $headerWeight),
                e($column['label']),
            );
        })->implode('');

        $rowsHtml = collect($rows)->values()->map(function (array $row) use ($columnLabels): string {
            $cellsHtml = collect($columnLabels)->map(function (array $column) use ($row): string {
                $align = $this->normalizeAttachmentAlign($column['align'] ?? 'left');
                $value = trim((string) ($row[$column['key']] ?? ''));

                return sprintf(
                    '<td style="border:1px solid #0f172a; padding:6px 8px; vertical-align:top; text-align:%s;">%s</td>',
                    e($align),
                    e($value !== '' ? $value : '-'),
                );
            })->implode('');

            return '<tr>'.$cellsHtml.'</tr>';
        })->implode('');

        return sprintf(
            '<div class="surat-content lampiran-document lampiran-document--%s"><div class="lampiran-meta">
                <table>
                    <tr><td style="width:84px;">Lampiran</td><td style="width:16px;">:</td><td>-</td></tr>
                    <tr><td>Nomor</td><td>:</td><td>%s</td></tr>
                    <tr><td>Tanggal</td><td>:</td><td>%s</td></tr>
                </table>
            </div>
            %s
            <div class="lampiran-table-wrap">
            <table class="lampiran-table" border="1" cellspacing="0" cellpadding="0" style="border-collapse:collapse; border:1px solid #0f172a;">
                <thead>
                    <tr>%s</tr>
                </thead>
                <tbody>%s</tbody>
            </table></div></div>',
            e($this->normalizeAttachmentOrientation($orientation)),
            e($nomorSurat !== '' ? $nomorSurat : '-'),
            e($tanggalSurat !== '' ? $tanggalSurat : '-'),
            $judulLampiranHtml !== '' ? '<div class="'.$titleClasses.'" style="'.$titleStyle.'">'.$judulLampiranHtml.'</div>' : '',
            $headersHtml,
            $rowsHtml,
        );
    }

    protected function buildWrappedPreviewHtml(
        string $title,
        ?SuratTemplate $template,
        string $bodyHtml,
        string $orientation = 'portrait',
    ): string {
        $normalizedOrientation = $this->normalizeAttachmentOrientation($orientation);
        $pageSize = $normalizedOrientation === 'landscape' ? 'A4 landscape' : 'A4 portrait';
        $pageHeight = $normalizedOrientation === 'landscape' ? '210mm' : '297mm';
        $orientationCss = <<<CSS
<style>
@page { size: {$pageSize}; }
.preview-sheet {
    height: {$pageHeight};
}
</style>
CSS;

        return $this->renderer->wrapDocumentHtml(
            $title,
            $orientationCss.'<div class="preview-sheet__body">'.$bodyHtml.'</div>',
            $template,
        );
    }

    protected function renderPdfOutputWithMpdf(
        ?SuratTemplate $template,
        string $bodyHtml,
        string $orientation = 'portrait',
    ): string {
        $tempDir = $this->resolveTempDir();
        $settings = TemplateGlobalSetting::allAsArray();
        $marginTop = $this->normalizeTopMargin((string) ($settings['margin_top'] ?? '15mm'));
        $normalizedOrientation = $this->normalizeAttachmentOrientation($orientation);

        $fontFamilyKop = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_kop'),
            'pdf'
        );
        $fontFamilyBody = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_body'),
            'pdf'
        );
        $fontFamilyFooter = SuratKomponenRenderer::fontFamilyStack(
            SuratKomponenRenderer::resolveFontFamily($settings, 'font_family_footer'),
            'pdf'
        );
        $mpdfFontConfig = SuratKomponenRenderer::mpdfFontConfig();
        $fontDir = $mpdfFontConfig['fontDir'] ?? [];
        $fontdata = $mpdfFontConfig['fontdata'] ?? [];
        $fontCss = <<<CSS
:root {
    --font-family-kop: {$fontFamilyKop};
    --font-family-body: {$fontFamilyBody};
    --font-family-footer: {$fontFamilyFooter};
}

body,
.preview-sheet__body,
.surat-content {
    font-family: {$fontFamilyBody};
    font-weight: 500;
}

.preview-sheet__header,
.preview-sheet__header * {
    font-family: {$fontFamilyKop};
}

.preview-sheet__footer,
.preview-sheet__footer * {
    font-family: {$fontFamilyFooter};
}

.preview-sheet__body p,
.preview-sheet__body td,
.preview-sheet__body th,
.preview-sheet__body li,
.preview-sheet__body div {
    font-weight: 500;
}
CSS;

        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => $normalizedOrientation === 'landscape' ? 'A4-L' : 'A4-P',
            'margin_top' => $marginTop,
            'margin_bottom' => 16,
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_header' => 3,
            'margin_footer' => 4,
            'default_font' => $fontFamilyBody,
            'fontDir' => $fontDir,
            'fontdata' => $fontdata,
            'tempDir' => $tempDir,
            'cacheCleanupInterval' => app()->runningUnitTests() ? false : 3600,
        ]);

        $styles = $this->renderer->documentStyles().' '.$this->attachmentStyles();
        $customCss = (string) ($template?->css_style ?? '');

        $mpdf->WriteHTML("<style>{$fontCss} {$styles} {$customCss}</style>", HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($bodyHtml, HTMLParserMode::HTML_BODY);

        return $mpdf->Output('', 'S');
    }

    public function attachmentStyles(): string
    {
        return <<<'CSS'
.lampiran-document {
    width: 100%;
    font-size: 12pt;
}

.lampiran-document--landscape .lampiran-table th,
.lampiran-document--landscape .lampiran-table td {
    white-space: nowrap;
}

.lampiran-meta {
    margin-bottom: 42px;
}

.lampiran-meta table,
.lampiran-table {
    width: 100%;
    border-collapse: collapse;
}

.lampiran-meta td {
    padding: 0;
    vertical-align: top;
    line-height: 1.2;
}

.lampiran-content-title {
    margin: 32px 0 24px;
    font-size: 12pt;
    font-weight: 400;
    line-height: 1.4;
    width: 100%;
}

.lampiran-content-title--left {
    text-align: left;
}

.lampiran-content-title--center {
    text-align: center;
}

.lampiran-content-title--right {
    text-align: right;
}

.lampiran-content-title--bold {
    font-weight: 700;
}

.lampiran-table-wrap {
    margin-top: 10px;
}

.lampiran-table th,
.lampiran-table td {
    border: 1px solid #0f172a;
    padding: 6px 8px;
    vertical-align: top;
}

.lampiran-table th {
    text-align: center;
    font-weight: 700;
}
CSS;
    }

    protected function formatAttachmentDate(mixed $value): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->locale('id')->translatedFormat('d F Y');
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->locale('id')->translatedFormat('d F Y');
        }

        if (blank($value)) {
            return '';
        }

        return Carbon::parse((string) $value)->locale('id')->translatedFormat('d F Y');
    }

    protected function extractAttachmentScalar(
        Surat $surat,
        string $fieldName,
        string $fallback = '',
        array $allowedValues = [],
    ): string {
        $surat->loadMissing('dataEntries');

        $storedValue = trim((string) ($surat->dataEntries->firstWhere('field_name', $fieldName)?->field_value ?? ''));

        if ($storedValue === '') {
            $isiSurat = json_decode((string) $surat->isi_surat, true);
            $storedValue = trim((string) Arr::get($isiSurat, 'data.'.$fieldName, $fallback));
        }

        if ($allowedValues !== [] && ! in_array($storedValue, $allowedValues, true)) {
            return $fallback;
        }

        return $storedValue !== '' ? $storedValue : $fallback;
    }

    protected function normalizeAttachmentAlign(mixed $value): string
    {
        $align = strtolower(trim((string) $value));

        return in_array($align, ['left', 'center', 'right'], true) ? $align : 'center';
    }

    protected function normalizeAttachmentOrientation(mixed $value): string
    {
        $orientation = strtolower(trim((string) $value));

        return in_array($orientation, ['portrait', 'landscape'], true) ? $orientation : 'portrait';
    }

    protected function normalizeAttachmentTitleBold(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        return filter_var((string) $value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{no: string, nama: string, nim: string, prodi: string}
     */
    protected function normalizeAttachmentColumnLabels(array $payload): array
    {
        $columns = $this->normalizeAttachmentColumns(Arr::get($payload, 'lampiran_columns'));

        if ($columns !== []) {
            return $columns;
        }

        return $this->defaultAttachmentColumns();
    }

    /**
     * @param  array<int, array{nama: string, nim: string, prodi: string}>  $rows
     * @param  array<int, array{key: string, label: string, align: string, bold: bool}>  $columns
     * @return array<int, array<string, string>>
     */
    protected function mapLegacyStudentRowsToAttachmentRows(array $rows, array $columns): array
    {
        $defaults = $this->defaultAttachmentColumns();
        $keys = collect($columns)->pluck('key')->values();

        return collect($rows)->values()->map(function (array $row, int $index) use ($columns, $defaults, $keys): array {
            $mapped = [];

            foreach ($columns as $columnIndex => $column) {
                $defaultLabel = strtolower(trim((string) ($defaults[$columnIndex]['label'] ?? '')));
                $mapped[$column['key']] = match ($defaultLabel) {
                    'no' => (string) ($index + 1),
                    'nama mahasiswa' => $row['nama'],
                    'nim' => $row['nim'],
                    'program studi' => $row['prodi'],
                    default => '',
                };
            }

            foreach ($keys as $key) {
                $mapped[$key] = (string) ($mapped[$key] ?? '');
            }

            return $mapped;
        })->all();
    }

    protected function normalizeTopMargin(string $marginTop): string
    {
        $value = trim($marginTop);

        if ($value === '') {
            return '12mm';
        }

        if (preg_match('/^(\d+(?:\.\d+)?)\s*mm$/i', $value, $matches) === 1) {
            $adjusted = max(2, (float) $matches[1] - 10);

            return rtrim(rtrim(number_format($adjusted, 1, '.', ''), '0'), '.').'mm';
        }

        if (is_numeric($value)) {
            $adjusted = max(2, (float) $value - 10);

            return rtrim(rtrim(number_format($adjusted, 1, '.', ''), '0'), '.').'mm';
        }

        return $value;
    }

    protected function resolveTempDir(): string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'projek-fast-attachment-pdf'
            .DIRECTORY_SEPARATOR.Str::uuid()->toString();

        File::ensureDirectoryExists($dir);

        return $dir;
    }

    protected function resolveBrowserTempDir(): string
    {
        $dir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            .DIRECTORY_SEPARATOR.'projek-fast-attachment-browser-pdf'
            .DIRECTORY_SEPARATOR.Str::uuid()->toString();

        File::ensureDirectoryExists($dir);

        return $dir;
    }

    protected function resolveChromeBinary(): ?string
    {
        $candidates = array_filter([
            env('FAST_PDF_CHROME_PATH'),
            'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
            'C:\\Program Files\\Microsoft\\Edge\\Application\\msedge.exe',
            'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
