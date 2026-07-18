import { test, expect } from '../fixtures/auth.fixture';
import { ROUTES } from '../utils/selectors';

test.use({ userRole: 'mahasiswa' });

test.describe('Security — Forbidden (Role Permissions)', () => {
    test.beforeEach(async ({ db, auth, page }) => {
        await db.seed();
        await auth.loginAs('mahasiswa');
        await page.goto(ROUTES.home);
        await page.waitForURL(/\/(dashboard|portal)/, { timeout: 10_000 });
    });

    test('mahasiswa tidak dapat mengakses WorkOs admin panel', async ({ page }) => {
        await page.goto(ROUTES.workos);
        await page.waitForTimeout(2_000);

        const url = page.url();
        const bodyText = await page.locator('body').textContent();

        // Should not be able to access WorkOs
        const isBlocked =
            url.includes('/login') ||
            url.includes('/dashboard') ||
            bodyText?.includes('403') ||
            bodyText?.includes('Forbidden') ||
            !url.includes('/workos');

        expect(isBlocked).toBe(true);
    });

    test('mahasiswa tidak dapat mengakses portal-admin', async ({ page }) => {
        await page.goto(ROUTES.portalAdmin);
        await page.waitForTimeout(2_000);

        const url = page.url();
        expect(url).not.toMatch(/\/portal-admin/);
    });

    test('mahasiswa tidak dapat mengakses PAGI admin', async ({ page }) => {
        await page.goto(ROUTES.pagiAdmin);
        await page.waitForTimeout(2_000);

        const url = page.url();
        expect(url).not.toMatch(/\/pagi\/admin/);
    });

    test('mahasiswa dapat mengakses settings mereka sendiri', async ({ page }) => {
        await page.goto(ROUTES.settingsProfile);
        await page.waitForLoadState('networkidle');

        // Mahasiswa should be able to access their own settings
        expect(page.url()).toMatch(/\/settings\/profile/);
    });
});
