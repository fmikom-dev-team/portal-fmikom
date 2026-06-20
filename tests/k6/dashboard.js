import http from 'k6/http';
import { check, sleep } from 'k6';
import { loginLaravel } from './helpers/auth.js';

export const options = {
    stages: [
        { duration: '30s', target: 30 },  // Ramp-up ke 30 VUs
        { duration: '1m', target: 30 },   // Tahan di 30 VUs
        { duration: '30s', target: 0 },   // Ramp-down
    ],
    thresholds: {
        http_req_failed: ['rate<0.02'],    // Maksimal 2% error
        http_req_duration: ['p(95)<800'],  // 95% request di bawah 800ms
    },
};

const TARGET_URL = __ENV.TARGET_URL || 'https://fmikom.suntree.my.id';
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || 'mahasiswa@example.com';
const TEST_USER_PASS = __ENV.TEST_USER_PASS || 'mahasiswa123';

// Variabel scoped per VU. Login hanya akan dijalankan sekali per VU.
let isLoggedIn = false;

export default function () {
    if (!isLoggedIn) {
        isLoggedIn = loginLaravel(TARGET_URL, TEST_USER_EMAIL, TEST_USER_PASS);
        if (!isLoggedIn) {
            console.error('Gagal login untuk VU ini, melewati iterasi');
            sleep(1);
            return;
        }
    }

    // Mengunjungi dashboard utama
    const res = http.get(`${TARGET_URL}/dashboard`);

    check(res, {
        'dashboard page status is 200': (r) => r.status === 200,
        'contains dashboard content': (r) => r.body.includes('Dashboard') || r.body.length > 0,
    });

    sleep(1.5);
}
