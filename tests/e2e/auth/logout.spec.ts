import { expect, test } from "../fixtures/auth.fixture";
import { LoginPage } from "../pages/LoginPage";
import { ROUTES, TEST_USERS } from "../utils/selectors";

test.describe("Authentication — Logout", () => {
	test("mahasiswa dapat logout dari dashboard", async ({
		authenticatedPage,
	}) => {
		await authenticatedPage.goto(ROUTES.portal);
		await authenticatedPage.waitForLoadState("networkidle");

		// Click the logout button in the UI (Inertia Link doing POST /logout)
		const logoutBtn = authenticatedPage
			.locator(
				'button:has-text("Logout"), a:has-text("Logout"), button:has-text("Keluar"), [href="/logout"]',
			)
			.first();
		await logoutBtn.click();

		// Fortify redirects to homepage on web logout
		await authenticatedPage.waitForURL(ROUTES.home, { timeout: 15_000 });
		expect(authenticatedPage.url()).toMatch(/\/$/);
	});

	test("setelah logout, halaman terproteksi tidak bisa diakses", async ({
		authenticatedPage,
	}) => {
		await authenticatedPage.goto(ROUTES.portal);
		await authenticatedPage.waitForLoadState("networkidle");

		// Logout
		const logoutBtn = authenticatedPage
			.locator(
				'button:has-text("Logout"), a:has-text("Logout"), button:has-text("Keluar"), [href="/logout"]',
			)
			.first();
		await logoutBtn.click();
		await authenticatedPage.waitForURL(ROUTES.home, { timeout: 15_000 });

		// Try to access protected page
		await authenticatedPage.goto(ROUTES.settingsProfile);
		await authenticatedPage.waitForURL(/\/login/, { timeout: 10_000 });
		expect(authenticatedPage.url()).toMatch(/\/login/);
	});

	test("session dihapus setelah logout", async ({ page, auth, db }) => {
		await db.seed();

		// UI login
		const loginPage = new LoginPage(page);
		await loginPage.goto();
		await loginPage.login(
			TEST_USERS.mahasiswa.email,
			TEST_USERS.mahasiswa.password,
		);
		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 15_000 });

		// Verify authenticated
		const meResponse = await auth.me();
		expect(meResponse?.authenticated).toBe(true);

		// Logout
		await auth.logout();

		// Verify unauthenticated
		const meAfter = await auth.me();
		expect(meAfter?.authenticated).toBe(false);
	});
});
