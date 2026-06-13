<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Logbook PKL</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #161616;
        }

        .header {
            margin-bottom: 18px;
        }

        .title {
            margin: 0 0 12px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .identity {
            margin: 0;
            padding: 0;
        }

        .identity-item {
            margin: 0 0 4px;
            line-height: 1.45;
        }

        .identity-label {
            display: inline-block;
            width: 170px;
            font-weight: bold;
        }

        .identity-colon {
            display: inline-block;
            width: 10px;
            text-align: center;
        }

        .table-wrap {
            margin-top: 16px;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid #d0d7de;
            padding: 6px;
            vertical-align: top;
        }

        .detail-table th {
            background: #f4f4f4;
            text-align: left;
            font-weight: bold;
        }

        .detail-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .multiline {
            white-space: pre-line;
            line-height: 1.45;
        }

        .empty {
            text-align: center;
            color: #525252;
            padding: 14px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">LOGBOOK PKL</p>
        <div class="identity">
            <p class="identity-item"><span class="identity-label">Nama</span><span class="identity-colon">:</span>{{ $student['name'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">NIM</span><span class="identity-colon">:</span>{{ $student['nim'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">Prodi</span><span class="identity-colon">:</span>{{ $student['program_studi'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">Perusahaan/Mitra</span><span class="identity-colon">:</span>{{ $internship['company'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">Periode</span><span class="identity-colon">:</span>{{ $internship['period'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">Dosen Pembimbing Lapangan</span><span class="identity-colon">:</span>{{ $internship['supervisor_lecturer'] ?? '-' }}</p>
            <p class="identity-item"><span class="identity-label">Pembimbing Lapangan Mitra</span><span class="identity-colon">:</span>{{ $internship['mentor'] ?? '-' }}</p>
        </div>
    </div>

    <div class="table-wrap">
        <table class="detail-table">
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 10%;">Tanggal</th>
                    <th style="width: 7%;">Jam Mulai</th>
                    <th style="width: 7%;">Jam Selesai</th>
                    <th style="width: 25%;">Deskripsi Aktivitas</th>
                    <th style="width: 18%;">Kompetensi yang Dicapai</th>
                    <th style="width: 10%;">Status Review</th>
                    <th style="width: 19%;">Catatan Pembimbing Mitra</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['number'] }}</td>
                        <td>{{ $row['date'] ?? '-' }}</td>
                        <td>{{ $row['start_time'] ?? '-' }}</td>
                        <td>{{ $row['end_time'] ?? '-' }}</td>
                        <td class="multiline">{!! nl2br(e($row['activity'] ?? '-')) !!}</td>
                        <td class="multiline">{!! nl2br(e($row['competency'] ?? '-')) !!}</td>
                        <td>{{ $row['status'] ?? '-' }}</td>
                        <td class="multiline">{!! nl2br(e($row['mentor_note'] ?? '-')) !!}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="empty">Belum ada data logbook untuk periode PKL aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
