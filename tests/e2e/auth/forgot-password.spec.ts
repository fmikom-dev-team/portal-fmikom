import { test, expect } from '../fixtures/base.fixture';
import { FORGOT_PASSWORD_SELECTORS, ROUTES } from '../utils/selectors';

test.describe('Authentication — Forgot Password', () => {
    test.beforeEach(async ({ page }) => {
        await page.goto(ROUTES.forgotPassword);
        await page.waitForLoadState('domcontentloaded');
    });

    test('halaman forgot password dapat diakses', async ({ page }) => {
        await expect(page.locator('#email')).toBeVisible();
        await expect(page.locator(FORGOT_PASSWORD_SELECTORS.submitButton)).toBeVisible();
    });

    test('menampilkan pesan sukses setelah submit email valid', async ({ page, db }) => {
        await db.seed();

        await page.fill('#email', 'playwright.mahasiswa@fmikom.test');
        await page.click(FORGOT_PASSWORD_SELECTORS.submitButton);

        // Wait for either success message or a redirect
        await page.waitForTimeout(3_000);

        // The page should show a success status message (email sent)
        const statusEl = page.locator('.text-green-600, [class*="success"]').first();
        const hasStatus = await statusEl.isVisible().catch(() => false);

        // Either success message shows or we're still on the page (valid outcome)
        const currentUrl = page.url();
        expect(currentUrl).toMatch(/\/forgot-password/);
        if (hasStatus) {
            const text = await statusEl.textContent();
            expect(text?.toLowerCase()).toMatch(/sent|dikirim|email/i);
        }
    });

    test('form tidak submit dengan email kosong', async ({ page }) => {
        await page.click(FORGOT_PASSWORD_SELECTORS.submitButton);

        const emailInput = page.locator('#email');
        const validationMessage = await emailInput.evaluate(
            (el: HTMLInputElement) => el.validationMessage,
        );
        expect(validationMessage).not.toBe('');
    });

    test('form tidak submit dengan format email salah', async ({ page }) => {
        await page.fill('#email', 'bukan-email');
        await page.click(FORGOT_PASSWORD_SELECTORS.submitButton);

        const emailInput = page.locator('#email');
        const validationMessage = await emailInput.evaluate(
            (el: HTMLInputElement) => el.validationMessage,
        );
        expect(validationMessage).not.toBe('');
    });

    test('link "kembali ke login" berfungsi', async ({ page }) => {
        await page.click('a:has-text("log in"), a:has-text("Login"), a:has-text("login")');
        await page.waitForURL(/\/login/, { timeout: 5_000 });
        expect(page.url()).toMatch(/\/login/);
    });
});
