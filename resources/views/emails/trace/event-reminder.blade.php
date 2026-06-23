<x-mail::message>
# Yth. {{ $userName }},

Kami ingin menginformasikan dan mengingatkan kembali bahwa event FMIKOM yang telah Anda daftarkan akan diselenggarakan **besok**:

**{{ $event->title }}**

### Detail Acara:
* 📅 **Tanggal**: {{ \Carbon\Carbon::parse($event->event_date)->format('d M Y') }}
* 📍 **Lokasi / Tautan**: {{ $event->location }}

Mohon hadir tepat waktu. Anda dapat melihat informasi lengkap mengenai agenda acara dan petunjuk teknis pelaksanaan melalui tautan di bawah ini:

<x-mail::button :url="config('app.url') . '/trace/events/' . $event->id">
Lihat Detail Event
</x-mail::button>

Kami sangat menantikan kehadiran Anda.

Salam hangat,<br>
**Panitia Penyelenggara Event FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
