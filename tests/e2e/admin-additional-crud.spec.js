const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test.setTimeout(60_000);

test('admin can create, update, and delete a member', async ({ page }) => {
    const name = uniqueId('Playwright Member');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/members/create');
    await page.locator('#name').fill(name);
    await page.locator('#photo_url').fill('https://example.com/member.webp');
    await page.locator('#designation').fill('Board Member');
    await page.locator('#company').fill('Events Council');
    await page.locator('#sort_order').fill('5');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/members$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedName);
    await page.locator('#company').fill('Events Council Updated');
    await page.getByRole('button', { name: /update/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);
    await row.locator('input[type="checkbox"][name="ids[]"]').check();
    await acceptNextDialog(page);
    await page.locator('form[action$="/admin/members/destroy"] button[type="submit"]').first().click();
    await expectRowMissing(page, updatedName);
});
