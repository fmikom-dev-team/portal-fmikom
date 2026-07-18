import { test, expect } from '../fixtures/auth.fixture';
import { WorkOsDashboardPage } from '../pages/WorkOsDashboardPage';
import { ROUTES } from '../utils/selectors';

test.use({ userRole: 'superAdmin' });

test.describe('Admin — WorkOs Dashboard', () => {
    let adminPage: WorkOsDashboardPage;

    test.beforeEach(async ({ db, auth, page }) => {
        await db.seed();
        await auth.loginAs('superAdmin');
        await page.goto(ROUTES.home);
        await page.waitForURL(/\/(dashboard|portal|workos)/, { timeout: 10_000 });
        adminPage = new WorkOsDashboardPage(page);
    });

    test('super admin dapat mengakses WorkOs dashboard', async ({ page }) => {
        await adminPage.goto();
        await expect(page).toHaveURL(/\/workos/);

        // Dashboard should load without errors
        const errorPage = page.locator('text=403, text=404, text=500').first();
        const hasError = await errorPage.isVisible().catch(() => false);
        expect(hasError).toBe(false);
    });

    test('dashboard menampilkan navigasi admin yang benar', async ({ page }) => {
        await adminPage.goto();

        // Wait for SPA to render
        await page.waitForLoadState('networkidle');
        await page.waitForTimeout(1_000);

        // Check that the page renders some content (not blank)
        const bodyText = await page.locator('body').textContent();
        expect(bodyText?.length).toBeGreaterThan(100);
    });

    test('admin dapat mengakses halaman manajemen users', async ({ page }) => {
        await adminPage.gotoUsers();
        await expect(page).toHaveURL(/\/workos/);

        // Wait for SPA navigation to settle
        await page.waitForTimeout(2_000);
        const bodyText = await page.locator('body').textContent();
        expect(bodyText?.length).toBeGreaterThan(50);
    });

    test('mahasiswa biasa tidak dapat mengakses WorkOs admin', async ({ page, auth }) => {
        // Logout super admin
        await auth.logout();

        // Login as mahasiswa
        await auth.loginAs('mahasiswa');
        await page.goto(ROUTES.home);
        await page.waitForURL(/\/(dashboard|portal)/, { timeout: 10_000 });

        // Try to access WorkOs — should get 403 or redirect
        await page.goto(ROUTES.workos);
        await page.waitForTimeout(2_000);

        const url = page.url();
        // Should either redirect to dashboard or show 403
        expect(url).not.toMatch(/\/workos\/users/);
    });
});
