import { check, sleep } from "k6";
import http from "k6/http";
import { loginLaravel } from "./helpers/auth.js";

export const options = {
	stages: [
		{ duration: "30s", target: 10 },
		{ duration: "1m", target: 10 },
		{ duration: "30s", target: 0 },
	],
	thresholds: {
		http_req_failed: ["rate<0.02"],
		http_req_duration: ["p(95)<1200"],
	},
};

const TARGET_URL = __ENV.TARGET_URL || "https://fmikom.suntree.my.id";
const TEST_USER_EMAIL = __ENV.TEST_USER_EMAIL || "muchlisinmaruf@gmail.com"; // Butuh hak admin untuk kelola kategori
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

	const randomSuffix = Math.floor(Math.random() * 1000000);
	const categoryName = `Test Category ${randomSuffix}`;
	const categorySlug = `test-category-${randomSuffix}`;

	const headers = {
		"Content-Type": "application/json",
		Accept: "application/json",
		"X-XSRF-TOKEN": xsrfToken,
		"X-Requested-With": "XMLHttpRequest",
	};

	// 1. CREATE (Post new category)
	const createPayload = JSON.stringify({
		name: categoryName,
		slug: categorySlug,
		description: "Temporary category for performance load testing.",
	});

	const createRes = http.post(
		`${TARGET_URL}/portal-admin/categories`,
		createPayload,
		{ headers },
	);

	const isCreated = check(createRes, {
		"create category status is 200": (r) => r.status === 200,
		"category json returned": (r) => r.json() !== null,
	});

	if (!isCreated) {
		console.error("Gagal membuat kategori, melewati sisa alur CRUD");
		sleep(2);
		return;
	}

	const categoryId = createRes.json().category.id;

	sleep(1);

	// 2. READ (Get categories list)
	const readRes = http.get(`${TARGET_URL}/portal-admin/categories`, {
		headers,
	});
	check(readRes, {
		"read category list status is 200": (r) => r.status === 200,
	});

	sleep(1);

	// 3. UPDATE (Put updated details)
	const updatePayload = JSON.stringify({
		name: `${categoryName} (Updated)`,
		slug: `${categorySlug}-updated`,
		description: "Updated temporary category description.",
	});

	const updateRes = http.put(
		`${TARGET_URL}/portal-admin/categories/${categoryId}`,
		updatePayload,
		{ headers },
	);
	check(updateRes, {
		"update category status is 200 or 302": (r) =>
			r.status === 200 || r.status === 302,
	});

	sleep(1);

	// 4. DELETE (Destroy category to clean up db)
	const deleteRes = http.del(
		`${TARGET_URL}/portal-admin/categories/${categoryId}`,
		null,
		{ headers },
	);
	check(deleteRes, {
		"delete category status is 200 or 302": (r) =>
			r.status === 200 || r.status === 302,
	});

	sleep(2);
}
