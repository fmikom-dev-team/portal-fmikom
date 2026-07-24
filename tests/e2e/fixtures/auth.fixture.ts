import { test as base, expect, type Page } from "@playwright/test";
import { AuthHelper } from "../helpers/auth.helper";
import { DbHelper } from "../helpers/db.helper";
import { LoginPage } from "../pages/LoginPage";
import { ROUTES, TEST_USERS } from "../utils/selectors";

type UserRole =
	| "superAdmin"
	| "mahasiswa"
	| "dosen"
	| "alumni"
	| "pending"
	| "rejected";

/**
 * Auth-aware fixture that sets up an authenticated browser session
 * using the UI login flow.
 *
 * Usage:
 *   import { test } from '../fixtures/auth.fixture';
 *   test.use({ userRole: 'superAdmin' });
 *   test('admin can see dashboard', async ({ authenticatedPage }) => { ... });
 */
export const test = base.extend<{
	userRole: UserRole;
	auth: AuthHelper;
	db: DbHelper;
	authenticatedPage: Page;
}>({
	userRole: ["mahasiswa", { option: true }],

	auth: async ({ page }, use) => {
		const auth = new AuthHelper(page.request);
		await use(auth);
	},

	db: async ({ page }, use) => {
		const db = new DbHelper(page.request);
		await use(db);
	},

	/**
	 * `authenticatedPage` — a page that is already logged in as `userRole`.
	 * Performs a standard UI login flow for 100% reliable session state.
	 */
	authenticatedPage: async ({ page, userRole }, use) => {
		const db = new DbHelper(page.request);
		await db.seed();

		const loginPage = new LoginPage(page);
		await loginPage.goto();

		const user = TEST_USERS[userRole];
		await loginPage.login(user.email, user.password);

		// Wait for redirect to dashboard/portal
		await page.waitForURL(/\/(dashboard|portal|pagi|wims|workos)/, {
			timeout: 15_000,
		});

		await use(page);

		// Cleanup: navigate to logout or call API logout
		const auth = new AuthHelper(page.request);
		await auth.logout();
	},
});

export { expect };
