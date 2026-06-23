<x-mail::message>
# Hai {{ $userName }}! 👋

Berikut ringkasan minggu ini dari Portal FMIKOM:

@if($newJobs->count() > 0)
## 💼 {{ $newJobs->count() }} Lowongan Baru

<x-mail::table>
| Posisi | Perusahaan | Deadline |
|:-------|:-----------|:---------|
@foreach($newJobs as $job)
| {{ $job->title }} | {{ $job->mitra?->nama_perusahaan ?? 'FMIKOM' }} | {{ $job->deadline ? \Carbon\Carbon::parse($job->deadline)->format('d M Y') : '-' }} |
@endforeach
</x-mail::table>

<x-mail::button :url="config('app.url') . '/trace/jobs'">
Lihat Semua Lowongan
</x-mail::button>
@else
Tidak ada lowongan baru minggu ini.
@endif

@if($newEvents->count() > 0)
## 📅 {{ $newEvents->count() }} Event Baru

@foreach($newEvents as $event)
- **{{ $event->title }}** — {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }} di {{ $event->location }}
@endforeach

<x-mail::button :url="config('app.url') . '/trace/events'">
Lihat Semua Event
</x-mail::button>
@endif

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
