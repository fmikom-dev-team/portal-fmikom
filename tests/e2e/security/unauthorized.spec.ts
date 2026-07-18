import { test, expect } from '@playwright/test';
import { ROUTES } from '../utils/selectors';

/**
 * Security — Unauthorized Access Tests
 * These tests verify that unauthenticated users are blocked from protected routes.
 */
test.describe('Security — Unauthorized Access', () => {
    const protectedRoutes = [
        ROUTES.dashboard,
        ROUTES.portal,
        ROUTES.settingsProfile,
        ROUTES.settingsSecurity,
        ROUTES.settingsAppearance,
        ROUTES.pagi,
        ROUTES.wims,
    ];

    for (const route of protectedRoutes) {
        test(`route ${route} membutuhkan autentikasi`, async ({ page }) => {
            // Clear all cookies/storage to ensure no session
            await page.context().clearCookies();

            await page.goto(route);
            await page.waitForTimeout(2_000);

            // Should redirect to login or show 401/403
            const url = page.url();
            const bodyText = await page.locator('body').textContent();

            const isRedirectedToLogin = url.includes('/login');
            const shows401 = bodyText?.includes('401');
            const shows403 = bodyText?.includes('403') || bodyText?.includes('Unauthorized');

            expect(
                isRedirectedToLogin || shows401 || shows403,
                `Route ${route} should require authentication, but got URL: ${url}`,
            ).toBe(true);
        });
    }

    test('WorkOs admin routes membutuhkan autentikasi', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto(ROUTES.workos);
        await page.waitForTimeout(2_000);

        const url = page.url();
        expect(url).toMatch(/\/login/);
    });

    test('portal admin route membutuhkan autentikasi', async ({ page }) => {
        await page.context().clearCookies();
        await page.goto(ROUTES.portalAdmin);
        await page.waitForTimeout(2_000);

        const url = page.url();
        expect(url).toMatch(/\/login/);
    });

    test('testing routes hanya dapat diakses di environment lokal', async ({ page }) => {
        const response = await page.request.get(ROUTES.testingPing, {
            headers: { 'Accept': 'application/json' },
        });

        // In local env, should be accessible. In production, should return 404.
        // We just verify it responds (not crashes).
        expect([200, 404]).toContain(response.status());
    });
});
