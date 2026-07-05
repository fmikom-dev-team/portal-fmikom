<x-mail::message>
# Halo {{ $userName }},

Terima kasih telah melamar lowongan pekerjaan melalui Portal FMIKOM.

Kami mengonfirmasi bahwa lamaran Anda untuk posisi berikut telah berhasil dikirimkan:

💼 **Posisi**: {{ $jobTitle }}<br>
🏢 **Perusahaan**: {{ $companyName }}

### Apa Langkah Selanjutnya?
1. **Ditinjau oleh Rekrut**: Lamaran Anda saat ini telah masuk ke dashboard perusahaan {{ $companyName }} untuk ditinjau.
2. **Pantau Email & Portal**: Mohon pantau kotak masuk email Anda (termasuk folder spam) serta bel notifikasi di Portal FMIKOM secara berkala untuk menerima pembaruan status seleksi atau undangan wawancara dari pihak perusahaan.

Semoga sukses dalam proses seleksi ini! Jika ada pertanyaan lebih lanjut, silakan hubungi tim Career Center FMIKOM.

Salam hangat,<br>
**Tim Career Center FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
