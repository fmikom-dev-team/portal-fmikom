<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Verifikasi Akses OAuth FMIKOM Portal</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8fafc; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 32px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0;">

        <div style="text-align: center; margin-bottom: 24px;">
            <div style="display: inline-block; width: 48px; height: 48px; background-color: #eff6ff; border-radius: 50%; line-height: 48px; font-size: 24px;">
                🛡️
            </div>
        </div>

        <h2 style="color: #0f172a; text-align: center; font-size: 22px; font-weight: 700; margin-bottom: 8px;">Akses Akun Disetujui!</h2>
        <p style="color: #2563eb; text-align: center; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0; margin-bottom: 24px;">
            Pendaftaran via Akun {{ $providerName }}
        </p>

        <p style="color: #475569; font-size: 15px; line-height: 1.6;">
            Halo <strong>{{ $userName }}</strong>,<br><br>
            Kabar baik! Permohonan pendaftaran akun Anda di <strong>Portal FMIKOM</strong> telah berhasil diverifikasi dan disetujui oleh administrator.
        </p>

        <p style="color: #475569; font-size: 15px; line-height: 1.6;">
            Karena Anda mendaftar menggunakan akun <strong>{{ $providerName }}</strong>, Anda tidak perlu membuat password baru. Cukup klik tombol di bawah ini untuk melewati <strong>Smart Access Control Verification</strong> dan langsung masuk ke sistem.
        </p>

        <div style="text-align: center; margin: 32px 0;">
            <a href="{!! $activationUrl !!}"
               style="background-color: #2563eb; color: #ffffff; text-decoration: none; font-size: 15px; font-weight: 700; padding: 14px 36px; border-radius: 12px; display: inline-block; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                Verifikasi & Aktifkan Akses Sekarang →
            </a>
        </div>

        <p style="color: #64748b; font-size: 13px; text-align: center;">
            Jika tombol di atas tidak dapat diklik, salin dan buka tautan berikut pada peramban Anda:<br>
            <a href="{!! $activationUrl !!}" style="color: #2563eb; word-break: break-all; font-weight: 500;">{!! $activationUrl !!}</a>
        </p>

        <div style="background-color: #f1f5f9; padding: 12px 16px; border-radius: 8px; margin-top: 24px;">
            <p style="color: #64748b; font-size: 12px; text-align: center; margin: 0;">
                ⚠️ Tautan verifikasi ini berlaku selama <strong>24 jam</strong> demi keamanan akun Anda.
            </p>
        </div>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 28px 0 20px 0;">
        <p style="color: #94a3b8; font-size: 12px; text-align: center; margin: 0;">
            Email ini dikirimkan secara otomatis oleh Sistem Portal FMIKOM.<br>
            Jika Anda tidak merasa melakukan pendaftaran ini, harap abaikan email ini.
        </p>
    </div>
</body>
</html>
