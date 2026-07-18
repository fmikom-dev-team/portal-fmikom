import type { APIRequestContext } from '@playwright/test';
import { ROUTES, TEST_USERS } from '../utils/selectors';

type UserRole = keyof typeof TEST_USERS;

/**
 * AuthHelper — programmatic auth management for Portal FMIKOM tests.
 *
 * Uses the `/__testing/*` endpoints to login/logout without UI interaction.
 * This is the recommended approach for setting up auth state in Playwright fixtures.
 */
export class AuthHelper {
    constructor(private readonly request: APIRequestContext) {}

    /**
     * Seed test users via the testing API.
     * Call this once per test suite (or beforeAll).
     */
    async seedTestUsers(): Promise<void> {
        const response = await this.request.post(ROUTES.testingSeed, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok()) {
            const body = await response.text();
            throw new Error(`Failed to seed test users: ${response.status()} — ${body}`);
        }
    }

    /**
     * Login as a specific role via testing API (no UI, no CSRF needed).
     */
    async loginAs(role: UserRole): Promise<{ email: string; name: string; user_type: string }> {
        const user = TEST_USERS[role];

        const response = await this.request.post(ROUTES.testingLogin, {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            data: { email: user.email },
        });

        if (!response.ok()) {
            const body = await response.text();
            throw new Error(`Failed to login as ${role}: ${response.status()} — ${body}`);
        }

        return response.json();
    }

    /**
     * Logout via testing API.
     */
    async logout(): Promise<void> {
        await this.request.post(ROUTES.testingLogout, {
            headers: { 'Accept': 'application/json' },
        });
    }

    /**
     * Get currently authenticated user info.
     */
    async me(): Promise<{ authenticated: boolean; email?: string; user_type?: string } | null> {
        const response = await this.request.get(ROUTES.testingMe, {
            headers: { 'Accept': 'application/json' },
        });

        if (response.status() === 401) {
            return { authenticated: false };
        }

        return response.json();
    }

    /**
     * Verify server is reachable and in testing mode.
     */
    async ping(): Promise<boolean> {
        try {
            const response = await this.request.get(ROUTES.testingPing, {
                headers: { 'Accept': 'application/json' },
            });
            return response.ok();
        } catch {
            return false;
        }
    }
}
