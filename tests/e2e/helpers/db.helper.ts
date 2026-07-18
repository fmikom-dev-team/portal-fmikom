import type { APIRequestContext } from '@playwright/test';
import { ROUTES } from '../utils/selectors';

/**
 * DbHelper — database state management for Playwright tests.
 *
 * Provides utilities to seed and verify database state via testing API.
 */
export class DbHelper {
    constructor(private readonly request: APIRequestContext) {}

    /**
     * Run PlaywrightSeeder to create test users.
     */
    async seed(): Promise<void> {
        const response = await this.request.post(ROUTES.testingSeed, {
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok()) {
            const body = await response.text();
            throw new Error(`Seeding failed: ${response.status()} — ${body}`);
        }
    }

    /**
     * Create a custom user for a specific test scenario.
     */
    async createUser(data: {
        name: string;
        email: string;
        password: string;
        user_type: string;
        status_approval?: string;
    }): Promise<{ user_id: number; email: string }> {
        const response = await this.request.post('/__testing/create-user', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            data,
        });

        if (!response.ok()) {
            const body = await response.text();
            throw new Error(`Create user failed: ${response.status()} — ${body}`);
        }

        return response.json();
    }
}
