<x-mail::message>
# Halo Mitra {{ $companyName }},

Kabar baik! Lowongan pekerjaan yang Anda ajukan di Portal FMIKOM telah disetujui oleh Admin FMIKOM.

💼 **Posisi**: {{ $jobTitle }}

Lowongan Anda saat ini telah aktif dan dapat dilamar oleh para alumni FMIKOM melalui portal Tracer Study. Anda dapat memantau pelamar yang masuk melalui tautan di bawah ini:

<x-mail::button :url="config('app.url') . '/trace/open-job/jobs-listings/' . $jobId">
Lihat Lowongan Saya
</x-mail::button>

Terima kasih atas kerja sama Anda dalam membuka peluang karir bagi para alumni FMIKOM.

Salam hormat,<br>
**Tim Career Center FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
