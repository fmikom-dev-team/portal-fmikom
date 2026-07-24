<?php

namespace App\Modules\Pagi\Services;

use App\Concerns\HandlesImageCompression;
use App\Models\Pagi\PagiCv;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class PagiCvService
{
    use HandlesImageCompression;

    private const ERROR_MAX_CV_LIMIT = 'Batas maksimal pembuatan CV adalah 3.';

    /**
     * Create a new CV record.
     */
    public function createCv(User $user, string $templateId, ?string $title): PagiCv
    {
        $existingCvsCount = PagiCv::query()->where('user_id', $user->id)->count('*');
        if ($existingCvsCount >= 3) {
            throw new \RuntimeException(self::ERROR_MAX_CV_LIMIT);
        }

        $profileData = $this->mapUserProfileToCvData($user);

        $customization = [
            'primary_color' => $this->getDefaultColorForTemplate($templateId),
            'font_family' => 'GT Standard M',
            'font_size' => '11pt',
            'line_height' => '1.4',
            'spacing' => 'normal',
            'section_order' => [
                'summary',
                'experience',
                'education',
                'organizations',
                'skills',
                'certifications',
                'trainings',
                'achievements',
                'languages',
                'references',
            ],
            'sections_visibility' => [
                'summary' => true,
                'experience' => true,
                'education' => true,
                'organizations' => true,
                'skills' => true,
                'certifications' => true,
                'trainings' => true,
                'achievements' => true,
                'languages' => true,
                'references' => true,
            ],
        ];

        $finalTitle = $title ?: 'CV '.ucfirst(str_replace('-', ' ', $templateId));

        return PagiCv::query()->create([
            'user_id' => $user->id,
            'title' => $finalTitle,
            'template_id' => $templateId,
            'personal_info' => $profileData['personal_info'],
            'education' => $profileData['education'] ?? [],
            'experience' => [],
            'organizations' => [],
            'skills' => $profileData['skills'],
            'certifications' => $profileData['certifications'],
            'trainings' => [],
            'achievements' => [],
            'languages' => $profileData['languages'],
            'references' => [],
            'customization' => $customization,
            'status' => 'draft',
        ]);
    }

    /**
     * Update an existing CV record.
     */
    public function updateCv(PagiCv $cv, User $user, array $data): PagiCv
    {
        $data = $this->sanitizeInputRecursive($data);

        // Force name and email to be the auth user's real name and email
        if (isset($data['personal_info'])) {
            $data['personal_info']['name'] = $user->name;
            $data['personal_info']['email'] = $user->email;
        }

        // Auto-compute status based on completeness
        $data['status'] = $this->determineCvStatus($data);

        $cv->update($data);

        return $cv;
    }

    /**
     * Duplicate a CV record.
     */
    public function duplicateCv(PagiCv $cv, int $userId): PagiCv
    {
        $existingCvsCount = PagiCv::query()->where('user_id', $userId)->count('*');
        if ($existingCvsCount >= 3) {
            throw new \RuntimeException(self::ERROR_MAX_CV_LIMIT);
        }

        $newCv = $cv->replicate();
        $newCv->title = 'Salinan dari '.$cv->title;
        $newCv->save();

        return $newCv;
    }

    /**
     * Upload a custom photo for the CV.
     */
    public function uploadPhoto(PagiCv $cv, $photoFile): string
    {
        $path = $this->compressAndSaveImage($photoFile, 'cv-photos', 400, 400, 80);
        if (! $path) {
            throw new \RuntimeException('Failed to upload photo.');
        }

        $personalInfo = $cv->personal_info ?? [];

        // Delete old photo if it exists and is specific to cv
        if (! empty($personalInfo['foto_path']) && str_contains($personalInfo['foto_path'], 'cv-photos/')) {
            Storage::disk('public')->delete($personalInfo['foto_path']);
        }

        $personalInfo['foto_path'] = $path;
        $cv->update(['personal_info' => $personalInfo]);

        return $path;
    }

    /**
     * Map user profile data to standardized CV format.
     */
    public function mapUserProfileToCvData(User $user): array
    {
        $personalInfo = [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->metadata['phone'] ?? '',
            'location' => $user->location ?? 'Banyumas, Indonesia',
            'website' => $user->website ?? '',
            'linkedin' => $user->linkedin ?? '',
            'github' => $user->github ?? '',
            'instagram' => $user->instagram ?? '',
            'job_title' => $user->role_title ?? '',
            'summary' => $user->bio ?? '',
            'foto_path' => $user->foto_path ?? '',
        ];

        $certificates = [];
        $profileCertificates = $user->metadata['certificates'] ?? [];
        if (is_array($profileCertificates)) {
            foreach ($profileCertificates as $index => $cert) {
                $certificates[] = [
                    'id' => $index + 1,
                    'name' => $cert['title'] ?? '',
                    'issuer' => $cert['issuer'] ?? '',
                    'date' => $cert['date'] ?? '',
                    'credential_id' => $cert['credentialId'] ?? '',
                    'credential_url' => '',
                ];
            }
        }

        $skills = [];
        $profileSkills = $user->metadata['skills'] ?? [];
        if (is_array($profileSkills)) {
            foreach ($profileSkills as $index => $skillItem) {
                $skillName = is_array($skillItem) ? ($skillItem['name'] ?? '') : (string) $skillItem;
                if (empty(trim($skillName))) {
                    continue;
                }
                $skills[] = [
                    'id' => $index + 1,
                    'name' => $skillName,
                    'level' => is_array($skillItem) ? ($skillItem['percentage'] ?? 80) : 80,
                ];
            }
        }

        $languages = [];
        $profileLanguages = $user->metadata['languages'] ?? [];
        if (is_array($profileLanguages)) {
            foreach ($profileLanguages as $index => $langItem) {
                $langName = is_array($langItem) ? ($langItem['language'] ?? $langItem['name'] ?? '') : (string) $langItem;
                $langProf = is_array($langItem) ? ($langItem['proficiency'] ?? 'Professional working proficiency') : 'Professional working proficiency';
                if (empty(trim($langName))) {
                    continue;
                }
                $languages[] = [
                    'id' => $index + 1,
                    'name' => $langName,
                    'proficiency' => $langProf,
                ];
            }
        }

        $education = [];
        $profileEducation = $user->metadata['educations'] ?? [];
        if (is_array($profileEducation)) {
            foreach ($profileEducation as $index => $edu) {
                $education[] = [
                    'id' => $index + 1,
                    'school' => $edu['institution'] ?? '',
                    'degree' => $edu['level'] ?? '',
                    'field_of_study' => $edu['major'] ?? '',
                    'start_date' => $edu['start_date'] ?? '',
                    'end_date' => $edu['end_date'] ?? '',
                    'description' => $edu['description'] ?? '',
                ];
            }
        }

        return [
            'personal_info' => $personalInfo,
            'skills' => $skills,
            'certifications' => $certificates,
            'languages' => $languages,
            'education' => $education,
        ];
    }

    /**
     * Recursively sanitize input to prevent HTML/Script tag injection.
     */
    public function sanitizeInputRecursive($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->sanitizeInputRecursive($val);
            }

            return $value;
        }

        if (is_string($value)) {
            return strip_tags($value);
        }

        return $value;
    }

    /**
     * Helper to get default template accent colors.
     */
    private function getDefaultColorForTemplate(string $templateId): string
    {
        switch ($templateId) {
            case 'ats-professional':
                return '#1e3a8a'; // Blue 900
            case 'modern-sidebar':
                return '#0ea5e9'; // Cyan 500
            case 'executive':
                return '#111827'; // Dark Slate
            case 'creative-minimal':
                return '#6b21a8'; // Purple 800
            case 'student-resume':
                return '#10b981'; // Emerald 500
            default:
                return '#1e293b';
        }
    }

    /**
     * Auto-compute CV status based on completeness.
     */
    private function determineCvStatus(array $data): string
    {
        $pi = $data['personal_info'] ?? [];
        $hasBasic = ! empty(trim($pi['name'] ?? ''))
            && ! empty(trim($pi['email'] ?? ''))
            && ! empty(trim($pi['phone'] ?? ''))
            && ! empty(trim($pi['summary'] ?? ''));
        $hasSection = ! empty($data['education']) || ! empty($data['experience']);

        return ($hasBasic && $hasSection) ? 'published' : 'draft';
    }
}
