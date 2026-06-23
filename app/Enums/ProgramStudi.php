<?php

namespace App\Enums;

enum ProgramStudi: string
{
    case Informatika = 'Informatika';
    case SistemInformasi = 'Sistem Informasi';
    case Matematika = 'Matematika';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
