<x-mail::message>
# Hai {{ $userName }}! 📝

Kami mengingatkan bahwa kuesioner berikut belum kamu isi:

**{{ $kuesioner->judul }}**

@if($kuesioner->date_selesai)
⏰ Batas waktu: **{{ \Carbon\Carbon::parse($kuesioner->date_selesai)->format('d M Y') }}**
@endif

Partisipasimu sangat berarti untuk peningkatan kualitas pendidikan.

<x-mail::button :url="config('app.url') . '/trace/kuesioner/' . $kuesioner->id">
Isi Kuesioner Sekarang
</x-mail::button>

Salam,<br>
{{ config('app.name') }}
</x-mail::message>
