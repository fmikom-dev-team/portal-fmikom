import { expect, test } from "@playwright/test";

test("homepage loads successfully", async ({ page }) => {
	// Navigasi ke URL utama aplikasi Anda (menggunakan baseURL dari .env)
	await page.goto("/");

	// Pastikan URL-nya benar
	await expect(page).toHaveURL("/");
});
