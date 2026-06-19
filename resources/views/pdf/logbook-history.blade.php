<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Logbook PKL</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .meta td:first-child { width: 180px; font-weight: bold; background: #f9fafb; }
        .muted { color: #6b7280; }
        .cell-text { line-height: 1.45; white-space: normal; word-break: break-word; }
        .cell-text p { margin: 0; }
        .ordered-list { margin: 0; padding: 0 0 0 16px; }
        .ordered-list li { margin: 0 0 4px 0; line-height: 1.45; }
        .ordered-list li:last-child { margin-bottom: 0; }
        .plain-multiline { margin: 0; white-space: pre-line; line-height: 1.45; }
    </style>
</head>
<body>
    <h1>Logbook PKL</h1>
    <p class="muted">Ringkasan kegiatan harian mahasiswa selama periode PKL.</p>

    <h2>Identitas Mahasiswa</h2>
    <table class="meta">
        <tr><td>Nama</td><td>{{ $student['name'] ?? '-' }}</td></tr>
        <tr><td>NIM</td><td>{{ $student['nim'] ?? '-' }}</td></tr>
        <tr><td>Program Studi</td><td>{{ $student['program_studi'] ?? '-' }}</td></tr>
        <tr><td>Perusahaan</td><td>{{ $internship['company'] ?? '-' }}</td></tr>
        <tr><td>Periode PKL</td><td>{{ $internship['period'] ?? '-' }}</td></tr>
        <tr><td>Dosen Pembimbing</td><td>{{ $internship['supervisor_lecturer'] ?? '-' }}</td></tr>
        <tr><td>Pembimbing Mitra</td><td>{{ $internship['mentor'] ?? '-' }}</td></tr>
    </table>

    <h2>Riwayat Logbook</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Tanggal</th>
                <th>Jam Mulai</th>
                <th>Jam Selesai</th>
                <th style="width: 24%;">Aktivitas</th>
                <th style="width: 24%;">Kompetensi</th>
                <th>Status Review</th>
                <th>Catatan Mitra</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['number'] ?? '-' }}</td>
                <td>{{ $row['date'] ?? '-' }}</td>
                <td>{{ $row['start_time'] ?? '-' }}</td>
                <td>{{ $row['end_time'] ?? '-' }}</td>
                <td class="cell-text">
                    @if (!empty($row['activity_items']))
                        <ol class="ordered-list">
                            @foreach ($row['activity_items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <div class="plain-multiline">{!! nl2br(e($row['activity'] ?? '-')) !!}</div>
                    @endif
                </td>
                <td class="cell-text">
                    @if (!empty($row['competency_items']))
                        <ol class="ordered-list">
                            @foreach ($row['competency_items'] as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ol>
                    @else
                        <div class="plain-multiline">{!! nl2br(e($row['competency'] ?? '-')) !!}</div>
                    @endif
                </td>
                <td>{{ $row['status'] ?? '-' }}</td>
                <td class="cell-text">
                    <div class="plain-multiline">{!! nl2br(e($row['mentor_note'] ?? '-')) !!}</div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Belum ada data logbook.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</body>
</html>
