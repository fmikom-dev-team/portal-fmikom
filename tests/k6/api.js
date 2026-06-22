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
		http_req_duration: ["p(95)<800"],
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || "muchlisinmaruf@gmail.com"; // Butuh hak admin untuk mengakses stats admin
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

	const jar = http.cookieJar();
	const cookies = jar.cookiesForURL(TARGET_URL);
	const xsrfToken = cookies["XSRF-TOKEN"]
		? decodeURIComponent(cookies["XSRF-TOKEN"][0])
		: "";

	const params = {
		headers: {
			Accept: "application/json",
			"X-XSRF-TOKEN": xsrfToken,
			"X-Requested-With": "XMLHttpRequest",
		},
	};

	// 1. Menguji API stats admin (real-time data)
	const statsRes = http.get(`${TARGET_URL}/pagi/admin/api/stats`, params);
	check(statsRes, {
		"api stats status is 200": (r) => r.status === 200,
		"api stats response is json": (r) => r.json() !== null,
	});

	sleep(1);

	// 2. Menguji API chart admin (data visualisasi)
	const chartRes = http.get(`${TARGET_URL}/pagi/admin/api/chart`, params);
	check(chartRes, {
		"api chart status is 200": (r) => r.status === 200,
		"api chart response is json": (r) => r.json() !== null,
	});

	sleep(2);
}
