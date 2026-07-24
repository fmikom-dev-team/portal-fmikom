import { expect, test } from "../fixtures/auth.fixture";
import { ROUTES } from "../utils/selectors";

test.use({ userRole: "mahasiswa" });

test.describe("Performance — Dashboard Load Time", () => {
	test.beforeEach(async ({ db, auth }) => {
		await db.seed();
		await auth.loginAs("mahasiswa");
	});

	test("halaman login dimuat dalam waktu wajar", async ({ page }) => {
		const startTime = Date.now();

		await page.goto(ROUTES.login);
		await page.waitForLoadState("domcontentloaded");

		const loadTime = Date.now() - startTime;
		expect(loadTime).toBeLessThan(10_000); // Should load in under 10 seconds
	});

	test("homepage publik dimuat dalam waktu wajar", async ({ page }) => {
		const startTime = Date.now();
		await page.goto(ROUTES.home);
		await page.waitForLoadState("domcontentloaded");
		const loadTime = Date.now() - startTime;

		expect(loadTime).toBeLessThan(15_000);
		console.log(`Homepage load time: ${loadTime}ms`);
	});

	test("dashboard dimuat setelah login dalam waktu wajar", async ({
		page,
		auth,
	}) => {
		await page.goto(ROUTES.home);
		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 15_000 });

		const startTime = Date.now();
		await page.goto(ROUTES.dashboard);
		await page.waitForLoadState("networkidle");
		const loadTime = Date.now() - startTime;

		expect(loadTime).toBeLessThan(15_000);
		console.log(`Dashboard load time: ${loadTime}ms`);
	});

	test("settings profile dimuat dalam waktu wajar", async ({ page, auth }) => {
		await page.goto(ROUTES.home);
		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 15_000 });

		const startTime = Date.now();
		await page.goto(ROUTES.settingsProfile);
		await page.waitForLoadState("networkidle");
		const loadTime = Date.now() - startTime;

		expect(loadTime).toBeLessThan(10_000);
		console.log(`Settings profile load time: ${loadTime}ms`);
	});
});
