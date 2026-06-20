<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Verifikasi QR Code Surat</title>
</head>
<body>
    <h1>Hasil Verifikasi QR Code Surat</h1>
    @if ($found)
        <p><strong>Dokumen ASLI & VALID</strong></p>
        <ul>
            <li>Nomor Surat: {{ $surat->nomor_surat }}</li>
            <li>Jenis Surat: {{ $surat->jenisSurat->nama ?? '-' }}</li>
            <li>Keperluan: {{ $surat->keperluan }}</li>
            <li>Status: {{ $surat->status }}</li>
            <li>Tanggal Pengajuan: {{ $surat->tanggal_pengajuan }}</li>
        </ul>
    @else
        <p style="color:red"><strong>Dokumen TIDAK DITEMUKAN atau TIDAK VALID</strong></p>
        <p>Token: {{ $token }}</p>
    @endif
    <a href="{{ route('qr.verify.form') }}">Kembali ke Form Verifikasi</a>
</body>
</html>
