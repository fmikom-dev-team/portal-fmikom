<?php

namespace App\Modules\Trace\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AlumniMapService
{
    public function getMapData(array $filters): array
    {
        $statusFilter = $filters['status_filter'] ?? 'semua';
        if ($statusFilter === 'belum_bekerja') {
            $statusFilter = 'mencari_kerja';
        }

        $totalAlumni = Cache::remember('map_total_alumni', 3600, fn () => DB::table('profil_alumnis')->count());

        $workQuery = DB::table('career_history')
            ->join('employment', 'career_history.id', '=', 'employment.career_history_id')
            ->join('profil_alumnis', 'career_history.profil_alumni_id', '=', 'profil_alumnis.id')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->join('kota', 'career_history.kota_id', '=', 'kota.id')
            ->select(
                'users.name as nama_lengkap',
                'profil_alumnis.id as profil_alumni_id',
                'users.nomor_induk as nim',
                'profil_alumnis.angkatan',
                'program_studis.nama as program_studi',
                'employment.nama_perusahaan as instansi',
                'employment.jabatan as detail',
                'employment.sektor_industri',
                'kota.name as nama_kota',
                'career_history.latitude',
                'career_history.longitude',
                DB::raw("CASE WHEN career_history.status = 'wirausaha' THEN 'Wirausaha' ELSE 'Bekerja' END as tipe_lokasi")
            )
            ->where('career_history.is_current', true)
            ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
            ->whereNotNull('career_history.latitude')
            ->whereNotNull('career_history.longitude');

        if ($filters['angkatan'] ?? null) {
            $workQuery->where('profil_alumnis.angkatan', $filters['angkatan']);
        }
        if ($filters['program_studi'] ?? null) {
            $workQuery->where('program_studis.nama', $filters['program_studi']);
        }
        if ($filters['sektor'] ?? null) {
            $workQuery->where('employment.sektor_industri', $filters['sektor']);
        }

        // Education Query
        $studyQuery = DB::table('career_history')
            ->join('education', 'career_history.id', '=', 'education.career_history_id')
            ->join('profil_alumnis', 'career_history.profil_alumni_id', '=', 'profil_alumnis.id')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->leftJoin('kota', 'career_history.kota_id', '=', 'kota.id')
            ->select(
                'users.name as nama_lengkap',
                'profil_alumnis.id as profil_alumni_id',
                'users.nomor_induk as nim',
                'profil_alumnis.angkatan',
                'program_studis.nama as program_studi',
                'education.nama_universitas as instansi',
                'education.program_studi_lanjutan as detail',
                DB::raw("'Pendidikan' as sektor_industri"),
                DB::raw("COALESCE(kota.name, 'Lokasi Kampus') as nama_kota"),
                'career_history.latitude',
                'career_history.longitude',
                DB::raw("'Lanjut Studi' as tipe_lokasi")
            )
            ->where('career_history.is_current', true)
            ->where('career_history.status', 'lanjut_studi')
            ->whereNotNull('career_history.latitude')
            ->whereNotNull('career_history.longitude');

        if ($filters['angkatan'] ?? null) {
            $studyQuery->where('profil_alumnis.angkatan', $filters['angkatan']);
        }
        if ($filters['program_studi'] ?? null) {
            $studyQuery->where('program_studis.nama', $filters['program_studi']);
        }

        // Home Query for Mencari Kerja
        $homeQuery = DB::table('profil_alumnis')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->leftJoin('career_history', function ($join) {
                $join->on('profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                    ->where('career_history.is_current', true);
            })
            ->leftJoin('kota', 'profil_alumnis.kota_id', '=', 'kota.id')
            ->select(
                'users.name as nama_lengkap',
                'profil_alumnis.id as profil_alumni_id',
                'users.nomor_induk as nim',
                'profil_alumnis.angkatan',
                'program_studis.nama as program_studi',
                'profil_alumnis.alamat_rumah as instansi',
                DB::raw("'Mencari Kerja / Belum Bekerja' as detail"),
                DB::raw("'Lainnya' as sektor_industri"),
                DB::raw("COALESCE(kota.name, 'Domisili') as nama_kota"),
                'profil_alumnis.latitude_rumah as latitude',
                'profil_alumnis.longitude_rumah as longitude',
                DB::raw("'Belum Bekerja' as tipe_lokasi")
            )
            ->where(function ($query) {
                $query->where('career_history.status', 'mencari_kerja')
                    ->orWhereNull('career_history.id');
            })
            ->whereNotNull('profil_alumnis.latitude_rumah')
            ->whereNotNull('profil_alumnis.longitude_rumah');

        if ($filters['angkatan'] ?? null) {
            $homeQuery->where('profil_alumnis.angkatan', $filters['angkatan']);
        }
        if ($filters['program_studi'] ?? null) {
            $homeQuery->where('program_studis.nama', $filters['program_studi']);
        }

        $results = collect();

        if ($statusFilter === 'bekerja') {
            $results = $workQuery->where('career_history.status', 'bekerja')->get();
        } elseif ($statusFilter === 'wirausaha') {
            $results = $workQuery->where('career_history.status', 'wirausaha')->get();
        } elseif ($statusFilter === 'lanjut_studi') {
            $results = $studyQuery->get();
        } elseif ($statusFilter === 'mencari_kerja') {
            $results = $homeQuery->get();
        } elseif ($statusFilter === 'lainnya') {
            $results = $studyQuery->get()->concat($homeQuery->get());
        } else {
            $results = $workQuery->get();
            $results = $results->concat($studyQuery->get())->concat($homeQuery->get());
        }

        $mappedCount = $results->count();

        // Global mapped count (cached 15 min — expensive subquery, same for all filters)
        $totalMappedOverall = Cache::remember('map_total_mapped', 900, fn () => DB::table('profil_alumnis')
            ->where(function ($query) {
                // Alumni with work/study location mapped
                $query->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('career_history')
                        ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                        ->where('career_history.is_current', true)
                        ->whereIn('career_history.status', ['bekerja', 'wirausaha', 'lanjut_studi'])
                        ->whereNotNull('career_history.latitude')
                        ->whereNotNull('career_history.longitude');
                })
                // Or alumni seeking work with home location mapped
                    ->orWhere(function ($sub) {
                        $sub->where(function ($careerStatus) {
                            $careerStatus->whereExists(function ($inner) {
                                $inner->select(DB::raw(1))
                                    ->from('career_history')
                                    ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                                    ->where('career_history.is_current', true)
                                    ->where('career_history.status', 'mencari_kerja');
                            })->orWhereNotExists(function ($inner) {
                                $inner->select(DB::raw(1))
                                    ->from('career_history')
                                    ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                                    ->where('career_history.is_current', true);
                            });
                        })
                            ->whereNotNull('profil_alumnis.latitude_rumah')
                            ->whereNotNull('profil_alumnis.longitude_rumah');
                    });
            })
            ->count()
        );
        $globalCompletionRate = $totalAlumni > 0 ? round(($totalMappedOverall / $totalAlumni) * 100, 1) : 0;

