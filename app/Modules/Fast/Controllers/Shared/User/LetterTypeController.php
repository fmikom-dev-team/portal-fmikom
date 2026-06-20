<?php

namespace App\Modules\Fast\Controllers\Shared\User;

use App\Http\Controllers\Controller;
use App\Models\JenisSurat;
use App\Modules\Fast\DTOs\SuratDataContract;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LetterTypeController extends Controller
{
    public function show(Request $request, JenisSurat $jenisSurat): JsonResponse
    {
        $user = $request->user();
        abort_if($user === null, 403);
        abort_if(! $jenisSurat->is_active || $jenisSurat->template === null, 404);
        abort_unless($this->canAccessJenisSurat($user, $jenisSurat), 404);

        return response()->json([
            'data' => [
                'id' => $jenisSurat->id,
                'nama' => $jenisSurat->nama,
                'deskripsi' => $jenisSurat->deskripsi,
                'field_config' => SuratDataContract::normalizeDynamicFieldConfig(
                    SuratDataContract::filterDynamicFieldConfig($jenisSurat->field_config ?? []),
                ),
            ],
        ]);
    }

    protected function canAccessJenisSurat($user, JenisSurat $jenisSurat): bool
    {
        $roleSlug = $user->userTypeSlug();

        if ($roleSlug === '') {
            return false;
        }

        return $jenisSurat->allowed_role_id === null
            || $jenisSurat->allowedRole?->slug === $roleSlug
            || (string) $jenisSurat->allowedRole?->slug === $roleSlug;
    }

}


