<?php

namespace App\Modules\Fast\Services\Shared;

use App\Models\NomorSuratSequence;
use App\Models\Surat;
use App\Models\TemplateGlobalSetting;
use App\Modules\Fast\Template\Renderers\SuratKomponenRenderer;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Mpdf;
// use Barryvdh\DomPDF\Facade\Pdf;
// use Throwable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SuratDocumentGeneratorService
{
    /**
     * @var array<string, bool>
     */
    protected static array $suratColumnAvailability = [];

    public function __construct(
        protected SuratTemplateRendererService $renderer,
    ) {
    }

    public function generate(Surat $surat): Surat
    {
        $surat = $this->prepareDraft($surat);

        if (blank($surat->qr_token)) {
            $surat->forceFill([
                'qr_token' => (string) Str::uuid(),
            ])->save();
        }

        $outputPath = $this->makeOutputPath($surat);
        $freshSurat = $surat->fresh(['jenisSurat']);
        $finalizedAt = now();
        $renderSurat = $freshSurat->forceFill([
            'status' => Surat::STATUS_FINISHED,
            'tanggal_selesai' => $finalizedAt,
            'generated_at' => $finalizedAt,
        ]);
        $rendered     = $this->renderer->renderForSurat($renderSurat, true, 'pdf');
        $qrCodeBase64 = $this->makeQrCodeBase64($renderSurat);

        $renderedHtml   = is_array($rendered) ? ($rendered['full']   ?? $rendered['html'] ?? '') : (string) $rendered;
        $renderedBody   = is_array($rendered) ? ($rendered['body']   ?? $renderedHtml) : $renderedHtml;
        $renderedHeader = is_array($rendered) ? ($rendered['header'] ?? '') : '';
        $renderedFooter = is_array($rendered) ? ($rendered['footer'] ?? '') : '';

        $viewPayload = [
            'title'        => 'Surat '.$freshSurat->jenisSurat?->nama,
            'html'         => $renderedHtml,
            'bodyHtml'     => $renderedBody,
            'headerHtml'   => $renderedHeader,
            'footerHtml'   => $renderedFooter,
            'styles'       => $this->renderer->documentStyles(),
            'qrCodeBase64' => $qrCodeBase64,
            'qrToken'      => $freshSurat->qr_token,
            'template'     => $freshSurat->jenisSurat?->template,
            'customCss'    => $freshSurat->jenisSurat?->template?->css_style ?? '',
        ];
        // $rendered = $this->renderer->renderForSurat($surat->fresh());
        // $outputPath = $this->makeOutputPath($surat);
        // $freshSurat = $surat->fresh(['jenisSurat']);
        // $qrCodeBase64 = $this->makeQrCodeBase64($freshSurat);
        // $viewPayload = [
        //     'title'        => 'Surat '.$freshSurat->jenisSurat?->nama,
        //     'html'         => $rendered['full'],
        //     'bodyHtml'     => $rendered['body'],
        //     'headerHtml'   => $rendered['header'],
        //     'footerHtml'   => $rendered['footer'],
        //     'styles'       => $this->renderer->documentStyles(),
        //     'qrCodeBase64' => $qrCodeBase64,
        //     'qrToken'      => $freshSurat->qr_token,
        //     'template'     => $freshSurat->jenisSurat?->template,
        //     'customCss'    => $freshSurat->jenisSurat?->template?->css_style ?? '',
        // ];
        Storage::disk('public')->makeDirectory(dirname($outputPath));

        Storage::disk('public')->put(
            $outputPath,
            $this->renderPdfOutput($viewPayload),
        );

        $updates = [
            'rendered_snapshot' => $rendered['html'],
            'generated_file_path' => $outputPath,
            'generated_at' => $finalizedAt,
            'generated_file_type' => 'pdf',
            'template_version' => $rendered['template']->version,
            'status' => Surat::STATUS_FINISHED,
            'tanggal_selesai' => $finalizedAt,
            'nomor_surat_status' => Surat::NOMOR_SURAT_STATUS_ISSUED,
        ];

        if ($this->suratTableHasColumn('generated_by')) {
            $updates['generated_by'] = auth()->id();
        }

        $freshSurat->forceFill($updates)->save();

        return $freshSurat->fresh();
    }

    public function prepareDraft(Surat $surat): Surat
    {
        if (blank($surat->nomor_surat)) {
            $surat->forceFill([
                'nomor_surat' => $this->generateNomorSurat($surat),
                'nomor_surat_status' => Surat::NOMOR_SURAT_STATUS_RESERVED,
            ])->save();
        }

        $rendered = $this->renderer->renderForSurat($surat->fresh());

        $surat->forceFill([
            'rendered_snapshot' => $rendered['html'],
            'template_version' => $rendered['template']->version,
        ])->save();

        return $surat->fresh([
            'jenisSurat.template.placeholders',
            'pemohon',
            'dataEntries',
        ]);
    }

    protected function generateNomorSurat(Surat $surat): string
    {
        $surat->loadMissing('jenisSurat');

        $year = now()->year;
        $prefix = strtoupper((string) TemplateGlobalSetting::get('kode_prefix_nomor_surat', 'B'));
        $facultyCode = (string) TemplateGlobalSetting::get('kode_fakultas_nomor_surat', 'Ybk.041.10');
        $classificationCode = $this->resolveClassificationCode($surat);

        $sequenceKey = sprintf('FAKULTAS-%s', $year);
        $urutan = NomorSuratSequence::nextUrutan($sequenceKey, $year, 0);

        return sprintf(
            '%s/%03d/%s/%s/%d',
            $prefix,
            $urutan,
            $facultyCode,
            $classificationCode,
            $year
        );
    }

    protected function resolveClassificationCode(Surat $surat): string
    {
        $classificationCode = trim((string) ($surat->jenisSurat?->kode_klasifikasi ?? ''));

        if ($classificationCode === '') {
            return 'SURAT';
        }

        return strtoupper($classificationCode);
    }

    protected function makeOutputPath(Surat $surat): string
    {
        $surat->loadMissing('jenisSurat');

        $slug = Str::slug((string) ($surat->jenisSurat?->slug ?: $surat->jenisSurat?->nama ?: 'surat'));

        return sprintf('fast/generated/surat-%d-%s.pdf', $surat->id, $slug);
    }

    protected function makeQrCodeBase64(Surat $surat): string
    {
        // Ganti payload QR menjadi URL endpoint verifikasi
        $baseUrl = config('app.url') ?? 'http://localhost';
        $url = rtrim($baseUrl, '/') . '/verifikasi-qr/' . $surat->qr_token;

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(180),
                new SvgImageBackEnd(),
            ),
        );

        return 'data:image/svg+xml;base64,' . base64_encode($writer->writeString($url));
    }

    protected function renderPdfOutput(array $viewPayload): string
    {
        $browserPdf = $this->renderPdfOutputWithChrome($viewPayload);
        if ($browserPdf !== null) {
            return $browserPdf;
        }

        $styles     = (string) ($viewPayload['styles'] ?? '');
        $customCss  = (string) ($viewPayload['customCss'] ?? '');
        $bodyHtml   = (string) ($viewPayload['bodyHtml'] ?? '');
        $headerHtml = (string) ($viewPayload['headerHtml'] ?? '');
        $footerHtml = (string) ($viewPayload['footerHtml'] ?? '');
        $tempDir    = $this->resolveMpdfTempDir();
        $settings   = TemplateGlobalSetting::allAsArray();

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
        $configDefaults = (new ConfigVariables())->getDefaults();
        $fontDefaults   = (new FontVariables())->getDefaults();
        $fontDir        = array_values(array_unique(array_merge(
            $configDefaults['fontDir'] ?? [],
            $mpdfFontConfig['fontDir'] ?? [],
        )));
        $fontdata = array_merge(
            $fontDefaults['fontdata'] ?? [],
            $mpdfFontConfig['fontdata'] ?? [],
        );
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

        // Margin (mm). margin_bottom harus cukup menampung tinggi footer
        // berjalan (kop instansi + alamat + email) agar isi surat di SEMUA
        // halaman tidak menabrak footer.
        $mpdf = new Mpdf([
            'mode'          => 'utf-8',
            'format'        => 'A4',
            'margin_top'    => 38,
            'margin_bottom' => 16,
            'margin_left'   => 15,
            'margin_right'  => 15,
            'margin_header' => 5,
            'margin_footer' => 6,
            'default_font'   => $fontFamilyBody,
            'fontDir'        => $fontDir,
            'fontdata'       => $fontdata,
            'tempDir'       => $tempDir,
            'cacheCleanupInterval' => app()->runningUnitTests() ? false : 3600,
        ]);

        $mpdf->SetHTMLHeader('<div style="width:100%;">' . $headerHtml . '</div>');
        $mpdf->SetHTMLFooter('<div style="width:100%;">' . $footerHtml . '</div>');

        // Tulis style sekali, lalu body sekali (jangan duplikat).
        $mpdf->WriteHTML("<style>{$fontCss} {$styles} {$customCss}</style>", \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($bodyHtml, \Mpdf\HTMLParserMode::HTML_BODY);

        return $mpdf->Output('', 'S');
    }

    protected function renderPdfOutputWithChrome(array $viewPayload): ?string
    {
        $chromeBinary = $this->resolveChromeBinary();

        if ($chromeBinary === null) {
            return null;
        }

        $tempDir = $this->resolveBrowserTempDir();
        $profileDir = $tempDir . DIRECTORY_SEPARATOR . 'profile';
        File::ensureDirectoryExists($profileDir);

        $htmlPath = $tempDir . DIRECTORY_SEPARATOR . 'document.html';
        $pdfPath = $tempDir . DIRECTORY_SEPARATOR . 'document.pdf';
        $html = $this->buildBrowserPdfHtml($viewPayload);

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
            '--user-data-dir=' . $profileDir,
            '--allow-file-access-from-files',
            '--print-to-pdf=' . $pdfPath,
            '--print-to-pdf-no-header',
            'file:///' . str_replace('\\', '/', $htmlPath),
        ]);

        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($pdfPath)) {
            return null;
        }

        return File::get($pdfPath);
    }

    protected function buildBrowserPdfHtml(array $viewPayload): string
    {
        return $this->renderer->wrapDocumentHtml(
            (string) ($viewPayload['title'] ?? 'Surat'),
            (string) ($viewPayload['html'] ?? ''),
            $viewPayload['template'] ?? null,
        );
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

    protected function resolveBrowserTempDir(): string
    {
        $baseDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'projek-fast-browser-pdf';
        $suffix = app()->runningUnitTests()
            ? 'tests'
            : 'run';

        $dir = $baseDir
            . DIRECTORY_SEPARATOR . $suffix
            . DIRECTORY_SEPARATOR . Str::uuid()->toString();

        File::ensureDirectoryExists($dir);

        return $dir;
    }

    protected function resolveMpdfTempDir(): string
    {
        $baseDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR)
            . DIRECTORY_SEPARATOR . 'projek-fast-mpdf';
        $suffix = app()->runningUnitTests()
            ? 'tests'
            : 'run';

        return $baseDir
            . DIRECTORY_SEPARATOR . $suffix
            . DIRECTORY_SEPARATOR . Str::uuid()->toString();
    }

    protected function buildStyles(array $viewPayload): string
    {
        $styles    = (string) ($viewPayload['styles'] ?? '');
        $customCss = (string) ($viewPayload['customCss'] ?? '');
        return "<style>{$styles} {$customCss}</style>";
    }

    protected function suratTableHasColumn(string $column): bool
    {
        if (! array_key_exists($column, self::$suratColumnAvailability)) {
            self::$suratColumnAvailability[$column] = Schema::hasColumn('surats', $column);
        }

        return self::$suratColumnAvailability[$column];
    }

    // /**
    //  * @param  array<string, mixed>  $viewPayload
    //  */
    // protected function renderPdfOutput(array $viewPayload): string
    // {
    //     try {
    //         return Pdf::loadView('documents.surat', $viewPayload)
    //             ->setPaper('a4', 'portrait')
    //             ->output();
    //     } catch (Throwable) {
    //         return Pdf::loadHTML($this->buildFallbackHtml($viewPayload))
    //             ->setPaper('a4', 'portrait')
    //             ->output();
    //     }
    // }

    // protected function renderPdfOutput(array $viewPayload): string
    // {
    //     return Pdf::loadHTML($this->buildFullHtml($viewPayload))
    //         ->setPaper('a4', 'portrait')
    //         ->output();
    // }

    // protected function buildFullHtml(array $viewPayload): string
    // {
    //     $title      = e((string) ($viewPayload['title'] ?? 'Surat'));
    //     $styles     = (string) ($viewPayload['styles'] ?? '');
    //     $customCss  = (string) ($viewPayload['customCss'] ?? '');
    //     $bodyHtml   = (string) ($viewPayload['bodyHtml'] ?? $viewPayload['html'] ?? '');
    //     $footerHtml = (string) ($viewPayload['footerHtml'] ?? '');
    //     $qrMarkup   = '';

    //     if (filled($viewPayload['qrCodeBase64'] ?? null)) {
    //         $qrCodeBase64 = e((string) $viewPayload['qrCodeBase64']);
    //         $qrMarkup = <<<HTML
    //         <div style="position: fixed; bottom: -22mm; right: 0; text-align: right; width: 18mm;">
    //             <p style="color: #475569; font-size: 6pt; letter-spacing: 0.04em; margin-bottom: 1px; text-transform: uppercase;">Validasi Dokumen</p>
    //             <img src="{$qrCodeBase64}" alt="QR Code Surat" style="height: 15mm; width: 15mm;">
    //         </div>
    //         HTML;
    //         }

    //     return <<<HTML
    //     <!DOCTYPE html>
    //     <html lang="id">
    //     <head>
    //         <meta charset="utf-8">
    //         <title>{$title}</title>
    //         <style>
    //             @page { margin: 48mm 15mm 28mm 15mm; size: A4 portrait; }
    //             body { font-family: "Cambria", "Times New Roman", serif; font-size: 12pt; margin: 0; padding: 0; }
    //             {$styles}
    //             {$customCss}
    //             #kop    { position: fixed; top: -48mm; left: 0; right: 0; }
    //             #footer { position: fixed; bottom: -28mm; left: 0; right: 0; }
    //         </style>
    //     </head>
    //     <body>
    //         <div id="kop">{$headerHtml}</div>
    //         <div id="footer">{$footerHtml}</div>
    //         <div class="page">{$bodyHtml}</div>
    //         {$qrMarkup}
    //     </body>
    //     </html>
    //     HTML;
    // }
//     /**
//      * @param  array<string, mixed>  $viewPayload
//      */
//     protected function buildFallbackHtml(array $viewPayload): string
//     {
//         $qrMarkup = '';

//         if (filled($viewPayload['qrCodeBase64'] ?? null)) {
//             $qrCodeBase64 = e((string) $viewPayload['qrCodeBase64']);

//             $qrMarkup = <<<HTML
// <div style="bottom: 0; position: fixed; right: 0; text-align: right; width: 18mm;">
//     <p style="color: #475569; font-size: 6pt; letter-spacing: 0.04em; margin-bottom: 1px; text-transform: uppercase;">Validasi Dokumen</p>
//     <img src="{$qrCodeBase64}" alt="QR Code Surat" style="height: 15mm; width: 15mm;">
// </div>
// HTML;
//         }

//         return $this->renderer->wrapDocumentHtml(
//             (string) ($viewPayload['title'] ?? 'Surat'),
//             (string) ($viewPayload['html'] ?? '').$qrMarkup,
//             $viewPayload['template'] ?? null,
//         );
//     }
}
