import { expect, test } from "../fixtures/auth.fixture";
import { ROUTES } from "../utils/selectors";

test.use({ userRole: "mahasiswa" });

test.describe("Modul PAGI — Dashboard Mahasiswa", () => {
	test.beforeEach(async ({ db, auth, page }) => {
		await db.seed();
		await auth.loginAs("mahasiswa");
		await page.goto(ROUTES.home);
		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 10_000 });
	});

	test("mahasiswa dapat mengakses portal module selector", async ({ page }) => {
		await page.goto(ROUTES.portal);
		await page.waitForLoadState("networkidle");

		// Portal page should load (module selection page)
		expect(page.url()).toMatch(/\/portal/);
		await expect(page.locator("body")).not.toBeEmpty();
	});

	test("dashboard menampilkan konten setelah login", async ({ page }) => {
		await page.goto(ROUTES.dashboard);
		await page.waitForLoadState("networkidle");

		const bodyText = await page.locator("body").textContent();
		expect(bodyText?.length).toBeGreaterThan(50);
	});

	test("mahasiswa dapat mengakses modul PAGI", async ({ page }) => {
		await page.goto(ROUTES.pagi);
		await page.waitForLoadState("networkidle");
		await page.waitForTimeout(1_000);

		// Should not get a 403 or 404
		const errorEl = page.locator("text=403, text=404").first();
		const hasError = await errorEl.isVisible().catch(() => false);
		expect(hasError).toBe(false);
	});

	test("mahasiswa tidak dapat mengakses PAGI admin panel", async ({ page }) => {
		await page.goto(ROUTES.pagiAdmin);
		await page.waitForTimeout(2_000);

		// Should be redirected (not an admin)
		const url = page.url();
		expect(url).not.toMatch(/\/pagi\/admin/);
	});

	test("mahasiswa tidak dapat mengakses WorkOs admin", async ({ page }) => {
		await page.goto(ROUTES.workos);
		await page.waitForTimeout(2_000);

		// Should be redirected away or shown 403
		const url = page.url();
		const body = await page.locator("body").textContent();
		const has403 = body?.includes("403") || body?.includes("Forbidden");
		const redirectedAway = !url.includes("/workos");

		expect(has403 || redirectedAway).toBe(true);
	});
});
