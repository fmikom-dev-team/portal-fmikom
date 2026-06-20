import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 50 },  // Ramp-up ke 50 VUs (karena halaman publik, bisa menampung lebih banyak load)
        { duration: '2m', target: 50 },   // Tahan di 50 VUs
        { duration: '30s', target: 0 },   // Ramp-down
    ],
    thresholds: {
        http_req_failed: ['rate<0.01'],   // Maksimal 1% error
        http_req_duration: ['p(95)<400'],  // 95% respon harus di bawah 400ms karena menggunakan cache
    },
};

const TARGET_URL = __ENV.TARGET_URL || 'https://fmikom.suntree.my.id';

export default function () {
    // 1. Mengunjungi daftar berita
    const listRes = http.get(`${TARGET_URL}/berita`);
    
    check(listRes, {
        'berita index status is 200': (r) => r.status === 200,
    });

    // 2. Ekstrak tautan artikel berita secara dinamis dari halaman daftar berita
    const doc = listRes.html();
    const links = [];
    doc.find('a').each((idx, el) => {
        const href = el.getAttribute('href');
        if (href && href.includes('/berita/') && href !== '/berita') {
            links.push(href);
        }
    });

    // 3. Jika ada artikel berita yang ditemukan, kunjungi salah satunya secara acak
    if (links.length > 0) {
        const randomLink = links[Math.floor(Math.random() * links.length)];
        // Pastikan URL penuh terbentuk dengan benar
        const articleUrl = randomLink.startsWith('http') ? randomLink : `${TARGET_URL}${randomLink}`;
        
        const articleRes = http.get(articleUrl);
        check(articleRes, {
            'berita article status is 200': (r) => r.status === 200,
        });
    } else {
        // Fallback jika database berita kosong
        const fallbackRes = http.get(`${TARGET_URL}/berita/pengumuman-perkuliahan`);
        check(fallbackRes, {
            'fallback article status is 200 or 404': (r) => r.status === 200 || r.status === 404,
        });
    }

    sleep(2);
}
