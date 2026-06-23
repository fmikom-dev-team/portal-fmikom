<x-mail::message>
# Hai {{ $userName }}! 📅

Event yang kamu daftarkan akan berlangsung **besok**:

**{{ $event->title }}**
- 📅 Tanggal: {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}
- 📍 Lokasi: {{ $event->location }}

<x-mail::button :url="config('app.url') . '/trace/events/' . $event->id">
Lihat Detail Event
</x-mail::button>

Jangan sampai terlewat ya!

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
