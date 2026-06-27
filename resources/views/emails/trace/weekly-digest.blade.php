<x-mail::message>
# Yth. {{ $userName }},

Berikut adalah rangkuman info mingguan mengenai peluang karir (lowongan pekerjaan) dan agenda event terbaru di lingkungan FMIKOM yang dapat Anda ikuti:

@if($newJobs->count() > 0)
##  Lowongan Pekerjaan Baru (Minggu Ini)

<x-mail::table>
| Posisi / Jabatan | Mitra Perusahaan | Batas Akhir Lamaran |
|:-----------------|:-----------------|:-------------------|
@foreach($newJobs as $job)
| **{{ $job->title }}** | {{ $job->mitra?->nama_perusahaan ?? 'FMIKOM' }} | {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('d M Y') : '-' }} |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.url') . '/trace/jobs'">
Jelajahi & Lamar Lowongan
</x-mail::button>
@else
*Tidak ada lowongan pekerjaan baru yang diterbitkan pada minggu ini.*
@endif

@if($newEvents->count() > 0)
## 📅 Event & Kegiatan Mendatang

Kami juga mengundang Anda untuk berpartisipasi dalam agenda kegiatan akademik maupun non-akademik terbaru kami:

@foreach($newEvents as $event)
* **{{ $event->title }}**  
  📅 Tanggal: {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }} | 📍 Lokasi: {{ $event->location }}
@endforeach

<x-mail::button :url="config('app.url') . '/trace/events'">
Lihat & Daftar Event
</x-mail::button>
@endif

Terima kasih atas perhatian Anda. Semoga rangkuman informasi ini bermanfaat untuk menunjang karir dan jejaring Anda.

Salam hangat,<br>
**Pusat Karir & Tracer Study FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
