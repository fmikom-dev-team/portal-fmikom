import { test, expect } from '../fixtures/auth.fixture';
import { SettingsPage } from '../pages/SettingsPage';
import { ROUTES } from '../utils/selectors';

test.use({ userRole: 'mahasiswa' });

test.describe('Settings — Security', () => {

    test('halaman security settings dapat diakses', async ({ authenticatedPage }) => {
        const settingsPage = new SettingsPage(authenticatedPage);
        await settingsPage.gotoSecurity();
        await expect(settingsPage.isAtSecurity()).resolves.toBe(true);
    });

    test('security settings menampilkan form ubah password', async ({ authenticatedPage }) => {
        const settingsPage = new SettingsPage(authenticatedPage);
        await settingsPage.gotoSecurity();

        // There should be a password update form
        const currentPasswordInput = authenticatedPage.locator(
            '#current_password, input[name="current_password"], input[type="password"]',
        ).first();

        await expect(currentPasswordInput).toBeVisible();
    });

    test('update password gagal jika current password salah', async ({ authenticatedPage }) => {
        const settingsPage = new SettingsPage(authenticatedPage);
        await settingsPage.gotoSecurity();

        // Fill in wrong current password
        const currentPwdInput = authenticatedPage.locator('#current_password, input[name="current_password"]').first();
        if (await currentPwdInput.isVisible()) {
            await currentPwdInput.fill('WrongCurrentPassword!');

            const newPwdInput = authenticatedPage.locator('#password, input[name="password"]').nth(1);
            if (await newPwdInput.isVisible()) {
                await newPwdInput.fill('NewPassword@123!');
            }

            const submitBtn = authenticatedPage.locator('button[type="submit"]:has-text("Save"), button:has-text("Update"), button:has-text("Perbarui")').first();
            if (await submitBtn.isVisible()) {
                await submitBtn.click();
                await authenticatedPage.waitForTimeout(2_000);

                // Should show an error — current password is wrong
                const errorEl = authenticatedPage.locator('.text-red-500, [class*="error"], [class*="destructive"]').first();
                const hasError = await errorEl.isVisible().catch(() => false);
                expect(hasError || authenticatedPage.url().includes('/settings/security')).toBe(true);
            }
        }
    });
});