        // Calculate filtered stats
        $isFilterActive = ! empty($filters['angkatan'])
            || ! empty($filters['program_studi'])
            || ! empty($filters['sektor'])
            || ($statusFilter !== 'semua');

        $filteredTotal = 0;
        $filteredMapped = 0;
        $filteredRate = 0;

        if ($isFilterActive) {
            $filteredQuery = DB::table('profil_alumnis')
                ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
                ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id');

            if ($filters['angkatan'] ?? null) {
                $filteredQuery->where('profil_alumnis.angkatan', $filters['angkatan']);
            }
            if ($filters['program_studi'] ?? null) {
                $filteredQuery->where('program_studis.nama', $filters['program_studi']);
            }

            if (in_array($statusFilter, ['bekerja', 'wirausaha'], true)) {
                $filteredQuery->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                    ->where('career_history.is_current', true)
                    ->where('career_history.status', $statusFilter);
                if ($filters['sektor'] ?? null) {
                    $filteredQuery->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                        ->where('employment.sektor_industri', $filters['sektor']);
                }
            } elseif ($statusFilter === 'lanjut_studi') {
                $filteredQuery->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('career_history')
                        ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                        ->where('career_history.is_current', true)
                        ->where('career_history.status', 'lanjut_studi');
                });
            } elseif ($statusFilter === 'mencari_kerja') {
                $filteredQuery->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true)
                            ->where('career_history.status', 'mencari_kerja');
                    })->orWhereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true);
                    });
                });
            } elseif ($statusFilter === 'lainnya') {
                $filteredQuery->where(function ($q) {
                    $q->whereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true)
                            ->where('career_history.status', 'lanjut_studi');
                    })->orWhereExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true)
                            ->where('career_history.status', 'mencari_kerja');
                    })->orWhereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true);
                    });
                });
            } else {
                // statusFilter === 'semua'
                if ($filters['sektor'] ?? null) {
                    $filteredQuery->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                        ->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                        ->where('career_history.is_current', true)
                        ->where('employment.sektor_industri', $filters['sektor']);
                }
            }

            $filteredTotal = $filteredQuery->distinct('profil_alumnis.id')->count('profil_alumnis.id');
            $filteredMapped = $mappedCount;
            $filteredRate = $filteredTotal > 0 ? round(($filteredMapped / $filteredTotal) * 100, 1) : 0;
        }

        return [
            'status' => 'success',
            'data' => $results,
            'meta' => [
                'total_alumni' => $totalAlumni,
                'mapped_count' => $totalMappedOverall,
                'completion_rate' => $globalCompletionRate,
                'is_filter_active' => $isFilterActive,
                'filtered' => [
                    'total' => $filteredTotal,
                    'mapped' => $filteredMapped,
                    'rate' => $filteredRate,
                ],
            ],
        ];
    }
}
