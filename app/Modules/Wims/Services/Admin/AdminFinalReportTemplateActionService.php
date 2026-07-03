<?php

namespace App\Modules\Wims\Services\Admin;

use App\Models\Magang\FinalReportTemplate;
use App\Modules\Wims\Services\Shared\Report\FinalReportTemplateAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class AdminFinalReportTemplateActionService
{
    public function __construct(
        private readonly FinalReportTemplateAccessService $templateAccessService,
    ) {}

    public function create(array $validated, ?int $userId): FinalReportTemplate
    {
        /** @var UploadedFile $file */
        $file = $validated['file'];
        $storedPath = null;

        try {
            $storedPath = $this->templateAccessService->storeTemplateFile($file);

            return DB::transaction(function () use ($validated, $userId, $file, $storedPath): FinalReportTemplate {
                if ($validated['is_active']) {
                    $this->deactivateOtherTemplates();
                }

                return FinalReportTemplate::create([
                    'title' => trim((string) $validated['title']),
                    'description' => filled($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
                    'file_path' => $storedPath,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'is_active' => (bool) $validated['is_active'],
                    'uploaded_by' => $userId,
                ]);
            });
        } catch (Throwable $throwable) {
            $this->templateAccessService->deleteIfExists($storedPath);

            throw $throwable;
        }
    }

    public function update(FinalReportTemplate $template, array $validated, ?int $userId): FinalReportTemplate
    {
        /** @var UploadedFile|null $file */
        $file = $validated['file'] ?? null;
        $storedPath = null;
        $oldPath = $template->file_path;

        try {
            if ($file instanceof UploadedFile) {
                $storedPath = $this->templateAccessService->storeTemplateFile($file);
            }

            $updatedTemplate = DB::transaction(function () use ($template, $validated, $userId, $file, $storedPath): FinalReportTemplate {
                if ($validated['is_active']) {
                    $this->deactivateOtherTemplates($template->id);
                }

                $payload = [
                    'title' => trim((string) $validated['title']),
                    'description' => filled($validated['description'] ?? null) ? trim((string) $validated['description']) : null,
                    'is_active' => (bool) $validated['is_active'],
                    'uploaded_by' => $userId ?? $template->uploaded_by,
                ];

                if ($file instanceof UploadedFile && filled($storedPath)) {
                    $payload['file_path'] = $storedPath;
                    $payload['original_name'] = $file->getClientOriginalName();
                    $payload['mime_type'] = $file->getClientMimeType();
                    $payload['file_size'] = $file->getSize();
                }

                $template->update($payload);

                return $template->fresh();
            });

            if (filled($storedPath) && $storedPath !== $oldPath) {
                $this->templateAccessService->deleteIfExists($oldPath);
            }

            return $updatedTemplate;
        } catch (Throwable $throwable) {
            $this->templateAccessService->deleteIfExists($storedPath);

            throw $throwable;
        }
    }

    public function delete(FinalReportTemplate $template): void
    {
        $path = $template->file_path;

        DB::transaction(function () use ($template): void {
            $template->delete();
        });

        $this->templateAccessService->deleteIfExists($path);
    }

    private function deactivateOtherTemplates(?int $ignoreTemplateId = null): void
    {
        FinalReportTemplate::query()
            ->when($ignoreTemplateId !== null, fn (Builder $query) => $query->whereKeyNot($ignoreTemplateId))
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
}

