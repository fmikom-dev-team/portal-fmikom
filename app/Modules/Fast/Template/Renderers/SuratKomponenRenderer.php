<?php
// app/Services/SuratKomponenRenderer.php

namespace App\Modules\Fast\Template\Renderers;

use App\Models\TemplateGlobalSetting;
use App\Modules\Fast\Template\Parsers\TemplatePlaceholderReplacer;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class SuratKomponenRenderer
{
    const WARNA_DEFAULT = '#00b050';
    const FONT_FAMILY_DEFAULT = 'Times New Roman';

    public static function render(array $komponen, array $data = []): string
    {
        $html = '';
        foreach ($komponen as $komp) {
            $html .= static::renderKomponen($komp, $data);
        }
        return $html;
    }

    public static function renderBody(string $body, array $data = []): string
    {
        if (static::isKomponenJson($body)) {
            $komponen = json_decode($body, true) ?? [];
            return static::render($komponen, $data);
        }
        return static::fill($body, $data);
    }

    public static function isKomponenJson(string $body): bool
    {
        $trimmed = trim($body);
        if (!str_starts_with($trimmed, '[')) return false;
        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
            return is_array($decoded) && isset($decoded[0]['type']);
        } catch (\Throwable) {
            return false;
        }
    }

    public static function resolveFontFamily(array $settings, string $key, string $default = self::FONT_FAMILY_DEFAULT): string
    {
        $all = trim((string) ($settings['font_family_all'] ?? ''));
        $value = $all !== ''
            ? $all
            : trim((string) ($settings[$key] ?? ''));

        return $value !== '' ? $value : $default;
    }

    public static function fontFamilyStack(string $family, string $target = 'browser'): string
    {
        $normalized = strtolower(trim($family));

        if ($target === 'pdf') {
            return match ($normalized) {
                'times new roman', 'times', 'cambria', 'georgia' => 'timesnewroman',
                'arial', 'calibri', 'tahoma', 'verdana' => 'helvetica',
                'courier new', 'courier' => 'courier',
                default => 'timesnewroman',
            };
        }

        return match ($normalized) {
            'times new roman' => "'Times New Roman', Times, serif",
            'cambria' => 'Cambria, Georgia, serif',
            'georgia' => "Georgia, 'Times New Roman', serif",
            'arial' => 'Arial, Helvetica, sans-serif',
            'calibri' => 'Calibri, Arial, sans-serif',
            'tahoma' => 'Tahoma, Arial, sans-serif',
            'verdana' => 'Verdana, Geneva, sans-serif',
            'courier new' => '"Courier New", Courier, monospace',
            default => '"' . str_replace('"', '', $family) . '", serif',
        };
    }

    /**
     * @return array{fontDir: array<int, string>, fontdata: array<string, array<string, mixed>>}
     */
    public static function mpdfFontConfig(): array
    {
        $configDefaults = (new ConfigVariables())->getDefaults();
        $fontDefaults   = (new FontVariables())->getDefaults();

        $fontDirs = array_values(array_unique(array_filter(array_merge(
            $configDefaults['fontDir'] ?? [],
            [storage_path('fonts')],
            is_dir('C:\\Windows\\Fonts') ? ['C:\\Windows\\Fonts'] : [],
        ))));

        $fontdata = $fontDefaults['fontdata'] ?? [];
        $fontdata['timesnewroman'] = [
            'R'  => 'times.ttf',
            'B'  => 'timesbd.ttf',
            'I'  => 'timesi.ttf',
            'BI' => 'timesbi.ttf',
        ];

        return [
            'fontDir' => $fontDirs,
            'fontdata' => $fontdata,
        ];
    }
    // Hanya tampilkan: nama_instansi (dari field nama_instansi)
    // + nama_fakultas + singkatan jika ada
    // Struktur: Logo di atas, lalu Nama Instansi | Nama Fakultas | (Singkatan) | Keputusan | Garis
    public static function renderKop(array $settings = [], array $data = []): string
    {
        $kopHtml = $settings['kop_html'] ?? '';
        if (!empty(trim($kopHtml))) {
            return static::fill($kopHtml, $data);
        }

        $warna        = $settings['warna_primer'] ?? self::WARNA_DEFAULT;
        $namaInstansi = strtoupper(trim($settings['nama_instansi'] ?? 'UNUGHA CILACAP'));
        $namaFakultas = strtoupper(trim($settings['nama_fakultas'] ?? 'FAKULTAS MATEMATIKA DAN ILMU KOMPUTER'));
        $singkatan    = strtoupper(trim($settings['singkatan'] ?? ''));
        $keputusan    = trim($settings['keputusan'] ?? 'Keputusan Kemendikbud RI Nomor : 264/E/O/2014 Tanggal 23 Juli 2014');
        $logoPath     = $settings['logo_path'] ?? '';
        $logoPosition = strtolower(trim((string) ($settings['logo_kop_position'] ?? 'top')));
        $fontSizeH1   = trim((string) ($settings['font_size_kop_instansi'] ?? '17pt')) ?: '17pt';
        $fontSizeFak  = trim((string) ($settings['font_size_kop_fakultas'] ?? '13pt')) ?: '13pt';
        $kopBorder    = trim((string) ($settings['kop_border_thickness'] ?? '2px')) ?: '2px';
        $renderMode   = (string) ($data['__render_mode'] ?? 'preview');
        $kopBottomMargin = $renderMode === 'pdf' ? '0px' : '1px';
        $kopGapMargin    = $renderMode === 'pdf' ? '0px' : '2px';
        $kopFont      = static::fontFamilyStack(
            static::resolveFontFamily($settings, 'font_family_kop'),
            $renderMode === 'pdf' ? 'pdf' : 'browser'
        );

        // Logo
        $logoHtml = '';
        if (!empty($logoPath)) {
            $fullPath = storage_path('app/public/' . $logoPath);
            if (file_exists($fullPath)) {
                $mime    = mime_content_type($fullPath) ?: 'image/png';
                $encoded = base64_encode(file_get_contents($fullPath));
                $logoHtml = "<img src=\"data:{$mime};base64,{$encoded}\" alt=\"Logo\" style=\"display:block; margin:0 auto 4px; height:70px; width:auto;\">";
            }
        }
        // Fallback logo sementara
        if (empty($logoHtml) && file_exists(public_path('images/kop-logo-temp.png'))) {
            $encoded  = base64_encode(file_get_contents(public_path('images/kop-logo-temp.png')));
            $logoHtml = "<img src=\"data:image/png;base64,{$encoded}\" alt=\"Logo\" style=\"display:block; margin:0 auto 4px; height:70px; width:auto;\">";
        }

        // Singkatan: hanya tampil jika ada
        $singkatanHtml = !empty($singkatan) ? " ({$singkatan})" : '';

        $keputusanHtml = !empty($keputusan)
            ? "<p style=\"color: {$warna}; font-size: 9pt; line-height: 1.2; margin: {$kopGapMargin} 0 0 0;\">{$keputusan}</p>"
            : '';

        if ($logoPosition === 'side') {
            return <<<HTML
<section style="margin-bottom: {$kopBottomMargin}; width: 100%; font-family: {$kopFont};">
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <div style="flex: 0 0 auto;">{$logoHtml}</div>
        <div style="flex: 1 1 auto; text-align: center;">
            <div style="color: {$warna}; font-family: {$kopFont}; font-weight: 700; line-height: 1.08;">
                <div style="font-size: {$fontSizeH1}; letter-spacing: 0.04em;">{$namaInstansi}</div>
                <div style="font-size: {$fontSizeFak}; text-transform: uppercase;">{$namaFakultas}{$singkatanHtml}</div>
            </div>
            {$keputusanHtml}
        </div>
    </div>
    <div style="margin: {$kopGapMargin} auto 0; width: 100%;">
        <div style="border-top: {$kopBorder} solid {$warna}; margin-bottom: 0;"></div>
        <div style="border-top: 0.8px solid {$warna};"></div>
    </div>
</section>
HTML;
        }

        return <<<HTML
<section style="margin-bottom: {$kopBottomMargin}; text-align: center; font-family: {$kopFont};">
    {$logoHtml}
    <div style="color: {$warna}; font-family: {$kopFont}; font-weight: 700; line-height: 1.08;">
        <div style="font-size: {$fontSizeH1}; letter-spacing: 0.04em;">{$namaInstansi}</div>
        <div style="font-size: {$fontSizeFak}; text-transform: uppercase;">{$namaFakultas}{$singkatanHtml}</div>
    </div>
    {$keputusanHtml}
    <div style="margin: {$kopGapMargin} auto 0; width: 100%;">
        <div style="border-top: {$kopBorder} solid {$warna}; margin-bottom: 0;"></div>
        <div style="border-top: 0.8px solid {$warna};"></div>
    </div>
</section>
HTML;
    }
    // Persis seperti gambar: Nama Instansi bold | Baris alamat | Baris email+telp+fax
    public static function renderFooter(array $settings = []): string
    {
        $footerHtml = $settings['footer_html'] ?? '';
        if (!empty(trim($footerHtml))) {
            return $footerHtml;
        }

        $warna        = $settings['warna_primer'] ?? self::WARNA_DEFAULT;
        $namaInstansi = strtoupper(trim(
            (string) ($settings['nama_instansi_footer'] ?? $settings['nama_instansi'] ?? '')
        ));
        $alamat  = trim((string) ($settings['alamat_footer'] ?? $settings['alamat'] ?? ''));
        $website = trim((string) ($settings['website'] ?? ''));
        $email   = trim((string) ($settings['email'] ?? ''));
        $telepon = trim((string) ($settings['telepon'] ?? ''));
        $fax     = trim((string) ($settings['fax'] ?? ''));
        $fontSizeInstansi = trim((string) ($settings['font_size_footer_instansi'] ?? '8.8pt')) ?: '8.8pt';
        $fontSizeDetail   = trim((string) ($settings['font_size_footer_detail'] ?? '7.0pt')) ?: '7.0pt';
        $footerBorder     = trim((string) ($settings['footer_border_thickness'] ?? '2px')) ?: '2px';
        $renderMode       = (string) ($data['__render_mode'] ?? 'preview');
        $footerFont       = static::fontFamilyStack(
            static::resolveFontFamily($settings, 'font_family_footer'),
            $renderMode === 'pdf' ? 'pdf' : 'browser'
        );

        if ($namaInstansi === '' && $alamat === '' && $website === '' && $email === '' && $telepon === '' && $fax === '') {
            return '';
        }

        $baris1 = trim($alamat . ($website !== '' ? ', ' . $website : ''));
        $baris2Parts = array_filter([
            $email !== '' ? "Email : {$email}" : '',
            $telepon !== '' ? "Telp. : {$telepon}" : '',
            $fax !== '' ? "Fax : {$fax}" : '',
        ]);
        $baris2 = implode(' ', $baris2Parts);

        $content = '<div style="width: 100%; font-family: ' . $footerFont . ';">';
        $content .= '<div style="border-top: ' . $footerBorder . ' solid ' . $warna . '; margin-bottom: 1px;"></div>';
        $content .= '<div style="border-top: 0.8px solid ' . $warna . '; margin-bottom: 1px;"></div>';

        if ($namaInstansi !== '') {
            $content .= '<p style="color: ' . $warna . '; font-size: ' . $fontSizeInstansi . '; font-weight: 700; text-align: center; margin: 0; line-height: 1.12; letter-spacing: 0.03em;">'
                . $namaInstansi
                . '</p>';
        }

        if ($baris1 !== '') {
            $content .= '<p style="font-size: ' . $fontSizeDetail . '; text-align: center; margin: 0; line-height: 1.08; color: ' . $warna . ';">'
                . $baris1
                . '</p>';
        }

        if ($baris2 !== '') {
            $content .= '<p style="font-size: ' . $fontSizeDetail . '; text-align: center; margin: 0; line-height: 1.08; color: ' . $warna . ';">'
                . $baris2
                . '</p>';
        }

        $content .= '</div>';

        return $content;
    }

    protected static function renderKomponen(array $komp, array $data): string
    {
        $html = match ($komp['type'] ?? '') {
            'judul'           => static::renderJudul($komp, $data),
            'subjudul'        => static::renderSubjudul($komp, $data),
            'paragraf'        => static::renderParagraf($komp, $data),
            'paragraf_indent' => static::renderParagrafIndent($komp, $data),
            'daftar_bernomor',
            'paragraf_bernomor' => static::renderParagrafBernomor($komp, $data),
            'header_surat'    => static::renderHeaderSurat($komp, $data),
            'kepada_yth'      => static::renderKepadaYth($komp, $data),
            'tabel_data'      => static::renderTabelData($komp, $data),
            'tabel_biasa'     => static::renderTabelBiasa($komp, $data),
            'tabel_indent'    => static::renderTabelIndent($komp, $data),
            'tanda_tangan'    => static::renderTandaTangan($komp, $data),
            'tembusan'        => static::renderTembusan($komp, $data),
            'spasi'           => static::renderSpasi($komp),
            'garis'           => static::renderGaris(),
            default           => '',
        };

        // Wrap dengan margin_left jika ada
        $marginLeft = (int) ($komp['margin_left'] ?? 0);
        if ($marginLeft > 0 && $html !== '') {
            $html = "<div style='margin-left: {$marginLeft}px;'>{$html}</div>\n";
        }

        return $html;
    }
    protected static function renderJudul(array $komp, array $data): string
{
    $teks      = static::fill($komp['teks'] ?? '', $data);
    $align     = $komp['align'] ?? 'center';
        $size      = $komp['font_size'] ?? '12pt';
    $bold      = ($komp['bold'] ?? true) ? 'font-weight: bold;' : 'font-weight: normal;';
    $underline = ($komp['underline'] ?? false) ? 'text-decoration: underline; text-decoration-thickness: 2px;' : '';
    
    return "<h2 style=\"margin: 0 0 2px 0; text-align: {$align}; font-size: {$size}; {$bold} {$underline} text-transform: uppercase;\">{$teks}</h2>\n";
}
    protected static function renderSubjudul(array $komp, array $data): string
    {
        $teks = static::fill($komp['teks'] ?? '', $data);
        return "<p style=\"margin: 0 0 10px 0; text-align: center; font-size: 12pt;\">{$teks}</p>\n";
    }
    // Mendukung text_indent untuk baris pertama menjorok
    protected static function renderParagraf(array $komp, array $data): string
    {
        $teks        = static::fill($komp['teks'] ?? '', $data);
        $align       = $komp['align'] ?? 'justify';
        $italic      = ($komp['italic'] ?? false) ? 'font-style: italic;' : '';
        $bold        = ($komp['bold'] ?? false) ? 'font-weight: bold;' : '';
        $size        = $komp['font_size'] ?? '12pt';
        $lineHeight  = trim((string) ($data['line_height_paragraf'] ?? '1.45')) ?: '1.45';
        $teks        = nl2br($teks);
        $indentStyle = static::paragraphIndentStyle((string) ($komp['teks'] ?? ''), $komp['text_indent'] ?? null);

        return "<p style=\"margin: 0 0 6px 0; text-align: {$align}; font-size: {$size}; line-height: {$lineHeight}; white-space: pre-wrap; overflow-wrap: anywhere; {$italic} {$bold} {$indentStyle}\">{$teks}</p>\n";
    }
    protected static function renderParagrafIndent(array $komp, array $data): string
    {
        $teks        = static::fill($komp['teks'] ?? '', $data);
        $align       = $komp['align'] ?? 'justify';
        $indent      = $komp['indent'] ?? 40;
        $italic      = ($komp['italic'] ?? false) ? 'font-style: italic;' : '';
        $bold        = ($komp['bold'] ?? false) ? 'font-weight: bold;' : '';
        $size        = $komp['font_size'] ?? '12pt';
        $lineHeight  = trim((string) ($data['line_height_paragraf'] ?? '1.45')) ?: '1.45';
        $teks        = nl2br($teks);
        $indentStyle = static::paragraphIndentStyle((string) ($komp['teks'] ?? ''), $komp['text_indent'] ?? null);

        return "<p style=\"margin: 0 0 6px {$indent}px; text-align: {$align}; font-size: {$size}; line-height: {$lineHeight}; white-space: pre-wrap; overflow-wrap: anywhere; {$italic} {$bold} {$indentStyle}\">{$teks}</p>\n";
    }

    protected static function renderParagrafBernomor(array $komp, array $data): string
    {
        $items = static::daftarBernomorItems($komp, $data);
        if ($items === []) {
            return '';
        }

        $align      = $komp['align'] ?? 'justify';
        $italic     = ($komp['italic'] ?? false) ? 'font-style: italic;' : '';
        $bold       = ($komp['bold'] ?? false) ? 'font-weight: bold;' : '';
        $size       = $komp['font_size'] ?? '12pt';
        $lineHeight = trim((string) ($data['line_height_paragraf'] ?? '1.45')) ?: '1.45';
        $marginLeft = (int) ($komp['margin_left'] ?? 0);
        $numWidth   = (int) ($komp['text_indent'] ?? 24);
        if ($numWidth < 18) {
            $numWidth = 18;
        }
        $contentPadding = 6;

        $rows = '';
        foreach ($items as $index => $item) {
            $number = $index + 1;
            $content = static::fill($item, $data);
            $content = nl2br($content);

            $rows .= <<<HTML
<tr>
    <td style="width: {$numWidth}px; padding: 0 {$contentPadding}px 2px 0; vertical-align: top; text-align: right; white-space: nowrap;">{$number}.</td>
    <td style="padding: 0 0 2px 0; vertical-align: top; text-align: {$align}; white-space: pre-wrap; overflow-wrap: anywhere; line-height: {$lineHeight};">{$content}</td>
</tr>
HTML;
        }

        return <<<HTML
<table style="margin: 0 0 6px {$marginLeft}px; border-collapse: collapse; font-size: {$size}; line-height: {$lineHeight}; {$italic} {$bold}">
    <tbody>
        {$rows}
    </tbody>
</table>
HTML;
    }

    protected static function daftarBernomorItems(array $komp, array $data): array
    {
        $items = $komp['items'] ?? null;
        if (is_array($items)) {
            $filtered = array_values(array_filter(array_map(static function ($item): string {
                return trim((string) $item);
            }, $items), static fn (string $item): bool => $item !== ''));

            if ($filtered !== []) {
                return $filtered;
            }
        }

        $raw = trim((string) ($komp['teks'] ?? ''));
        if ($raw === '') {
            return [];
        }

        $lines = preg_split("/\r\n|\n|\r/", $raw) ?: [];
        $result = [];

        foreach ($lines as $line) {
            $item = preg_replace('/^\s*\d+[.)-]?\s+/', '', $line);
            $item = preg_replace('/^\s*[-*]\s+/', '', $item ?? '');
            $item = trim((string) $item);
            if ($item !== '') {
                $result[] = $item;
            }
        }

        if ($result !== []) {
            return $result;
        }

        return [$raw];
    }
    protected static function renderHeaderSurat(array $komp, array $data): string
    {
        $nomor    = static::fill($komp['nomor'] ?? '{{nomor_surat}}', $data);
        $lampiranTemplate = filled($data['lampiran_keterangan'] ?? null)
            ? '{{lampiran_keterangan}}'
            : ($komp['lampiran'] ?? '-');
        $perihalTemplate = filled($data['perihal'] ?? null)
            ? '{{perihal}}'
            : ($komp['perihal'] ?? '');
        $lampiran = static::fill($lampiranTemplate, $data);
        $perihal  = static::fill($perihalTemplate, $data);
        $kotaTemplate = (string) ($komp['kota'] ?? '{{kota_surat}}');
        $tanggalTemplate = (string) ($komp['tanggal'] ?? '{{tanggal_surat_panjang}}');
        $kota     = static::fill($kotaTemplate, $data);
        $tanggal  = static::fill($tanggalTemplate, $data);
        $kotaMewakiliTanggal = preg_match('/{{\s*tanggal_surat(?:_panjang)?\s*}}/i', $kotaTemplate) === 1;
        $tanggalKosong = trim(strip_tags((string) $tanggal)) === '';
        $size     = $komp['font_size'] ?? '12pt';
        $lineHeight = trim((string) ($data['line_height_header'] ?? '1.65')) ?: '1.65';
        $renderMode = (string) ($data['__render_mode'] ?? 'preview');
        $tableTopMargin = $renderMode === 'pdf' ? '-2px' : '0px';
        $tableBottomMargin = $renderMode === 'pdf' ? '0px' : '4px';
        $rightHeader = $kotaMewakiliTanggal || $tanggalKosong
            ? $kota
            : "{$kota}, {$tanggal}";

        return <<<HTML
<table style="width: 100%; border-collapse: collapse; margin-top: {$tableTopMargin}; margin-bottom: {$tableBottomMargin}; font-size: {$size}; line-height: {$lineHeight};">
    <tbody>
        <tr>
            <td style="width: 55%; vertical-align: top; padding: 0;">
                <table style="border-collapse: collapse; font-size: {$size}; line-height: {$lineHeight};">
                    <tr>
                        <td style="width: 80px; padding: 0; vertical-align: top;">Nomor</td>
                        <td style="width: 16px; padding: 0 4px; vertical-align: top; text-align: center;">:</td>
                        <td style="padding: 0;">{$nomor}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; vertical-align: top;">Lampiran</td>
                        <td style="padding: 0; text-align: center;">:</td>
                        <td style="padding: 0;">{$lampiran}</td>
                    </tr>
                    <tr>
                        <td style="padding: 0; vertical-align: top;">Perihal</td>
                        <td style="padding: 0; text-align: center;">:</td>
                        <td style="padding: 0;">{$perihal}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 45%; vertical-align: top; text-align: right; padding: 0; font-size: {$size}; line-height: {$lineHeight};">
                {$rightHeader}
            </td>
        </tr>
    </tbody>
</table>
HTML;
    }
    protected static function renderKepadaYth(array $komp, array $data): string
    {
        $penerima = is_array($data['__kepada_yth_items'] ?? null) && $data['__kepada_yth_items'] !== []
            ? $data['__kepada_yth_items']
            : ($komp['penerima'] ?? []);
        $lokasi   = static::fill($komp['lokasi'] ?? 'di-', $data);
        $tempat   = static::fill($komp['tempat'] ?? 'Tempat', $data);
        $size     = $komp['font_size'] ?? '12pt';
        $lokasiIndent = (int) ($komp['lokasi_indent'] ?? 0);
        $tempatIndent = (int) ($komp['tempat_indent'] ?? 16);
        $lineHeight = trim((string) ($data['line_height_yth'] ?? '1.05')) ?: '1.05';

        $penerimaHtml = "<p style=\"margin: 0; font-size: {$size}; line-height: {$lineHeight};\">Yth.</p>\n";
        foreach ($penerima as $i => $p) {
            $no = $i + 1;
            $prefix = count($penerima) === 1 ? '' : "{$no}. ";
            $penerimaHtml .= "<p style=\"margin: 0; font-size: {$size}; line-height: {$lineHeight}; font-weight: 700;\">" . $prefix . static::fill($p, $data) . "</p>\n";
        }

        return <<<HTML
<div style="margin-bottom: 6px; font-size: {$size}; line-height: {$lineHeight};">
    {$penerimaHtml}
    <p style="margin: 0; font-size: {$size}; line-height: {$lineHeight}; padding-left: {$lokasiIndent}px;">{$lokasi}</p>
    <p style="margin: 0; font-size: {$size}; line-height: {$lineHeight}; padding-left: {$tempatIndent}px;">{$tempat}</p>
</div>
HTML;
    }
    protected static function renderTabelData(array $komp, array $data): string
    {
        $rows = $komp['rows'] ?? [];
        if (empty($rows)) return '';
        $size = $komp['font_size'] ?? '12pt';
        $lineHeight = trim((string) ($data['line_height_tabel'] ?? '1.65')) ?: '1.65';

        $html = "<table style=\"width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: {$size}; line-height: {$lineHeight};\">\n<tbody>\n";
        foreach ($rows as $row) {
            $label = htmlspecialchars($row['label'] ?? '');
            $nilai = static::fill($row['nilai'] ?? '', $data);
            $html .= "<tr>
                <td style=\"width: 150px; white-space: nowrap; padding: 1.5px 0; vertical-align: top; line-height: {$lineHeight};\">{$label}</td>
                <td style=\"width: 12px; padding: 1.5px 0; vertical-align: top; text-align: center; line-height: {$lineHeight};\">:</td>
                <td style=\"padding: 1.5px 0; vertical-align: top; white-space: nowrap; line-height: {$lineHeight};\">{$nilai}</td>
            </tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }
    protected static function renderTabelBiasa(array $komp, array $data): string
    {
        $headers = array_values(array_filter($komp['headers'] ?? [], static fn ($header): bool => trim((string) $header) !== ''));
        $rows = $komp['rows'] ?? [];

        if (empty($headers) && empty($rows)) {
            return '';
        }

        $size = $komp['font_size'] ?? '12pt';
        $lineHeight = trim((string) ($data['line_height_tabel'] ?? '1.4')) ?: '1.4';
        $marginLeft = (int) ($komp['margin_left'] ?? 0);
        $borderColor = '#111827';
        $headerFill = '#F8FAFC';
        $columnCount = max(count($headers), 1);
        $headerWidth = round(100 / $columnCount, 2);

        $html = "<table style=\"width: 100%; margin-left: {$marginLeft}px; border-collapse: collapse; table-layout: fixed; margin-bottom: 8px; font-size: {$size}; line-height: {$lineHeight}; border: 1px solid {$borderColor};\">\n<thead>\n<tr>\n";

        foreach ($headers as $header) {
            $label = htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8');
            $html .= "<th style=\"width: {$headerWidth}%; border: 1px solid {$borderColor}; background: {$headerFill}; padding: 6px 8px; text-align: center; vertical-align: middle; font-weight: 700; line-height: {$lineHeight};\">{$label}</th>\n";
        }

        if (empty($headers)) {
            $html .= "<th style=\"width: 100%; border: 1px solid {$borderColor}; background: {$headerFill}; padding: 6px 8px; text-align: center; vertical-align: middle; font-weight: 700; line-height: {$lineHeight};\">Kolom</th>\n";
        }

        $html .= "</tr>\n</thead>\n<tbody>\n";

        foreach ($rows as $row) {
            $cells = array_values($row['cells'] ?? []);
            $html .= "<tr>\n";
            for ($index = 0; $index < $columnCount; $index++) {
                $value = static::fill((string) ($cells[$index] ?? ''), $data);
                $align = (string) ($komp['align'] ?? 'left');
                $html .= "<td style=\"border: 1px solid {$borderColor}; padding: 6px 8px; vertical-align: top; text-align: {$align}; line-height: {$lineHeight};\">{$value}</td>\n";
            }
            $html .= "</tr>\n";
        }

        if (empty($rows)) {
            $html .= "<tr><td colspan=\"{$columnCount}\" style=\"border: 1px solid {$borderColor}; padding: 10px 8px; vertical-align: top; line-height: {$lineHeight}; color: #94A3B8;\">Belum ada isi tabel</td></tr>\n";
        }

        $html .= "</tbody>\n</table>\n";
        return $html;
    }
    protected static function renderTabelIndent(array $komp, array $data): string
    {
        $rows   = $komp['rows'] ?? [];
        $indent = $komp['indent'] ?? 40;
        if (empty($rows)) return '';
        $size = $komp['font_size'] ?? '12pt';
        $lineHeight = trim((string) ($data['line_height_tabel'] ?? '1.65')) ?: '1.65';

        $html = "<table style=\"margin-left: {$indent}px; border-collapse: collapse; margin-bottom: 8px; font-size: {$size}; line-height: {$lineHeight};\">\n<tbody>\n";
        foreach ($rows as $row) {
            $label = htmlspecialchars($row['label'] ?? '');
            $nilai = static::fill($row['nilai'] ?? '', $data);
            $html .= "<tr>
                <td style=\"width: 150px; white-space: nowrap; padding: 1.5px 0; vertical-align: top; line-height: {$lineHeight};\">{$label}</td>
                <td style=\"width: 12px; padding: 1.5px 0; vertical-align: top; text-align: center; line-height: {$lineHeight};\">:</td>
                <td style=\"padding: 1.5px 0; vertical-align: top; white-space: nowrap; line-height: {$lineHeight};\">{$nilai}</td>
            </tr>\n";
        }
        $html .= "</tbody>\n</table>\n";
        return $html;
    }
    // Layout: kolom TTD (1/2/3 kolom), QR di dalam kolom terakhir
    // $data['__qr_svg']    = SVG string QR
    // $data['__qr_active'] = bool
    protected static function renderTandaTangan(array $komp, array $data): string
    {
        $kolom  = $komp['kolom'] ?? [];
        if (empty($kolom)) return '';
        $size   = $komp['font_size'] ?? '12pt';
        $jumlah = count($kolom);
        $renderMode = $data['__render_mode'] ?? 'preview';
        $signatureGap = $renderMode === 'pdf' ? '8mm' : '32mm';
        $tableMarginTop = $renderMode === 'pdf' ? '2px' : '16px';
        $qrBoxSize = $renderMode === 'pdf' ? '17mm' : '68px';
        $qrLabelSize = $renderMode === 'pdf' ? '0' : '7pt';
        $qrSvg    = $data['__qr_svg'] ?? '';
        $qrActive = $data['__qr_active'] ?? false;
        $qrHidden = $data['__qr_hidden'] ?? false;

        if ($renderMode === 'pdf' && $qrSvg !== '') {
            // Buang ukuran bawaan hanya pada tag <svg> pembuka, lalu pasang
            // ukuran fisik PDF yang tunggal.
            $qrSvg = preg_replace_callback(
                '/<svg\b([^>]*)>/i',
                function (array $matches) use ($qrBoxSize): string {
                    $attrs = preg_replace('/\s(width|height)="[^"]+"/i', '', $matches[1]) ?? $matches[1];
                    return '<svg' . $attrs . ' width="' . $qrBoxSize . '" height="' . $qrBoxSize . '" style="width:100%;height:100%;display:block;">';
                },
                $qrSvg,
                1
            ) ?? $qrSvg;
        }

if ($qrHidden) {
            $qrHtml = '';
} elseif ($qrActive && !empty($qrSvg)) {
            // QR dibuat sedikit lebih kecil di mode PDF agar blok tanda tangan
            // tetap muat dalam satu halaman.
            $qrHtml = <<<HTML
<div data-qr-embedded="true" style="display: inline-block; text-align: center; width: {$qrBoxSize};">
    <div style="width: {$qrBoxSize}; height: {$qrBoxSize};">
        {$qrSvg}
    </div>
    <p style="margin: 2px 0 0 0; font-size: {$qrLabelSize}; text-align: center; font-weight: bold; letter-spacing: 0.02em;">Dokumen Terverifikasi</p>
</div>
HTML;
        } else {
            $qrHtml = <<<HTML
<div data-qr-embedded="true" style="display: inline-block; text-align: center; width: {$qrBoxSize};">
    <div style="width: {$qrBoxSize}; height: {$qrBoxSize}; border: 1.5px dashed #CBD5E1; background: #F8FAFC; display: flex; align-items: center; justify-content: center;">
        <p style="font-size: {$qrLabelSize}; color: #94A3B8; text-align: center; margin: 0; line-height: 1.5; padding: 8px;">QR Code akan muncul setelah disetujui</p>
    </div>
    <p style="margin: 2px 0 0 0; font-size: {$qrLabelSize}; text-align: center; color: #94A3B8;">Menunggu Persetujuan</p>
</div>
HTML;
        }
        $alignMap = match ($jumlah) {
            1 => ['right'],
            default => array_fill(0, $jumlah, 'center'),
        };
        $colWidth = $jumlah > 0 ? round(100 / $jumlah, 2) : 100;
        $showTanggal = ($komp['show_tanggal'] ?? false) && !empty($komp['tanggal'] ?? '');
        $tanggalHtml = $showTanggal ? static::fill($komp['tanggal'], $data) : '';
        $ttdCols = '';
        $lastIdx = count($kolom) - 1;
        foreach ($kolom as $i => $kol) {
            $jabatan   = static::fill($kol['jabatan'] ?? '', $data);
            $nama      = static::fill($kol['nama'] ?? '', $data);
            $nik       = static::fill($kol['nik'] ?? '', $data);
            $textAlign = $alignMap[$i] ?? 'center';
            $tanggalRow = $showTanggal ? "<tr><td style=\"padding: 0 0 2mm 0; text-align: {$textAlign};\">{$tanggalHtml}</td></tr>" : '';
            // QR hanya di kolom terakhir, di dalam area spasi tanda tangan
            $qrInGap = ($i === $lastIdx && ! $qrHidden)
                ? $qrHtml
                : '';
            $gapContent = $qrInGap ?: '&nbsp;';
            $ttdCols  .= <<<HTML
<td style="width: {$colWidth}%; vertical-align: top; text-align: {$textAlign}; padding: 0 4px; font-size: {$size};">
    <table style="display: inline-table; border-collapse: collapse; text-align: {$textAlign};">
        <tbody>
            {$tanggalRow}
            <tr>
                <td style="padding: 0; text-align: {$textAlign};">{$jabatan}</td>
            </tr>
            <tr>
                <td style="height: {$signatureGap}; padding: 0; text-align: {$textAlign}; vertical-align: middle;">
                    {$gapContent}
                </td>
            </tr>
            <tr>
                <td style="padding: 0; font-weight: bold; text-align: {$textAlign};">{$nama}</td>
            </tr>
            <tr>
                <td style="padding: 1.5mm 0 0 0; text-align: {$textAlign};">{$nik}</td>
            </tr>
        </tbody>
    </table>
</td>
HTML;
        }

return <<<HTML
<table style="width: 100%; border-collapse: collapse; margin-top: {$tableMarginTop}; font-size: {$size};">
    <tbody>
        <tr>
            {$ttdCols}
        </tr>
    </tbody>
</table>
HTML;
    }
    protected static function renderTembusan(array $komp, array $data): string
    {
        $items = $komp['items'] ?? [];
        if (empty($items)) return '';
        $size = $komp['font_size'] ?? '12pt';

        $html = "<div style=\"font-size: {$size}; line-height: 1.35; margin-top: 6px;\">\n";
        $html .= "<p style=\"margin: 0 0 2px 0;\">Tembusan:</p>\n";
        foreach ($items as $i => $item) {
            $no    = $i + 1;
            $label = static::fill($item, $data);
            $html .= "<p style=\"margin: 0;\">{$no}. {$label}</p>\n";
        }
        $html .= "</div>\n";
        return $html;
    }
    protected static function paragraphIndentStyle(string $rawText, mixed $configuredIndent = null, bool $forceHanging = false): string
    {
        $text = trim($rawText);
        $configuredIndent = is_numeric($configuredIndent) ? (int) $configuredIndent : 0;

        if ($text === '') {
            return $configuredIndent > 0 ? "text-indent: {$configuredIndent}px;" : '';
        }

        $isNumbered = preg_match('/^\s*\d+[.)-]?\s+/', $text) === 1;
        $isBulleted = preg_match('/^\s*[-*•]\s+/', $text) === 1;

        if (! $forceHanging && ! $isNumbered && ! $isBulleted) {
            return $configuredIndent > 0 ? "text-indent: {$configuredIndent}px;" : '';
        }

        $hang = $configuredIndent > 0 ? $configuredIndent : 18;

        return "padding-left: {$hang}px; text-indent: -{$hang}px;";
    }

    protected static function renderSpasi(array $komp): string
    {
        $tinggi = intval($komp['tinggi'] ?? 20);
        return "<div style=\"height: {$tinggi}px;\"></div>\n";
    }
    protected static function renderGaris(): string
    {
        return "<hr style=\"border: none; border-top: 1px solid #CBD5E1; margin: 8px 0;\" />\n";
    }
    protected static function fill(string $teks, array $data): string
    {
        return TemplatePlaceholderReplacer::replace($teks, $data, false);
    }
}



