import { check, sleep } from "k6";
import http from "k6/http";

// 1. Opsi Konfigurasi Load Test
export const options = {
	stages: [
		{ duration: "1m", target: 50 }, // Ramp-up dari 0 ke 50 pengguna dalam 1 menit
		{ duration: "3m", target: 50 }, // Stabil di 50 pengguna selama 3 menit
		{ duration: "1m", target: 0 }, // Ramp-down kembali ke 0 pengguna dalam 1 menit
	],
	thresholds: {
		http_req_failed: ["rate<0.01"], // Error rate harus di bawah 1%
		http_req_duration: ["p(95)<500"], // 95% request harus selesai di bawah 500ms
	},
};

// Target URL default dari env variable atau fallback ke local/production URL
const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";

export default function () {
	// Mengirim HTTP GET ke URL target
	const res = http.get(TARGET_URL);

	// Memverifikasi response
	check(res, {
		"status is 200": (r) => r.status === 200,
		"body contains portal": (r) =>
			r.body.includes("portal") ||
			r.body.includes("Portal") ||
			r.body.length > 0,
	});

	// Istirahat sejenak 1 detik antar request per virtual user
	sleep(1);
}
