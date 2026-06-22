import { check } from "k6";
import http from "k6/http";

/**
 * Melakukan login ke aplikasi Laravel dengan mengekstrak CSRF token terlebih dahulu.
 * @param {string} baseUrl - URL dasar target (misal: http://localhost:8000 atau https://fmikom.suntree.my.id)
 * @param {string} email - Email user
 * @param {string} password - Password user
 * @returns {boolean} - true jika login sukses, false jika gagal
 */
export function loginLaravel(baseUrl, email, password) {
	// 1. Ambil halaman login untuk mendapatkan Cookie Session dan CSRF Token awal
	const loginPageRes = http.get(`${baseUrl}/login`);

	const isOk = check(loginPageRes, {
		"get login page status is 200": (r) => r.status === 200,
	});

	if (!isOk) {
		return false;
	}

	// 2. Ekstrak CSRF token dari form input html name="_token"
	const doc = loginPageRes.html();
	const csrfToken = doc.find('input[name="_token"]').attr("value");

	if (!csrfToken) {
		// Fallback jika token tidak ditemukan di input _token, coba cari di meta tag
		const metaCsrf = doc.find('meta[name="csrf-token"]').attr("content");
		if (!metaCsrf) {
			console.warn("Gagal mengekstrak CSRF Token");
			return false;
		}
	}

	// 3. Kirim request POST login dengan data autentikasi dan CSRF Token
	const loginPayload = {
		_token: csrfToken,
		email: email,
		password: password,
		remember: "on",
	};

	const loginRes = http.post(`${baseUrl}/login`, loginPayload, {
		headers: {
			"Content-Type": "application/x-www-form-urlencoded",
			Referer: `${baseUrl}/login`,
		},
		// k6 otomatis menyimpan cookie session di Virtual User (VU) Cookie Jar
	});

	const loginCheck = check(loginRes, {
		"login successful (redirect or ok)": (r) =>
			r.status === 200 || r.status === 302,
	});

	return loginCheck;
}
