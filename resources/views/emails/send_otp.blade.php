<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kode OTP FMIKOM</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
        
        <h2 style="color: #1E293B; text-align: center;">Verifikasi Keamanan Akun</h2>
        <p style="color: #64748B; font-size: 16px;">
            Halo <strong>{{ $userName }}</strong>,<br><br>
            Sistem Portal FMIKOM mendeteksi Anda mencoba masuk untuk pertama kalinya. Untuk menjaga keamanan akun dan menghindari peretasan, silakan gunakan kode OTP berikut di layar portal:
        </p>
        
        <div style="text-align: center; margin: 30px 0;">
            <span style="background-color: #F8FAFC; color: #2563eb; font-size: 32px; font-weight: bold; padding: 15px 30px; border-radius: 12px; letter-spacing: 5px; border: 1px solid #E2E8F0;">
                {{ $otpCode }}
            </span>
        </div>
        
        <p style="color: #DC2626; font-size: 14px; text-align: center;">
            ⚠️ Kode ini akan kedaluwarsa dalam 15 menit. Jangan bagikan kode ini kepada siapa pun!
        </p>
        
        <hr style="border: 0; border-top: 1px solid #E2E8F0; margin: 30px 0;">
        <p style="color: #94A3B8; font-size: 12px; text-align: center;">
            Email ini dikirim otomatis oleh Sistem Portal FMIKOM.<br>
            Jika Anda merasa tidak mencoba untuk login, abaikan pesan ini.
        </p>
    </div>
</body>
</html>
