<?php

namespace App\Support;

use DOMDocument;
use DOMXPath;
use RuntimeException;
use ZipArchive;

class DocxPreview
{
    public static function isDocx(string $path, ?string $mimeType = null): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return $extension === 'docx'
            || str_contains(strtolower((string) $mimeType), 'wordprocessingml.document');
    }

    public static function renderHtml(string $absolutePath, string $title): string
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi Zip tidak tersedia untuk pratinjau DOCX.');
        }

        $zip = new ZipArchive;
        if ($zip->open($absolutePath) !== true) {
            throw new RuntimeException('File DOCX tidak dapat dibuka.');
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($documentXml === false || trim($documentXml) === '') {
            throw new RuntimeException('Isi DOCX tidak ditemukan.');
        }

        $dom = new DOMDocument;
        $dom->loadXML($documentXml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $body = $xpath->query('//w:body')->item(0);
        if (! $body) {
            throw new RuntimeException('Body DOCX tidak ditemukan.');
        }

        $content = '';
        foreach ($body->childNodes as $child) {
            $content .= self::renderNode($child, $xpath);
        }

        $safeTitle = htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{$safeTitle}</title>
    <style>
        body {
            margin: 0;
            padding: 24px;
            background: #f8fafc;
            color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
        }
        .docx-preview {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 32px 40px;
            line-height: 1.65;
            font-size: 14px;
        }
        .docx-preview h1, .docx-preview h2, .docx-preview h3, .docx-preview h4 {
            line-height: 1.25;
            margin: 0 0 12px;
        }
        .docx-preview p {
            margin: 0 0 12px;
            white-space: pre-wrap;
        }
        .docx-preview table {
            border-collapse: collapse;
            width: 100%;
            margin: 0 0 16px;
        }
        .docx-preview td, .docx-preview th {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: top;
        }
        .docx-preview img {
            max-width: 100%;
            height: auto;
        }
        .docx-note {
            max-width: 900px;
            margin: 0 auto 16px;
            padding: 12px 14px;
            border: 1px solid #dbeafe;
            border-radius: 12px;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="docx-note">Pratinjau DOCX lokal. Jika ada elemen kompleks seperti SmartArt atau gaya khusus, tampilan dapat berbeda dari file asli.</div>
    <div class="docx-preview">{$content}</div>
</body>
</html>
HTML;
    }

    protected static function renderNode(\DOMNode $node, DOMXPath $xpath): string
    {
        return match ($node->nodeName) {
            'w:p' => self::renderParagraph($node, $xpath),
            'w:tbl' => self::renderTable($node, $xpath),
            default => '',
        };
    }

    protected static function renderParagraph(\DOMNode $paragraph, DOMXPath $xpath): string
    {
        $text = '';
        foreach ($xpath->query('.//w:t|.//w:tab|.//w:br', $paragraph) as $child) {
            $text .= match ($child->nodeName) {
                'w:t' => htmlspecialchars($child->textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                'w:tab' => "\t",
                default => '<br>',
            };
        }

        if (trim(strip_tags((string) $text)) === '') {
            return '';
        }

        return '<p>'.$text.'</p>';
    }

    protected static function renderTable(\DOMNode $table, DOMXPath $xpath): string
    {
        $html = '<table>';
        foreach ($xpath->query('.//w:tr', $table) as $row) {
            $html .= '<tr>';
            foreach ($xpath->query('./w:tc', $row) as $cell) {
                $cellContent = '';
                foreach ($cell->childNodes as $child) {
                    $cellContent .= self::renderNode($child, $xpath);
                }

                $html .= '<td>'.$cellContent.'</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }
}
