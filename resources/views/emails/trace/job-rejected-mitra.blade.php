<x-mail::message>
# Halo Mitra {{ $companyName }},

Kami menginformasikan bahwa lowongan pekerjaan yang Anda ajukan di Portal FMIKOM memerlukan perbaikan dan saat ini belum dapat disetujui.

💼 **Posisi**: {{ $jobTitle }}

@if($reason)
💬 **Alasan Penolakan/Perbaikan**:
> {{ $reason }}
@endif

Silakan lakukan perbaikan data atau kelengkapan lowongan pekerjaan sesuai catatan di atas, kemudian ajukan kembali untuk dilakukan peninjauan ulang oleh Admin melalui tautan di bawah ini:

<x-mail::button :url="config('app.url') . '/trace/open-job/jobs-listings/' . $jobId">
Edit & Ajukan Kembali
</x-mail::button>

Terima kasih atas perhatian dan kerja sama Anda.

Salam hormat,<br>
**Tim Career Center FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
