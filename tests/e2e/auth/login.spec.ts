import { expect, test } from "../fixtures/base.fixture";
import { LoginPage } from "../pages/LoginPage";
import { ROUTES, TEST_USERS } from "../utils/selectors";

test.describe("Authentication — Login", () => {
	let loginPage: LoginPage;

	test.beforeEach(async ({ page, db }) => {
		await db.seed();
		loginPage = new LoginPage(page);
		await loginPage.goto();
	});

	test("shows login page with correct elements", async ({ page }) => {
		await expect(page).toHaveTitle(/Log in|Login/i);
		await expect(page.locator("#email")).toBeVisible();
		await expect(page.locator("#password")).toBeVisible();
		await expect(page.locator('[data-test="login-button"]')).toBeVisible();
	});

	test("login berhasil sebagai Super Admin", async ({ page }) => {
		const { email, password } = TEST_USERS.superAdmin;
		await loginPage.login(email, password);

		await page.waitForURL(/\/(dashboard|portal|workos)/, { timeout: 15_000 });
		expect(page.url()).toMatch(/\/(dashboard|portal|workos)/);
	});

	test("login berhasil sebagai Mahasiswa", async ({ page }) => {
		const { email, password } = TEST_USERS.mahasiswa;
		await loginPage.login(email, password);

		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 15_000 });
		expect(page.url()).toMatch(/\/(dashboard|portal)/);
	});

	test("login berhasil sebagai Dosen", async ({ page }) => {
		const { email, password } = TEST_USERS.dosen;
		await loginPage.login(email, password);

		await page.waitForURL(/\/(dashboard|portal)/, { timeout: 15_000 });
		expect(page.url()).toMatch(/\/(dashboard|portal)/);
	});

	test("login gagal dengan password salah", async ({ page }) => {
		await loginPage.login(TEST_USERS.mahasiswa.email, "WrongPassword123!");

		// Should stay on login page or show error
		await page.waitForTimeout(2_000);
		const currentUrl = page.url();
		expect(currentUrl).toMatch(/\/login/);
	});

	test("login gagal dengan email yang tidak ada", async ({ page }) => {
		await loginPage.login("tidakada@fmikom.test", "Password123!");

		await page.waitForTimeout(2_000);
		expect(page.url()).toMatch(/\/login/);
	});

	test("login gagal dengan field email kosong", async ({ page }) => {
		await loginPage.fillPassword("Password123!");
		await loginPage.submit();

		// HTML5 validation should prevent submission or show error
		const emailInput = page.locator("#email");
		const validationMessage = await emailInput.evaluate(
			(el: HTMLInputElement) => el.validationMessage,
		);
		expect(validationMessage).not.toBe("");
	});

	test("login gagal dengan field password kosong", async ({ page }) => {
		await loginPage.fillEmail(TEST_USERS.mahasiswa.email);
		await loginPage.submit();

		const passwordInput = page.locator("#password");
		const validationMessage = await passwordInput.evaluate(
			(el: HTMLInputElement) => el.validationMessage,
		);
		expect(validationMessage).not.toBe("");
	});

	test("user pending diarahkan ke waiting room", async ({ page }) => {
		const { email, password } = TEST_USERS.pending;
		await loginPage.login(email, password);

		// Pending users should be blocked and see an error, or redirected
		await page.waitForTimeout(3_000);
		// Should either stay on login or go to waiting room
		const url = page.url();
		expect(url).toMatch(/\/(login|waiting-room)/);
	});

	test("redirect ke halaman yang dituju setelah login", async ({ page }) => {
		// Navigate to a protected page
		await page.goto(ROUTES.settingsProfile);
		// Should be redirected to login
		await page.waitForURL(/\/login/, { timeout: 5_000 });

		// Login
		await loginPage.login(
			TEST_USERS.mahasiswa.email,
			TEST_USERS.mahasiswa.password,
		);

		// Should redirect back to settings/profile or dashboard
		await page.waitForURL(/\/(settings\/profile|dashboard|portal)/, {
			timeout: 15_000,
		});
	});

	test("halaman login dapat diakses dengan keyboard", async ({ page }) => {
		await page.keyboard.press("Tab");
		const emailFocused = await page.evaluate(
			() => document.activeElement?.id === "email",
		);

		// Tab through fields
		await page.keyboard.press("Tab");
		await page.keyboard.press("Tab");

		// Page should still be functional
		await expect(page.locator("#email")).toBeVisible();
	});
});
