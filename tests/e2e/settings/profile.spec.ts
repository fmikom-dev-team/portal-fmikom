import { expect, test } from "../fixtures/auth.fixture";
import { SettingsPage } from "../pages/SettingsPage";
import { ROUTES } from "../utils/selectors";

test.use({ userRole: "mahasiswa" });

test.describe("Settings — Profile", () => {
	test("halaman profile settings dapat diakses", async ({
		authenticatedPage,
	}) => {
		const settingsPage = new SettingsPage(authenticatedPage);
		await settingsPage.gotoProfile();
		await expect(settingsPage.isAtProfile()).resolves.toBe(true);
	});

	test("profile settings menampilkan form dengan field yang benar", async ({
		authenticatedPage,
	}) => {
		const settingsPage = new SettingsPage(authenticatedPage);
		await settingsPage.gotoProfile();

		// Name field should be visible
		const nameInput = authenticatedPage
			.locator('#name, input[name="name"]')
			.first();
		await expect(nameInput).toBeVisible();

		// Email field should be visible (usually readonly or verified)
		const emailInput = authenticatedPage
			.locator('#email, input[name="email"]')
			.first();
		await expect(emailInput).toBeVisible();
	});

	test("halaman appearance settings dapat diakses", async ({
		authenticatedPage,
	}) => {
		const settingsPage = new SettingsPage(authenticatedPage);
		await settingsPage.gotoAppearance();
		await expect(authenticatedPage).toHaveURL(/\/settings\/appearance/);
	});
});
