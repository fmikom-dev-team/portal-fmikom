import type { Page, Locator } from '@playwright/test';
import { LOGIN_SELECTORS, ROUTES } from '../utils/selectors';

/**
 * LoginPage — Page Object Model for /login
 *
 * Encapsulates all interactions with the login page to avoid
 * duplicating selectors across test files.
 */
export class LoginPage {
    readonly page: Page;
    readonly emailInput: Locator;
    readonly passwordInput: Locator;
    readonly rememberCheckbox: Locator;
    readonly submitButton: Locator;

    constructor(page: Page) {
        this.page = page;
        this.emailInput = page.locator(LOGIN_SELECTORS.emailInput);
        this.passwordInput = page.locator(LOGIN_SELECTORS.passwordInput);
        this.rememberCheckbox = page.locator(LOGIN_SELECTORS.rememberCheckbox);
        this.submitButton = page.locator(LOGIN_SELECTORS.submitButton);
    }

    async goto(): Promise<void> {
        await this.page.goto(ROUTES.login);
        await this.page.waitForLoadState('domcontentloaded');
    }

    async fillEmail(email: string): Promise<void> {
        await this.emailInput.fill(email);
    }

    async fillPassword(password: string): Promise<void> {
        await this.passwordInput.fill(password);
    }

    async checkRememberMe(): Promise<void> {
        await this.rememberCheckbox.check();
    }

    async submit(): Promise<void> {
        await this.submitButton.click();
    }

    /**
     * Full login sequence with email, password, and optional remember.
     */
    async login(email: string, password: string, remember = false): Promise<void> {
        await this.fillEmail(email);
        await this.fillPassword(password);
        if (remember) {
            await this.checkRememberMe();
        }
        await this.submit();
    }

    /**
     * Get the currently displayed error message (if any).
     */
    async getError(): Promise<string> {
        const errorLocator = this.page.locator('.text-red-600').first();
        await errorLocator.waitFor({ state: 'visible', timeout: 5_000 }).catch(() => null);
        return errorLocator.textContent().then((t) => t?.trim() ?? '');
    }

    /**
     * Get the status/success message (if any).
     */
    async getStatus(): Promise<string> {
        const statusLocator = this.page.locator(LOGIN_SELECTORS.statusMessage).first();
        await statusLocator.waitFor({ state: 'visible', timeout: 5_000 }).catch(() => null);
        return statusLocator.textContent().then((t) => t?.trim() ?? '');
    }

    async isAt(): Promise<boolean> {
        return this.page.url().includes('/login');
    }
}
