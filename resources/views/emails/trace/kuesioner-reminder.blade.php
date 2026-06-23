<x-mail::message>
# Yth. {{ $userName }},

Dalam rangka peningkatan mutu akademik dan akreditasi program studi di Fakultas Ilmu Komputer (FMIKOM), partisipasi alumni dalam pengisian data penelusuran lulusan sangatlah krusial.

Kami mendapati bahwa Anda belum melengkapi instrumen kuesioner berikut:

**{{ $kuesioner->judul }}**

@if($kuesioner->date_selesai)
⏰ **Batas Waktu Pengisian**: {{ \Carbon\Carbon::parse($kuesioner->date_selesai)->format('d M Y') }}
@endif

Setiap jawaban dan data yang Anda berikan akan diperlakukan secara rahasia dan hanya digunakan untuk kepentingan evaluasi kurikulum serta pelaporan resmi institusi. Mohon luangkan waktu 5–10 menit untuk melengkapinya melalui tombol di bawah ini:

<x-mail::button :url="config('app.url') . '/trace/kuesioner/' . $kuesioner->id">
Isi Kuesioner Sekarang
</x-mail::button>

Terima kasih banyak atas waktu, dedikasi, dan kontribusi nyata Anda untuk almamater tercinta.

Salam hormat,<br>
**Tim Tracer Study FMIKOM**<br>
{{ config('app.name') }}
</x-mail::message>
