import http from 'k6/http';
import { check, sleep } from 'k6';
import { loginLaravel } from './helpers/auth.js';

export const options = {
    stages: [
        { duration: '30s', target: 5 },  // Pertahankan jumlah VU tetap rendah untuk tes upload agar tidak membebani jaringan lokal secara berlebihan
        { duration: '1m', target: 5 },
        { duration: '30s', target: 0 },
    ],
    thresholds: {
        http_req_failed: ['rate<0.05'],
        http_req_duration: ['p(95)<3000'], // Tes upload biasanya membutuhkan waktu lebih lama (toleransi 3 detik)
    },
};

const TARGET_URL = __ENV.TARGET_URL || 'https://fmikom.suntree.my.id';
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || 'muchlisinmaruf@gmail.com'; // Butuh hak admin untuk upload image artikel
const TEST_USER_PASS = __ENV.TEST_USER_PASS || 'admin123';

// File dummy 1x1 pixel gif base64
const binFile = JSON.parse(JSON.stringify(http.file('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7', 'test.gif', 'image/gif')));

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

    const payload = {
        upload: binFile, // Sesuai parameter editor image upload Laravel
    };

    const params = {
        headers: {
            'X-XSRF-TOKEN': xsrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
    };

    // Melakukan POST upload file ke rute portal-admin
    const res = http.post(`${TARGET_URL}/portal-admin/posts/upload-image`, payload, params);

    check(res, {
        'upload image status is 200 or 201': (r) => r.status === 200 || r.status === 201,
    });

    sleep(3);
}
