const { defineConfig, devices } = require('@playwright/test');
const path = require('path');

const PORT = process.env.PLAYWRIGHT_PORT || '8000';
const baseURL = process.env.PLAYWRIGHT_BASE_URL || `http://127.0.0.1:${PORT}`;

module.exports = defineConfig({
    testDir: path.join(__dirname, 'tests/e2e'),
    fullyParallel: false,
    workers: 1,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    reporter: [['list'], ['html', { open: 'never' }]],
    use: {
        baseURL,
        trace: 'retain-on-failure',
        screenshot: 'only-on-failure',
        video: 'retain-on-failure',
    },
    projects: [
        {
            name: 'setup',
            testMatch: /.*\.setup\.spec\.js/,
        },
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                storageState: path.join(__dirname, 'tests/e2e/.auth/admin.json'),
            },
            dependencies: ['setup'],
            testIgnore: /.*\.setup\.spec\.js/,
        },
    ],
    webServer: {
        command: `php artisan serve --host=127.0.0.1 --port=${PORT}`,
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 120 * 1000,
    },
});
