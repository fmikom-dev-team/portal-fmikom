import { check, sleep } from "k6";
import http from "k6/http";

// 5. Opsi Konfigurasi High Load Test (10,000 VUs)
export const options = {
	stages: [
		{ duration: "3m", target: 2000 }, // Ramp-up bertahap ke 2.000 VUs
		{ duration: "3m", target: 5000 }, // Ramp-up lagi ke 5.000 VUs
		{ duration: "4m", target: 10000 }, // Naik ke puncak beban 10.000 VUs
		{ duration: "5m", target: 10000 }, // Menahan beban puncak 10.000 VUs selama 5 menit
		{ duration: "3m", target: 2000 }, // Turun bertahap ke 2.000 VUs (recovery)
		{ duration: "2m", target: 0 }, // Ramp-down penuh ke 0
	],
	thresholds: {
		http_req_failed: ["rate<0.05"], // Maksimal toleransi kegagalan 5% saat beban puncak
		http_req_duration: ["p(95)<2000"], // 95% request harus selesai di bawah 2 detik
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";

export default function () {
	const res = http.get(TARGET_URL);

	check(res, {
		"status is 200": (r) => r.status === 200,
	});

	// Menggunakan sleep minimal 1.5 detik untuk mengurangi kemacetan jaringan lokal penguji
	sleep(1.5);
}
