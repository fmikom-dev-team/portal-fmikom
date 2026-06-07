<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{
    public function store(Request $r)
    {
        return app(DashboardController::class)->storePermission($r);
    }

    public function update(Request $r, $p)
    {
        return app(DashboardController::class)->updatePermission($r, $p);
    }

    public function destroy($p)
    {
        return app(DashboardController::class)->destroyPermission($p);
    }
}
