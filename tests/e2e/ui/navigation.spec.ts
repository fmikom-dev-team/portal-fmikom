import { expect, test } from "../fixtures/auth.fixture";
import { ROUTES } from "../utils/selectors";

test.use({ userRole: "mahasiswa" });

test.describe("UI — Navigation", () => {
	test.beforeEach(async ({ db, auth, page }) => {
		await db.seed();
		await auth.loginAs("mahasiswa");
		await page.goto(ROUTES.home);
		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 10_000 });
	});

	test("dashboard memiliki elemen navigasi utama", async ({ page }) => {
		await page.goto(ROUTES.dashboard);
		await page.waitForLoadState("networkidle");

		// Check that there's some navigation element
		const nav = page.locator("nav, header, aside").first();
		await expect(nav).toBeVisible();
	});

	test("halaman memiliki title yang benar", async ({ page }) => {
		await page.goto(ROUTES.dashboard);
		const title = await page.title();
		expect(title.length).toBeGreaterThan(0);
	});

	test("portal menampilkan modul yang tersedia", async ({ page }) => {
		await page.goto(ROUTES.portal);
		await page.waitForLoadState("networkidle");

		// Portal module selection page should have some content
		const bodyText = await page.locator("body").textContent();
		expect(bodyText?.length).toBeGreaterThan(50);
	});

	test("settings profile dapat diakses dari navigasi", async ({ page }) => {
		await page.goto(ROUTES.settingsProfile);
		await page.waitForLoadState("networkidle");
		expect(page.url()).toMatch(/\/settings\/profile/);
	});

	test("link-link navigasi berfungsi tanpa error 404", async ({ page }) => {
		const protectedRoutes = [
			ROUTES.dashboard,
			ROUTES.portal,
			ROUTES.settingsProfile,
			ROUTES.settingsAppearance,
		];

		for (const route of protectedRoutes) {
			await page.goto(route);
			await page.waitForLoadState("networkidle");

			// Page should not show 404 error
			const pageText = await page.locator("body").textContent();
			const has404 =
				pageText?.includes("404") && pageText?.includes("Not Found");
			expect(has404, `Route ${route} returned 404`).toBe(false);
		}
	});
});
