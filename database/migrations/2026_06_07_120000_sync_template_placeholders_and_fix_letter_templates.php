<?php

use App\Models\SuratTemplate;
use App\Support\TemplatePlaceholderSynchronizer;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        SuratTemplate::query()
            ->with('jenisSurat')
            ->each(function (SuratTemplate $template): void {
                $template->template_body = $this->normalizeTemplateBody(
                    $template->name,
                    (string) ($template->template_body ?? ''),
                );

                if ($template->isDirty('template_body')) {
                    $template->save();
                }

                TemplatePlaceholderSynchronizer::syncTemplate(
                    $template,
                    $template->jenisSurat?->field_config ?? [],
                );
            });
    }

    public function down(): void
    {
        // Tidak ada rollback otomatis yang aman untuk sinkronisasi metadata
        // dan perbaikan isi template yang sudah benar.
    }

    protected function normalizeTemplateBody(string $templateName, string $body): string
    {
        return match ($templateName) {
            'Surat Undangan PKL' => str_replace('{{Prodi}}', '{{program_studi}}', $body),
            'Surat Undangan Makan' => str_replace(
                ['"lampiran":"{{lampiran}}"', '"perihal":"{{perihal}"'],
                ['"lampiran":"-"', '"perihal":"Undangan"'],
                $body,
            ),
            default => $body,
        };
    }
};
