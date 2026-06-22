import { check, sleep } from "k6";
import http from "k6/http";
import { loginLaravel } from "./helpers/auth.js";

export const options = {
	stages: [
		{ duration: "30s", target: 20 }, // Ramp-up ke 20 VUs
		{ duration: "1m", target: 20 }, // Tahan di 20 VUs
		{ duration: "30s", target: 0 }, // Ramp-down
	],
	thresholds: {
		http_req_failed: ["rate<0.05"], // Maksimal 5% error (karena rate-limiting login biasanya ketat)
		http_req_duration: ["p(95)<1500"], // Respon di bawah 1.5 detik (karena proses hashing password cukup memakan CPU)
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || "mahasiswa@example.com";
const TEST_USER_PASS = __ENV.TEST_USER_PASS || "mahasiswa123";

export default function () {
	// Jalankan helper login
	const success = loginLaravel(TARGET_URL, TEST_USER_EMAIL, TEST_USER_PASS);

	check(success, {
		"user logged in successfully": (val) => val === true,
	});

	sleep(2);
}
