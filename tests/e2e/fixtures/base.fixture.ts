import { test as base, expect } from "@playwright/test";
import { AuthHelper } from "../helpers/auth.helper";
import { DbHelper } from "../helpers/db.helper";
import { ROUTES } from "../utils/selectors";

/**
 * Extended test fixture with helpers pre-instantiated.
 *
 * Usage:
 *   import { test } from '../fixtures/base.fixture';
 *   test('example', async ({ auth, db, page }) => { ... });
 */
export const test = base.extend<{
	auth: AuthHelper;
	db: DbHelper;
}>({
	auth: async ({ page }, use) => {
		const auth = new AuthHelper(page.request);
		await use(auth);
	},

	db: async ({ page }, use) => {
		const db = new DbHelper(page.request);
		await use(db);
	},
});

export { expect };
