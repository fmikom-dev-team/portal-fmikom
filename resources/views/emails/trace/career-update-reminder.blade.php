```blade
<x-mail::message>
# Yth. {{ $userName }},

Semoga Anda senantiasa diberikan kesehatan, kesuksesan, dan kelancaran dalam menjalankan aktivitas.

Dalam rangka mendukung pelaksanaan **Tracer Study** serta pembaruan data alumni Fakultas Ilmu Komputer (FMIKOM), kami mengundang Anda untuk memperbarui informasi mengenai riwayat pekerjaan atau status karir terkini.

Berdasarkan data yang kami miliki, riwayat karir Anda **belum diperbarui selama lebih dari 1 tahun**. Oleh karena itu, kami memohon kesediaan Anda untuk meluangkan waktu sekitar **1–2 menit** guna memperbarui atau mengonfirmasi data tersebut melalui portal Tracer Study.

Informasi yang Anda berikan, seperti status pekerjaan saat ini, kesesuaian bidang kerja, kegiatan wirausaha, maupun studi lanjut, memiliki peran penting dalam:

- Mendukung evaluasi dan pengembangan kurikulum.
- Menjadi bagian dari proses akreditasi program studi.
- Meningkatkan kualitas layanan dan pengembangan pendidikan bagi mahasiswa serta alumni.

Silakan klik tombol di bawah ini untuk memperbarui data Anda.

<x-mail::button :url="config('app.url') . '/trace/career'">
Perbarui Riwayat Karir
</x-mail::button>

Apabila tidak terdapat perubahan pada status karir Anda, cukup masuk ke halaman tersebut dan lakukan konfirmasi atau simpan kembali data yang tersedia agar informasi Anda tetap tercatat sebagai data terbaru.

Atas waktu, perhatian, dan partisipasi Anda, kami mengucapkan terima kasih. Kontribusi Anda sangat berarti dalam mendukung kemajuan Fakultas Ilmu Komputer dan peningkatan mutu pendidikan bagi generasi berikutnya.

Hormat kami,

**Tim Tracer Study FMIKOM**  
{{ config('app.name') }}
</x-mail::message>
```
