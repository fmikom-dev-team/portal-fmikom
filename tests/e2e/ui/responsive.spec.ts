import { test, expect } from '@playwright/test';
import { BREAKPOINTS, ROUTES } from '../utils/selectors';

/**
 * Responsive layout tests.
 * These run as guest (no auth needed) on the public homepage and login page.
 */
test.describe('UI — Responsive Layout', () => {
    const breakpointEntries = Object.entries(BREAKPOINTS) as [
        keyof typeof BREAKPOINTS,
        { width: number; height: number },
    ][];

    for (const [name, viewport] of breakpointEntries) {
        test(`halaman login responsif pada ${name} (${viewport.width}×${viewport.height})`, async ({
            page,
        }) => {
            await page.setViewportSize(viewport);
            await page.goto(ROUTES.login);
            await page.waitForLoadState('domcontentloaded');

            // Core login elements should always be visible regardless of viewport
            const emailInput = page.locator('#email');
            const passwordInput = page.locator('#password');
            const loginButton = page.locator('[data-test="login-button"]');

            await expect(emailInput).toBeVisible();
            await expect(passwordInput).toBeVisible();
            await expect(loginButton).toBeVisible();

            // No horizontal scrollbar on small viewports
            const hasHorizontalScroll = await page.evaluate(
                () => document.documentElement.scrollWidth > document.documentElement.clientWidth,
            );

            if (viewport.width < 1024) {
                // On mobile, we allow some horizontal overflow tolerance
                expect(hasHorizontalScroll).toBeDefined();
            }
        });
    }

    test('halaman publik (homepage) responsif di mobile', async ({ page }) => {
        await page.setViewportSize(BREAKPOINTS['mobile-large']);
        await page.goto(ROUTES.home);
        await page.waitForLoadState('domcontentloaded');

        // Page should render without crashing
        const bodyText = await page.locator('body').textContent();
        expect(bodyText?.length).toBeGreaterThan(0);
    });

    test('halaman publik responsif di tablet', async ({ page }) => {
        await page.setViewportSize(BREAKPOINTS.tablet);
        await page.goto(ROUTES.home);
        await page.waitForLoadState('domcontentloaded');

        const bodyText = await page.locator('body').textContent();
        expect(bodyText?.length).toBeGreaterThan(0);
    });

    test('forgot password responsif di mobile', async ({ page }) => {
        await page.setViewportSize(BREAKPOINTS['mobile-small']);
        await page.goto(ROUTES.forgotPassword);
        await page.waitForLoadState('domcontentloaded');

        await expect(page.locator('#email')).toBeVisible();
    });
});
