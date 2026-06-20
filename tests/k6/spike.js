import http from 'k6/http';
import { check, sleep } from 'k6';

// 3. Opsi Konfigurasi Spike Test
export const options = {
    stages: [
        { duration: '30s', target: 0 },   // Start dari 0
        { duration: '1m', target: 500 },  // Melonjak tajam (spike) ke 500 pengguna dalam 1 menit
        { duration: '2m', target: 500 },  // Menahan beban 500 pengguna selama 2 menit
        { duration: '1m', target: 0 },   // Turun drastis ke 0 pengguna dalam 1 menit
    ],
    // Spike test biasanya melonggarkan thresholds karena server dipaksa untuk melampaui limitnya secara mendadak
    thresholds: {
        http_req_failed: ['rate<0.10'],    // Maksimal error rate 10% saat spike terjadi
        http_req_duration: ['p(95)<3000'], // Respon di bawah 3 detik
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
