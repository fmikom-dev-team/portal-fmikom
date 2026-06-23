<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Trace\Services\AlumniMapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MapController extends Controller
{
    public function __construct(private AlumniMapService $mapService) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('Modules/Trace/Admin/Map');
    }

    public function getData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'angkatan' => 'nullable|integer|min:2000|max:'.(date('Y') + 1),
            'program_studi' => 'nullable|string|max:100',
            'sektor' => 'nullable|string|max:100',
            'status_filter' => 'nullable|string|in:semua,bekerja,wirausaha,lanjut_studi',
        ]);

        return response()->json($this->mapService->getMapData($validated));
    }
}
