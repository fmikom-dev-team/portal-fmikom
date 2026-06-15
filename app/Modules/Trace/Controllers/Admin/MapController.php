<?php

namespace App\Modules\Trace\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MapController extends Controller
{
    public function index()
    {
        return Inertia::render('Modules/Trace/Admin/Map');
    }

    public function getData(Request $request)
    {
        $statusFilter = $request->status_filter ?? 'semua';

        // Total alumni for completion rate
        $totalAlumni = DB::table('profil_alumnis')->count();

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
                DB::raw("'Bekerja' as tipe_lokasi")
            )
            ->where('career_history.is_current', true)
            ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
            ->whereNotNull('career_history.latitude')
            ->whereNotNull('career_history.longitude');

        if ($request->angkatan) {
            $workQuery->where('profil_alumnis.angkatan', $request->angkatan);
        }
        if ($request->program_studi) {
            $workQuery->where('program_studis.nama', $request->program_studi);
        }
        if ($request->sektor) {
            $workQuery->where('employment.sektor_industri', $request->sektor);
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

        if ($request->angkatan) {
            $studyQuery->where('profil_alumnis.angkatan', $request->angkatan);
        }
        if ($request->program_studi) {
            $studyQuery->where('program_studis.nama', $request->program_studi);
        }

        // Home Query for Mencari Kerja
        $homeQuery = DB::table('profil_alumnis')
            ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
            ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id')
            ->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
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
            ->where('career_history.is_current', true)
            ->where('career_history.status', 'mencari_kerja')
            ->whereNotNull('profil_alumnis.latitude_rumah')
            ->whereNotNull('profil_alumnis.longitude_rumah');

        if ($request->angkatan) {
            $homeQuery->where('profil_alumnis.angkatan', $request->angkatan);
        }
        if ($request->program_studi) {
            $homeQuery->where('program_studis.nama', $request->program_studi);
        }

        $results = collect();

        if ($statusFilter === 'bekerja') {
            $results = $workQuery->get();
        } elseif ($statusFilter === 'lainnya') {
            $results = $studyQuery->get()->concat($homeQuery->get());
        } else {
            $results = $workQuery->get()->concat($studyQuery->get())->concat($homeQuery->get());
        }

        $mappedCount = $results->count();

        // Calculate global mapped alumni (overall)
        $globalWorkQueryIds = DB::table('career_history')
            ->where('career_history.is_current', true)
            ->whereIn('career_history.status', ['bekerja', 'wirausaha'])
            ->whereNotNull('career_history.latitude')
            ->whereNotNull('career_history.longitude')
            ->pluck('profil_alumni_id');

        $globalStudyQueryIds = DB::table('career_history')
            ->where('career_history.is_current', true)
            ->where('career_history.status', 'lanjut_studi')
            ->whereNotNull('career_history.latitude')
            ->whereNotNull('career_history.longitude')
            ->pluck('profil_alumni_id');

        $globalHomeQueryIds = DB::table('profil_alumnis')
            ->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
            ->where('career_history.is_current', true)
            ->where('career_history.status', 'mencari_kerja')
            ->whereNotNull('profil_alumnis.latitude_rumah')
            ->whereNotNull('profil_alumnis.longitude_rumah')
            ->pluck('profil_alumnis.id');

        $globalMappedAlumniIds = $globalWorkQueryIds
            ->concat($globalStudyQueryIds)
            ->concat($globalHomeQueryIds)
            ->unique();
        $totalMappedOverall = $globalMappedAlumniIds->count();
        $globalCompletionRate = $totalAlumni > 0 ? round(($totalMappedOverall / $totalAlumni) * 100, 1) : 0;

        // Calculate filtered stats
        $isFilterActive = !empty($request->angkatan)
            || !empty($request->program_studi)
            || !empty($request->sektor)
            || ($statusFilter !== 'semua');

        $filteredTotal = 0;
        $filteredMapped = 0;
        $filteredRate = 0;

        if ($isFilterActive) {
            $filteredQuery = DB::table('profil_alumnis')
                ->join('users', 'profil_alumnis.user_id', '=', 'users.id')
                ->leftJoin('program_studis', 'users.program_studi_id', '=', 'program_studis.id');

            if ($request->angkatan) {
                $filteredQuery->where('profil_alumnis.angkatan', $request->angkatan);
            }
            if ($request->program_studi) {
                $filteredQuery->where('program_studis.nama', $request->program_studi);
            }

            if ($statusFilter === 'bekerja') {
                $filteredQuery->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                    ->where('career_history.is_current', true)
                    ->whereIn('career_history.status', ['bekerja', 'wirausaha']);
                if ($request->sektor) {
                    $filteredQuery->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                        ->where('employment.sektor_industri', $request->sektor);
                }
            } elseif ($statusFilter === 'lainnya') {
                $filteredQuery->where(function($q) {
                    $q->whereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true)
                            ->where('career_history.status', 'lanjut_studi');
                    })->orWhereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('career_history')
                            ->whereColumn('career_history.profil_alumni_id', 'profil_alumnis.id')
                            ->where('career_history.is_current', true)
                            ->where('career_history.status', 'mencari_kerja');
                    });
                });
            } else {
                // statusFilter === 'semua'
                if ($request->sektor) {
                    $filteredQuery->join('career_history', 'profil_alumnis.id', '=', 'career_history.profil_alumni_id')
                        ->join('employment', 'career_history.id', '=', 'employment.career_history_id')
                        ->where('career_history.is_current', true)
                        ->where('employment.sektor_industri', $request->sektor);
                }
            }

            $filteredTotal = $filteredQuery->distinct('profil_alumnis.id')->count('profil_alumnis.id');
            $filteredMapped = $mappedCount;
            $filteredRate = $filteredTotal > 0 ? round(($filteredMapped / $filteredTotal) * 100, 1) : 0;
        }

        return response()->json([
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
                ]
            ]
        ]);
    }
}

