<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Magic Link Login FMIKOM Portal</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">

        <h2 style="color: #1E293B; text-align: center;">🔗 Link Login Portal FMIKOM</h2>
        <p style="color: #64748B; font-size: 16px;">
            Halo <strong>{{ $userName }}</strong>,<br><br>
            Anda meminta link login untuk akun Portal FMIKOM Anda. Klik tombol di bawah untuk masuk secara langsung tanpa password.
        </p>
        <p style="color: #64748B; font-size: 16px;">
            Link ini hanya berlaku selama <strong>15 menit</strong> dan hanya bisa digunakan <strong>satu kali</strong>.
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $magicUrl }}"
               style="background-color: #7c3aed; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: bold; padding: 14px 32px; border-radius: 8px; display: inline-block;">
                Login ke Portal FMIKOM
            </a>
        </div>

        <p style="color: #94A3B8; font-size: 13px; text-align: center;">
            Jika tombol di atas tidak berfungsi, salin dan tempel URL berikut ke browser Anda:<br>
            <a href="{{ $magicUrl }}" style="color: #7c3aed; word-break: break-all;">{{ $magicUrl }}</a>
        </p>

        <p style="color: #DC2626; font-size: 14px; text-align: center;">
            ⚠️ Jangan bagikan link ini kepada siapa pun! Link ini hanya bisa digunakan sekali.
        </p>

        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 30px 0;">
        <p style="color: #94A3B8; font-size: 12px; text-align: center;">
            Email ini dikirim karena ada permintaan magic link dari Portal FMIKOM.<br>
            Jika Anda tidak meminta ini, abaikan email ini. Akun Anda tetap aman.
        </p>
    </div>
</body>
</html>
