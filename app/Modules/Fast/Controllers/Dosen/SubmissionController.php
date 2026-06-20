<?php

namespace App\Modules\Fast\Controllers\Dosen;

use App\Modules\Fast\Controllers\Shared\User\SubmissionController as BaseSubmissionController;

class SubmissionController extends BaseSubmissionController
{
    protected function pageName(): string
    {
        return 'dosen/Ajukan';
    }

    protected function basePath(): string
    {
        return '/dosen';
    }

    protected function dashboardRouteName(): string
    {
        return 'dosen.dashboard';
    }
}
