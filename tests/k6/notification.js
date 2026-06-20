import http from 'k6/http';
import { check, sleep } from 'k6';
import { loginLaravel } from './helpers/auth.js';

export const options = {
    stages: [
        { duration: '30s', target: 20 },
        { duration: '1m', target: 20 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.02'],
        http_req_duration: ['p(95)<600'],
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

    const jar = http.cookieJar();
    const cookies = jar.cookiesForURL(TARGET_URL);
    const xsrfToken = cookies['XSRF-TOKEN'] ? decodeURIComponent(cookies['XSRF-TOKEN'][0]) : '';

    const params = {
        headers: {
            'X-XSRF-TOKEN': xsrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    // 1. Menguji loading halaman notifikasi
    const getRes = http.get(`${TARGET_URL}/pagi/notifications`, params);
    check(getRes, {
        'get notifications status is 200': (r) => r.status === 200,
    });

    sleep(1);

    // 2. Menguji aksi mark all read (POST)
    const postRes = http.post(`${TARGET_URL}/pagi/notifications/mark-all-read`, {}, params);
    check(postRes, {
        'mark all read status is 200 or 302': (r) => r.status === 200 || r.status === 302,
    });

    sleep(2);
}
