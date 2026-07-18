<?php

namespace App\Modules\Pagi\Services;

use App\Models\Pagi\PagiCv;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PagiEducationService
{
    /**
     * Store a new education record.
     */
    public function store(User $user, array $validatedData): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('educations', $metadata)) {
            $metadata['educations'] = [];
        }

        $newId = count($metadata['educations']) > 0
            ? max(array_column($metadata['educations'], 'id')) + 1
            : 1;

        $newEdu = [
            'id' => $newId,
            'level' => $validatedData['level'],
            'institution' => $validatedData['institution'],
            'major' => $validatedData['major'] ?? '',
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'description' => $validatedData['description'] ?? '',
        ];

        $metadata['educations'][] = $newEdu;
        $user->metadata = $metadata;
        $user->save();

        // Sync with CV Builder
        $this->syncEducationsToCvs($user, $metadata['educations']);

        return response()->json([
            'success' => true,
            'educations' => $metadata['educations'],
            'message' => 'Riwayat pendidikan berhasil ditambahkan!',
        ]);
    }

    /**
     * Update an existing education record.
     */
    public function update(User $user, string $id, array $validatedData): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('educations', $metadata)) {
            return response()->json(['success' => false, 'message' => 'Data pendidikan tidak ditemukan.'], 404);
        }

        $foundIndex = -1;
        foreach ($metadata['educations'] as $index => $edu) {
            if ($edu['id'] == $id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex === -1) {
            return response()->json(['success' => false, 'message' => 'Data pendidikan tidak ditemukan.'], 404);
        }

        $metadata['educations'][$foundIndex] = [
            'id' => (int) $id,
            'level' => $validatedData['level'],
            'institution' => $validatedData['institution'],
            'major' => $validatedData['major'] ?? '',
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'description' => $validatedData['description'] ?? '',
        ];

        $user->metadata = $metadata;
        $user->save();

        // Sync with CV Builder
        $this->syncEducationsToCvs($user, $metadata['educations']);

        return response()->json([
            'success' => true,
            'educations' => $metadata['educations'],
            'message' => 'Riwayat pendidikan berhasil diperbarui!',
        ]);
    }

    /**
     * Delete an education record.
     */
    public function delete(User $user, string $id): JsonResponse
    {
        $metadata = $user->metadata ?? [];

        if (! array_key_exists('educations', $metadata)) {
            return response()->json(['success' => false, 'message' => 'Data pendidikan tidak ditemukan.'], 404);
        }

        $filtered = array_filter($metadata['educations'], function ($edu) use ($id) {
            return $edu['id'] != $id;
        });

        $metadata['educations'] = array_values($filtered);
        $user->metadata = $metadata;
        $user->save();

        // Sync with CV Builder
        $this->syncEducationsToCvs($user, $metadata['educations']);

        return response()->json([
            'success' => true,
            'educations' => $metadata['educations'],
            'message' => 'Riwayat pendidikan berhasil dihapus!',
        ]);
    }

    /**
     * Synchronize educational history to user's CVs.
     */
    private function syncEducationsToCvs(User $user, array $educations): void
    {
        $cvEducation = [];
        foreach ($educations as $index => $edu) {
            $cvEducation[] = [
                'id' => $index + 1,
                'school' => $edu['institution'] ?? '',
                'degree' => $edu['level'] ?? '',
                'field_of_study' => $edu['major'] ?? '',
                'start_date' => $edu['start_date'] ?? '',
                'end_date' => $edu['end_date'] ?? '',
                'description' => $edu['description'] ?? '',
            ];
        }

        $cvs = PagiCv::query()->where('user_id', $user->id)->get();
        foreach ($cvs as $cv) {
            $cv->update(['education' => $cvEducation]);
        }
    }
}
