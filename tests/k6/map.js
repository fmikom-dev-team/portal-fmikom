import { check, sleep } from "k6";
import http from "k6/http";
import { loginLaravel } from "./helpers/auth.js";

export const options = {
	stages: [
		{ duration: "30s", target: 15 },
		{ duration: "1m", target: 15 },
		{ duration: "30s", target: 0 },
	],
	thresholds: {
		http_req_failed: ["rate<0.02"],
		http_req_duration: ["p(95)<1000"],
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || "mahasiswa@example.com";
const TEST_USER_PASS = __ENV.TEST_USER_PASS || "mahasiswa123";

let isLoggedIn = false;

export default function () {
	if (!isLoggedIn) {
		isLoggedIn = loginLaravel(TARGET_URL, TEST_USER_EMAIL, TEST_USER_PASS);
		if (!isLoggedIn) {
			console.error("Gagal login untuk VU ini");
			sleep(1);
			return;
		}
	}

	// Ambil token CSRF dari cookie untuk AJAX POST request
	const jar = http.cookieJar();
	const cookies = jar.cookiesForURL(TARGET_URL);
	const xsrfToken = cookies["XSRF-TOKEN"]
		? decodeURIComponent(cookies["XSRF-TOKEN"][0])
		: "";

	// Simulasi update lokasi mahasiswa (koordinat peta)
	const payload = JSON.stringify({
		latitude: -6.2088,
		longitude: 106.8456,
		address: "Jakarta, Indonesia",
		postal_code: "10110",
	});

	const params = {
		headers: {
			"Content-Type": "application/json",
			"X-XSRF-TOKEN": xsrfToken,
			"X-Requested-With": "XMLHttpRequest",
		},
	};

	// Mengirim POST request untuk memperbarui lokasi
	const res = http.post(`${TARGET_URL}/pagi/profile/update`, payload, params);

	check(res, {
		"location update status is 200 or 302": (r) =>
			r.status === 200 || r.status === 302,
	});

	sleep(2);
}
