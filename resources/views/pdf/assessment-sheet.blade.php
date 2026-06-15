<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Lembar Penilaian PKL' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111827; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        h2 { font-size: 13px; margin: 18px 0 8px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
        .meta td:first-child { width: 180px; font-weight: bold; background: #f9fafb; }
        .right { text-align: right; }
        .muted { color: #6b7280; }
    </style>
</head>
<body>
    <h1>{{ $title ?? 'Lembar Penilaian PKL' }}</h1>
    <p class="muted">
        Status submission:
        {{ ($submission_status ?? 'draft') === 'submitted' ? 'Final / submitted' : 'Draft / belum final' }}
    </p>

    <h2>Identitas Mahasiswa</h2>
    <table class="meta">
        <tr><td>Nama</td><td>{{ $student['name'] ?? '-' }}</td></tr>
        <tr><td>NIM</td><td>{{ $student['nim'] ?? '-' }}</td></tr>
        <tr><td>Program Studi</td><td>{{ $student['program_studi'] ?? '-' }}</td></tr>
        <tr><td>Perusahaan</td><td>{{ $company_name ?? '-' }}</td></tr>
        <tr><td>Template</td><td>{{ $template['name'] ?? '-' }}</td></tr>
        <tr><td>Role Penilai</td><td>{{ $template['assessor_role'] ?? '-' }}</td></tr>
        <tr><td>Periode Template</td><td>{{ $template['periode_label'] ?? '-' }}</td></tr>
        <tr><td>Waktu Submit</td><td>{{ $submitted_at ?? '-' }}</td></tr>
    </table>

    <h2>Komponen Penilaian</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Komponen</th>
                <th class="right">Bobot (%)</th>
                <th class="right">Nilai</th>
                <th class="right">Nilai Bobot</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['number'] ?? '-' }}</td>
                <td>{{ $row['component'] ?? '-' }}</td>
                <td class="right">{{ $row['weight_percentage'] ?? '-' }}</td>
                <td class="right">{{ $row['score'] ?? '-' }}</td>
                <td class="right">{{ $row['weighted_score'] ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">Belum ada data penilaian.</td>
            </tr>
        @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2">Total</th>
                <th class="right">{{ $total_weight ?? '0' }}</th>
                <th class="right"></th>
                <th class="right">{{ $total_score ?? '0.00' }}</th>
            </tr>
        </tfoot>
    </table>

    <h2>Pengesahan</h2>
    <table class="meta">
        <tr><td>{{ $signer_label ?? 'Penilai' }}</td><td>{{ $signer_name ?? '-' }}</td></tr>
        <tr><td>Tahun Dokumen</td><td>{{ $year ?? '-' }}</td></tr>
    </table>
</body>
</html>
