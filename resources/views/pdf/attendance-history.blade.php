<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Presensi PKL</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #d0d7de;
            padding: 7px 6px;
            vertical-align: top;
        }

        th {
            background: #f4f4f4;
            text-align: left;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .empty {
            text-align: center;
            color: #525252;
            padding: 16px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">RIWAYAT PRESENSI PKL</p>
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
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 18%;">Tanggal</th>
                    <th style="width: 14%;">Check-in</th>
                    <th style="width: 14%;">Check-out</th>
                    <th style="width: 16%;">Status</th>
                    <th style="width: 33%;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    <tr>
                        <td>{{ $row['number'] }}</td>
                        <td>{{ $row['date'] ?? '-' }}</td>
                        <td>{{ $row['check_in'] ?? '-' }}</td>
                        <td>{{ $row['check_out'] ?? '-' }}</td>
                        <td>{{ $row['status'] ?? '-' }}</td>
                        <td>{{ $row['remark'] ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">Belum ada data presensi untuk periode PKL aktif.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
