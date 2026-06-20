import { check, sleep } from 'k6';
import http from 'k6/http';

// 4. Opsi Konfigurasi Endurance (Soak) Test
export const options = {
    stages: [
        { duration: '2m', target: 50 },  // Ramp-up dari 0 ke 50 pengguna dalam 2 menit
        { duration: '30m', target: 50 }, // Durasi panjang stabil di 50 pengguna (bisa disesuaikan ke 2 jam - 8 jam jika diperlukan)
        { duration: '2m', target: 0 },   // Ramp-down kembali ke 0 pengguna dalam 2 menit
    ],
    thresholds: {
        http_req_failed: ['rate<0.01'],   // Harus sangat stabil, error rate < 1%
        http_req_duration: ['p(95)<500'], // Respon tetap di bawah 500ms
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
