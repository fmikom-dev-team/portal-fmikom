import type { Page } from "@playwright/test";
import { ROUTES } from "../utils/selectors";

/**
 * SettingsPage — Page Object Model for /settings/*
 */
export class SettingsPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	async gotoProfile(): Promise<void> {
		await this.page.goto(ROUTES.settingsProfile);
		await this.page.waitForLoadState("networkidle");
	}

	async gotoSecurity(
		password: string = "Playwright@Mahasiswa1",
	): Promise<void> {
		await this.page.goto(ROUTES.settingsSecurity);
		await this.page.waitForLoadState("networkidle");

		// Handle Fortify password confirmation if intercepted
		if (this.page.url().includes("confirm-password")) {
			const pwdInput = this.page.locator('input[type="password"]').first();
			await pwdInput.fill(password);

			const confirmBtn = this.page
				.locator('button:has-text("Confirm"), button:has-text("Konfirmasi")')
				.first();
			await confirmBtn.click();
			await this.page.waitForURL(/\/settings\/security/, { timeout: 10_000 });
			await this.page.waitForLoadState("networkidle");
		}
	}

	async gotoAppearance(): Promise<void> {
		await this.page.goto(ROUTES.settingsAppearance);
		await this.page.waitForLoadState("networkidle");
	}

	async isAtProfile(): Promise<boolean> {
		return this.page.url().includes("/settings/profile");
	}

	async isAtSecurity(): Promise<boolean> {
		return this.page.url().includes("/settings/security");
	}

	/**
	 * Update name on profile settings page.
	 */
	async updateName(name: string): Promise<void> {
		const nameInput = this.page.locator('#name, input[name="name"]').first();
		await nameInput.clear();
		await nameInput.fill(name);

		const submitBtn = this.page
			.locator(
				'button[type="submit"]:has-text("Save"), button:has-text("Simpan")',
			)
			.first();
		await submitBtn.click();

		await this.page.waitForTimeout(1_500);
	}

	/**
	 * Get the success/flash message after saving.
	 */
	async getFlashMessage(): Promise<string> {
		const flash = this.page
			.locator(
				'[data-test="flash"], .toast, [class*="success"], [class*="alert"]',
			)
			.first();
		await flash.waitFor({ state: "visible", timeout: 5_000 }).catch(() => null);
		return (await flash.textContent())?.trim() ?? "";
	}
}
