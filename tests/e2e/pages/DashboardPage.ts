import type { Page, Locator } from '@playwright/test';
import { ROUTES } from '../utils/selectors';

/**
 * DashboardPage — Page Object Model for /dashboard (Portal module selector)
 */
export class DashboardPage {
    readonly page: Page;

    constructor(page: Page) {
        this.page = page;
    }

    async goto(): Promise<void> {
        await this.page.goto(ROUTES.dashboard);
        await this.page.waitForLoadState('networkidle');
    }

    async isAt(): Promise<boolean> {
        return this.page.url().includes('/dashboard');
    }

    async getTitle(): Promise<string> {
        return this.page.title();
    }

    /**
     * Get all visible module cards on the portal dashboard.
     */
    async getModuleCards(): Promise<Locator[]> {
        await this.page.goto(ROUTES.portal);
        await this.page.waitForLoadState('networkidle');
        const cards = this.page.locator('[data-test="module-card"], .module-card, [class*="module"]');
        const count = await cards.count();
        return Array.from({ length: count }, (_, i) => cards.nth(i));
    }

    /**
     * Click the logout option from the user menu.
     */
    async logout(): Promise<void> {
        // Try to find logout via user menu or direct button
        const logoutBtn = this.page.locator('[data-test="logout-button"], button:has-text("Logout"), button:has-text("Keluar"), a:has-text("Logout"), a:has-text("Keluar")').first();
        await logoutBtn.click();
        await this.page.waitForURL(/\/login/, { timeout: 10_000 });
    }
}
