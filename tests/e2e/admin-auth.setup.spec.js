const { test, expect } = require('@playwright/test');
const { authFile, loginAsAdmin } = require('./helpers/admin');

test('seeded admin can authenticate for browser specs', async ({ page }) => {
    await loginAsAdmin(page);
    await expect(page).toHaveURL(/\/admin$/);
    await page.context().storageState({ path: authFile });
});
