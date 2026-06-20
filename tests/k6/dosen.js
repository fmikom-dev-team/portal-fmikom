import { check, sleep } from 'k6';
import http from 'k6/http';
import { loginLaravel } from './helpers/auth.js';

export const options = {
    stages: [
        { duration: '30s', target: 20 },
        { duration: '1m', target: 20 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.02'],
        http_req_duration: ['p(95)<1000'],
    },
};

const TARGET_URL = __ENV.TARGET_URL || 'https://fmikom.suntree.my.id';
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || 'mahasiswa@example.com';
const TEST_USER_PASS = __ENV.TEST_USER_PASS || 'mahasiswa123';

let isLoggedIn = false;

export default function () {
    if (!isLoggedIn) {
        isLoggedIn = loginLaravel(TARGET_URL, TEST_USER_EMAIL, TEST_USER_PASS);
        if (!isLoggedIn) {
            console.error('Gagal login untuk VU ini');
            sleep(1);
            return;
        }
    }

    // Menguji endpoint pencarian/kontak dosen yang sering dipanggil dari halaman chat/pesan
    const res = http.get(`${TARGET_URL}/pagi/messages/contacts?type=dosen`);

    check(res, {
        'dosen contacts status is 200': (r) => r.status === 200,
    });

    sleep(2);
}
