import type { Locator, Page } from "@playwright/test";
import { ROUTES } from "../utils/selectors";

/**
 * WorkOsDashboardPage — Page Object Model for /workos (SPA Admin)
 *
 * WorkOs is a single-page application with client-side routing.
 * All sections (/workos/users, /workos/roles, etc.) are served by one
 * Laravel route but navigated via Vue Router client-side.
 *
 * Always wait for Vue reactivity to settle before asserting.
 */
export class WorkOsDashboardPage {
	readonly page: Page;

	constructor(page: Page) {
		this.page = page;
	}

	async goto(): Promise<void> {
		await this.page.goto(ROUTES.workos);
		await this.page.waitForLoadState("networkidle");
	}

	async gotoUsers(): Promise<void> {
		await this.page.goto(ROUTES.workosUsers);
		await this.page.waitForLoadState("networkidle");
		// Wait for the SPA to render the users panel
		await this.page.waitForTimeout(1_000);
	}

	async isAt(): Promise<boolean> {
		return this.page.url().includes("/workos");
	}

	/**
	 * Search for a user by name or email.
	 */
	async searchUser(query: string): Promise<void> {
		const searchInput = this.page
			.locator(
				'[data-test="search-input"], input[placeholder*="Search"], input[placeholder*="Cari"]',
			)
			.first();
		await searchInput.fill(query);
		await this.page.waitForTimeout(500); // Debounce
	}

	/**
	 * Find the first pending user row in the users table.
	 */
	async findPendingUserRow(): Promise<Locator | null> {
		const pendingRows = this.page.locator(
			'tr:has-text("pending"), [data-status="pending"], [class*="pending"]',
		);
		const count = await pendingRows.count();
		if (count === 0) return null;
		return pendingRows.first();
	}

	/**
	 * Get total user count from the dashboard stats.
	 */
	async getUserCount(): Promise<number | null> {
		try {
			const countEl = this.page
				.locator('[data-test="user-count"], .stat-users')
				.first();
			const text = await countEl.textContent({ timeout: 3_000 });
			const parsed = parseInt(text?.replace(/\D/g, "") ?? "0", 10);
			return isNaN(parsed) ? null : parsed;
		} catch {
			return null;
		}
	}
}
