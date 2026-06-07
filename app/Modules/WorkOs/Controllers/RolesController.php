<?php

namespace App\Modules\WorkOs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RolesController extends Controller
{
    public function store(Request $r)
    {
        return app(DashboardController::class)->storeRole($r);
    }

    public function update(Request $r, $role)
    {
        return app(DashboardController::class)->updateRole($r, $role);
    }

    public function destroy($role)
    {
        return app(DashboardController::class)->destroyRole($role);
    }

    public function syncPermissions(Request $r, $role)
    {
        return app(DashboardController::class)->syncPermissions($r, $role);
    }
}
