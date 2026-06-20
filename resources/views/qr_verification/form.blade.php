<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi QR Code Surat</title>
</head>
<body>
    <h1>Verifikasi QR Code Surat</h1>
    <form action="{{ route('qr.verify') }}" method="get">
        <label for="token">Masukkan Token QR Code:</label>
        <input type="text" id="token" name="token" required>
        <button type="submit">Verifikasi</button>
    </form>
    <p>Atau scan QR code pada dokumen surat Anda.</p>
</body>
</html>
