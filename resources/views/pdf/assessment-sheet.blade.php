<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Penilaian Mahasiswa' }}</title>
    <style>
        body {
            font-family: DejaVu Serif, serif;
            color: #111;
            font-size: 13px;
            line-height: 1.45;
            margin: 28px 34px 22px;
        }

        .title {
            margin: 0 0 46px;
            text-align: center;
            font-size: 18px;
            font-weight: 700;
        }

        .identity-table,
        .score-table {
            width: 100%;
            border-collapse: collapse;
        }

        .identity-table {
            margin-bottom: 28px;
        }

        .identity-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .identity-label {
            width: 150px;
        }

        .identity-colon {
            width: 12px;
            text-align: center;
        }

        .identity-value {
            border-bottom: 1px dotted #222;
            min-height: 18px;
        }

        .score-table th,
        .score-table td {
            border: 1px solid #222;
            padding: 8px 10px;
            vertical-align: top;
        }

        .score-table th {
            text-align: center;
            font-weight: 700;
        }

        .center {
            text-align: center;
        }

        .bold {
            font-weight: 700;
        }

        .signature {
            margin-top: 54px;
            width: 100%;
        }

        .signature-box {
            width: 290px;
            margin-left: auto;
            text-align: center;
        }

        .signature-date {
            margin-bottom: 12px;
        }

        .signature-space {
            height: 68px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <p class="title">{{ $title ?? 'Penilaian Mahasiswa' }}</p>

    <table class="identity-table">
        <tr>
            <td class="identity-label">Nama Mahasiswa</td>
            <td class="identity-colon">:</td>
            <td class="identity-value">{{ $student['name'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="identity-label">NIM</td>
            <td class="identity-colon">:</td>
            <td class="identity-value">{{ $student['nim'] ?? '-' }}</td>
        </tr>
        <tr>
            <td class="identity-label">Program Studi</td>
            <td class="identity-colon">:</td>
            <td class="identity-value">{{ $student['program_studi'] ?? '-' }}</td>
        </tr>
    </table>

    <table class="score-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th>Komponen</th>
                <th style="width: 22%;">Persentase %</th>
                <th style="width: 20%;">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    <td class="center">{{ $row['number'] }}</td>
                    <td>{{ $row['component'] }}</td>
                    <td class="center">{{ rtrim(rtrim(number_format((float) $row['weight_percentage'], 2, '.', ''), '0'), '.') }} %</td>
                    <td class="center">{{ $row['score'] }}</td>
                </tr>
            @endforeach
            <tr>
                <td>&nbsp;</td>
                <td class="bold">TOTAL NILAI</td>
                <td class="center bold">{{ rtrim(rtrim(number_format((float) $total_weight, 2, '.', ''), '0'), '.') }} %</td>
                <td class="center bold">{{ $total_score ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="signature">
        <div class="signature-box">
            <div class="signature-date">............................., {{ $year ?? now()->format('Y') }}</div>
            <div>{{ $signer_label ?? 'Pembimbing Lapangan' }}</div>
            <div class="signature-space"></div>
            <div class="signature-name">{{ $signer_name ?? '-' }}</div>
        </div>
    </div>
</body>
</html>
