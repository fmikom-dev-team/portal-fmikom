import { defineConfig, devices } from '@playwright/test';
import dotenv from 'dotenv';

// Load .env.playwright if it exists, otherwise fallback to .env
dotenv.config({ path: '.env.playwright', override: false });
dotenv.config({ override: false });

/**
 * Portal FMIKOM — Enterprise Playwright Configuration
 * @see https://playwright.dev/docs/test-configuration
 */
export default defineConfig({
    testDir: './tests/e2e',
    outputDir: './test-results',

    /* Run tests in files in parallel */
    fullyParallel: true,

    /* Fail the build on CI if you accidentally left test.only in the source code. */
    forbidOnly: !!process.env.CI,

    /* Retry on CI only */
    retries: process.env.CI ? 2 : 1,

    /* Opt out of parallel tests on CI. */
    workers: process.env.CI ? 1 : 2,

    /* Reporter to use */
    reporter: [
        ['html', { outputFolder: 'playwright-report', open: 'never' }],
        ['json', { outputFile: 'test-results/report.json' }],
        ['junit', { outputFile: 'test-results/junit.xml' }],
        ['list'],
    ],

    /* Shared settings for all the projects below */
    use: {
        /* Base URL from environment — falls back to local octane dev server */
        baseURL: process.env.PLAYWRIGHT_BASE_URL || process.env.APP_URL || 'http://localhost:8000',

        /* Collect trace when retrying the failed test */
        trace: 'on-first-retry',

        /* Capture screenshot on failure */
        screenshot: 'only-on-failure',

        /* Record video on failure */
        video: 'on-first-retry',

        /* Viewport default */
        viewport: { width: 1280, height: 720 },

        /* Locale */
        locale: 'id-ID',
        timezoneId: 'Asia/Makassar',

        /* Ignore HTTPS errors on local self-signed certs */
        ignoreHTTPSErrors: true,
    },

    /* Global timeout per test */
    timeout: 30_000,

    /* Expect timeout */
    expect: {
        timeout: 10_000,
        toHaveScreenshot: {
            maxDiffPixels: 100,
        },
    },

    /* Configure projects for major browsers */
    projects: [
        // ── Desktop Browsers ────────────────────────────────────────────────────
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
        {
            name: 'firefox',
            use: { ...devices['Desktop Firefox'] },
        },
        {
            name: 'webkit',
            use: { ...devices['Desktop Safari'] },
        },

        // ── Branded Browsers (CI only) ───────────────────────────────────────────
        ...(process.env.CI
            ? [
                  {
                      name: 'Google Chrome',
                      use: { ...devices['Desktop Chrome'], channel: 'chrome' },
                  },
                  {
                      name: 'Microsoft Edge',
                      use: { ...devices['Desktop Edge'], channel: 'msedge' },
                  },
              ]
            : []),

        // ── Mobile Devices ───────────────────────────────────────────────────────
        {
            name: 'iPhone 15',
            use: { ...devices['iPhone 15'] },
        },
        {
            name: 'Pixel 8',
            use: { ...devices['Pixel 8'] },
        },
        {
            name: 'Galaxy S24',
            use: { ...devices['Galaxy S24'] },
        },
        {
            name: 'iPad',
            use: { ...devices['iPad Pro'] },
        },
    ],
});
