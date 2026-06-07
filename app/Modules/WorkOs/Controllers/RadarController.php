<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RadarController extends Controller
{
    public function updateConfig(Request $r)
    {
        return app(DashboardController::class)->updateRadarConfig($r);
    }

    public function storeBlockedItem(Request $r)
    {
        return app(DashboardController::class)->storeBlockedItem($r);
    }

    public function destroyBlockedItem($id)
    {
        return app(DashboardController::class)->destroyBlockedItem($id);
    }

    public function resetDetections()
    {
        return app(DashboardController::class)->resetDetections();
    }
}
