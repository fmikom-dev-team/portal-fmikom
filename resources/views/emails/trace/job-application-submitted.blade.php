<x-mail::message>
# Halo Mitra {{ $companyName }},

Kami menginformasikan bahwa ada pelamar baru yang mengirimkan lamaran untuk lowongan pekerjaan Anda di Portal FMIKOM.

👤 **Nama Pelamar**: {{ $alumniName }}<br>
💼 **Posisi Lowongan**: {{ $jobTitle }}

Silakan tinjau berkas lamaran, CV, dan portfolio pelamar tersebut melalui dashboard rekrutmen Anda di portal melalui tombol di bawah ini:

<x-mail::button :url="config('app.url') . '/trace/open-job/jobs-listings/' . $jobId">
Tinjau Pelamar
</x-mail::button>

Terima kasih atas kerja sama Anda.

Salam hangat,<br>
**Tim Career Center FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
