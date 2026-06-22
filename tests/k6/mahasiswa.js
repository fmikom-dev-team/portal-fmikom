import { check, sleep } from "k6";
import http from "k6/http";
import { loginLaravel } from "./helpers/auth.js";

export const options = {
	stages: [
		{ duration: "30s", target: 20 },
		{ duration: "1m", target: 20 },
		{ duration: "30s", target: 0 },
	],
	thresholds: {
		http_req_failed: ["rate<0.02"],
		http_req_duration: ["p(95)<1000"],
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";
// Menggunakan user admin karena butuh akses halaman kelola user mahasiswa
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || "muchlisinmaruf@gmail.com";
const TEST_USER_PASS = __ENV.TEST_USER_PASS || "admin123";

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

	// 1. Menguji loading halaman index mahasiswa admin
	const listRes = http.get(`${TARGET_URL}/pagi/admin/users/mahasiswa`);
	check(listRes, {
		"mahasiswa page status is 200": (r) => r.status === 200,
	});

	sleep(1);

	// 2. Menguji API pencarian mahasiswa (biasanya dipicu secara real-time saat mengetik)
	const searchRes = http.get(`${TARGET_URL}/pagi/users/search?q=dummy`);
	check(searchRes, {
		"search api status is 200": (r) => r.status === 200,
	});

	sleep(2);
}
