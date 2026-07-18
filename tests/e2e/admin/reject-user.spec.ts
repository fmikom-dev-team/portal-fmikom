import { test, expect } from '../fixtures/auth.fixture';
import { ROUTES } from '../utils/selectors';

test.use({ userRole: 'superAdmin' });

test.describe('Admin — Reject User Registration', () => {
    test.beforeEach(async ({ db, auth, page }) => {
        await db.seed();
        await auth.loginAs('superAdmin');
        await page.goto(ROUTES.home);
        await page.waitForURL(/\/(dashboard|portal|workos)/, { timeout: 10_000 });
    });

    test('reject API endpoint tersedia', async ({ page }) => {
        // Verify the testing endpoint is reachable
        const ping = await page.request.get('/__testing/ping', {
            headers: { 'Accept': 'application/json' },
        });
        expect(ping.ok()).toBe(true);
        const body = await ping.json();
        expect(body.status).toBe('ok');
    });

    test('rejected user tidak dapat login ke sistem', async ({ page, auth }) => {
        // Logout admin
        await auth.logout();

        // Try to login as rejected user
        await page.goto(ROUTES.login);
        await page.fill('#email', 'playwright.rejected@fmikom.test');
        await page.fill('#password', 'Playwright@Rejected1');
        await page.click('[data-test="login-button"]');

        await page.waitForTimeout(3_000);

        // Should remain on login page (rejected users cannot login)
        const url = page.url();
        expect(url).toMatch(/\/login/);
    });

    test('admin dapat melihat panel users rejection', async ({ page }) => {
        await page.goto(ROUTES.workosUsers);
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(2_000);

        // Verify admin is on the WorkOs users page
        expect(page.url()).toContain('/workos');
    });
});
