<?php

use App\Modules\Fast\Services\Shared\OutgoingLetterAttachmentService;
use App\Modules\Fast\Services\Shared\SuratDocumentGeneratorService;
use App\Modules\Fast\Template\Renderers\SuratKomponenRenderer;
use App\Modules\Fast\Template\Renderers\SuratTemplateRendererService;
use Illuminate\Support\Facades\Log;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;

it('renders a complete PDF with mPDF when Chrome is unavailable', function () {
    if (! class_exists(Mpdf::class)) {
        $this->markTestSkipped('mPDF is not installed.');
    }

    $service = new class(Mockery::mock(SuratTemplateRendererService::class), Mockery::mock(OutgoingLetterAttachmentService::class)) extends SuratDocumentGeneratorService
    {
        public function renderWithoutChrome(array $viewPayload): string
        {
            return $this->renderPdfOutput($viewPayload);
        }

        public function fallbackMpdfConfiguration(): array
        {
            return $this->mpdfConfiguration('5mm', 'freeserif', [], [], storage_path('fonts'));
        }

        public function headerFooterLifecycleDocument(): string
        {
            return $this->mpdfHeaderFooterDocument(
                '/* base font CSS */',
                '/* document CSS */',
                '@page { margin: 12mm 15mm 25mm 15mm; }',
                'HEADER CONTENT',
                'FOOTER CONTENT',
            );
        }

        public function headerFooterParserMode(): int
        {
            return $this->mpdfHeaderFooterParserMode();
        }

        protected function resolveChromeBinary(): ?string
        {
            return null;
        }
    };

    $configuration = $service->fallbackMpdfConfiguration();

    expect($configuration)
        ->toMatchArray([
            'margin_top' => 5.0,
            'setAutoTopMargin' => 'stretch',
            'setAutoBottomMargin' => 'stretch',
            'autoMarginPadding' => 2,
        ]);

    $lifecycleDocument = $service->headerFooterLifecycleDocument();

    expect($lifecycleDocument)
        ->toContain('@page { margin: 12mm 15mm 25mm 15mm; }')
        ->toContain('header: html_fast_header;')
        ->toContain('footer: html_fast_footer;')
        ->toContain('<htmlpageheader name="fast_header">')
        ->toContain('HEADER CONTENT')
        ->toContain('<htmlpagefooter name="fast_footer">')
        ->toContain('FOOTER CONTENT');
    expect($service->headerFooterParserMode())->toBe(HTMLParserMode::DEFAULT_MODE);

    $bodyHtml = implode('', array_fill(
        0,
        90,
        '<p style="margin:0 0 4mm 0; font-size:11pt;">ISI SURAT REGRESSION MPDF</p>',
    ));
    $settings = [
        'nama_instansi' => 'UNIVERSITAS REGRESSION MPDF',
        'nama_fakultas' => 'FAKULTAS PENGUJIAN',
        'keputusan' => 'Keputusan Regression',
        'nama_instansi_footer' => 'UNIVERSITAS REGRESSION MPDF',
        'alamat_footer' => 'Alamat Regression',
        'website' => 'https://example.test',
        'email' => 'fast@example.test',
        'telepon' => '0800000000',
    ];

    Log::spy();

    $pdf = $service->renderWithoutChrome([
        'html' => '',
        'headerHtml' => SuratKomponenRenderer::renderKop($settings, ['__render_mode' => 'pdf']),
        'bodyHtml' => $bodyHtml,
        'footerHtml' => SuratKomponenRenderer::renderFooter($settings, ['__render_mode' => 'pdf']),
        'styles' => '@page { margin: 12mm 15mm 25mm 15mm; }',
        'customCss' => '',
    ]);

    expect($pdf)
        ->toStartWith('%PDF-')
        ->and(strlen($pdf))->toBeGreaterThan(1_000)
        ->and(preg_match_all('/\/Type\s*\/Page\b/', $pdf))->toBeGreaterThan(1);

    Log::shouldHaveReceived('info')
        ->with('FASt PDF Chrome renderer unavailable; using fallback renderer.', [
            'reason' => 'chrome_binary_not_found',
        ])
        ->once();
    Log::shouldHaveReceived('info')
        ->with('FASt PDF renderer selected: mPDF fallback.', [
            'attachment_section' => false,
        ])
        ->once();
});
