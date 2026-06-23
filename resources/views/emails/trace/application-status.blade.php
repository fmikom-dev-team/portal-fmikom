<x-mail::message>
@if($status === 'accepted')
# Selamat, {{ $name }}! 🎉

Kami ingin mengabarkan bahwa lamaran Anda untuk posisi **{{ $jobTitle }}** di **{{ $companyName }}** telah dinyatakan: **Diterima (Accepted)**.

Pihak perusahaan/mitra akan segera menghubungi Anda untuk tahap koordinasi selanjutnya atau proses onboarding. Mohon pastikan nomor telepon dan email Anda dalam keadaan aktif.

<x-mail::button :url="$url">
Lihat Detail Lamaran
</x-mail::button>

Selamat atas pencapaian Anda, semoga ini menjadi awal perjalanan karir yang gemilang!

@elseif($status === 'rejected')
# Yth. {{ $name }},

Terima kasih atas partisipasi dan antusiasme Anda dalam melamar posisi **{{ $jobTitle }}** di **{{ $companyName }}** melalui Portal Tracer Study FMIKOM.

Kami ingin menginformasikan bahwa proses seleksi untuk posisi tersebut telah selesai. Setelah mempertimbangkan berbagai kualifikasi secara cermat, dengan berat hati kami sampaikan bahwa lamaran Anda **belum dapat dilanjutkan ke tahap berikutnya** pada kesempatan kali ini.

Kami sangat menghargai minat dan waktu yang telah Anda dedikasikan. Profil Anda akan tetap tersimpan di database kami untuk peluang karir lain yang sesuai di masa mendatang. Tetap semangat dan semoga sukses dalam pencarian karir Anda berikutnya.

<x-mail::button :url="$url">
Jelajahi Lowongan Lain
</x-mail::button>

@else
# Halo {{ $name }},

Kami ingin mengabarkan bahwa lamaran Anda untuk posisi **{{ $jobTitle }}** di **{{ $companyName }}** saat ini **sedang dalam tahap peninjauan (Reviewed)** oleh tim rekrutmen mitra kami.

Pihak perusahaan sedang mengevaluasi berkas dan kualifikasi Anda. Kami akan segera mengirimkan pemberitahuan kembali begitu ada pembaruan status lebih lanjut dari pihak mitra.

<x-mail::button :url="$url">
Pantau Status Lamaran
</x-mail::button>

Terima kasih atas kesabaran Anda.
@endif

Salam hangat,<br>
**Tim Tracer Study FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
