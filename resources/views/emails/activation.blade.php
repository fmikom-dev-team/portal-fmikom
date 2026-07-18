<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Aktivasi Akun FMIKOM Portal</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">

        <h2 style="color: #1E293B; text-align: center;">🎉 Pendaftaran Anda Disetujui!</h2>
        <p style="color: #64748B; font-size: 16px;">
            Halo <strong>{{ $userName }}</strong>,<br><br>
            Kabar baik! Pendaftaran akun Anda di <strong>Portal FMIKOM</strong> telah disetujui oleh administrator.
        </p>

        <p style="color: #64748B; font-size: 16px;">
            Klik tombol di bawah ini untuk mengaktifkan akun Anda dan membuat password. Link ini hanya berlaku selama <strong>24 jam</strong>.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $activationUrl }}"
               style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; padding: 14px 32px; border-radius: 8px; display: inline-block;">
                Aktifkan Akun Saya
            </a>
        </div>

        <p style="color: #94A3B8; font-size: 13px; text-align: center;">
            Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:<br>
            <a href="{{ $activationUrl }}" style="color: #2563eb; word-break: break-all;">{{ $activationUrl }}</a>
        </p>

        <p style="color: #DC2626; font-size: 14px; text-align: center;">
            ⚠️ Link ini akan kedaluwarsa dalam 24 jam. Jangan bagikan link ini kepada siapa pun!
        </p>

        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 30px 0;">
        <p style="color: #94A3B8; font-size: 12px; text-align: center;">
            Email ini dikirim otomatis oleh Sistem Portal FMIKOM.<br>
            Jika Anda merasa tidak mendaftar, abaikan pesan ini atau hubungi administrator.
        </p>
    </div>
</body>
</html>
