<?php

namespace App\Modules\Fast\Controllers\Dosen;

use App\Modules\Fast\Controllers\Shared\User\DashboardController as BaseDashboardController;

class DashboardController extends BaseDashboardController
{
    protected function pageName(): string
    {
        return 'dosen/Dashboard';
    }

    protected function submissionRouteName(): string
    {
        return 'dosen.submissions.store';
    }

    protected function basePath(): string
    {
        return '/dosen';
    }
}
