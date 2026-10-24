const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
    testDir: __dirname,
    testMatch: 'train-ui.spec.js',
    workers: 1,
    retries: 0,
    reporter: 'list',
    use: {
        baseURL: 'http://smartseat.test',
        launchOptions: { executablePath: process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE || undefined },
    },
    projects: [
        { name: 'desktop', use: { viewport: { width: 1440, height: 1000 } } },
        { name: 'mobile', use: { ...devices['iPhone 13'], browserName: 'chromium' } },
    ],
});
