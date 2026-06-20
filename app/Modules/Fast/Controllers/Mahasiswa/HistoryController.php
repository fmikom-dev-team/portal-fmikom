<?php

namespace App\Modules\Fast\Controllers\Mahasiswa;

use App\Modules\Fast\Controllers\Shared\User\HistoryController as BaseHistoryController;

class HistoryController extends BaseHistoryController
{
    protected function basePath(): string
    {
        return '/mahasiswa';
    }
}
