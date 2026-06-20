import http from 'k6/http';
import { check, sleep } from 'k6';

// 2. Opsi Konfigurasi Stress Test
export const options = {
    stages: [
        { duration: '2m', target: 100 }, // Ramp-up dari 0 ke 100 pengguna
        { duration: '3m', target: 100 }, // Stabil di 100 pengguna
        { duration: '2m', target: 200 }, // Ramp-up lagi dari 100 ke 200 pengguna
        { duration: '3m', target: 200 }, // Stabil di 200 pengguna
        { duration: '2m', target: 400 }, // Ramp-up ke batas maksimum (400 pengguna) untuk mencari breaking point
        { duration: '3m', target: 400 }, // Stabil di 400 pengguna
        { duration: '2m', target: 0 },   // Ramp-down kembali ke 0 pengguna (recovery)
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'],    // Error rate toleransi di bawah 5%
        http_req_duration: ['p(95)<1500'], // 95% request harus selesai di bawah 1.5 detik
    },
};

const TARGET_URL = __ENV.TARGET_URL || 'https://fmikom.suntree.my.id';

export default function () {
    const res = http.get(TARGET_URL);

    check(res, {
        'status is 200': (r) => r.status === 200,
    });

    sleep(1);
}
