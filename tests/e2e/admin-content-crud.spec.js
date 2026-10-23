const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test.setTimeout(60_000);

test('admin can create, update, and delete a sponsor category', async ({ page }) => {
    const name = uniqueId('Playwright Sponsor Category');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/sponsor-categories/create');
    await page.locator('#name').fill(name);
    await page.locator('#sort_order').fill('42');
    await page.locator('#logo_size').selectOption('lg');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/sponsor-categories$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedName);
    await page.locator('#sort_order').fill('43');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);

    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedName);
});

test('admin can create, update, and delete FAQ content with a UI-created category', async ({ page }) => {
    const categoryName = uniqueId('Playwright FAQ Category');
    const question = uniqueId('How does Playwright FAQ CRUD work');
    const updatedQuestion = `${question} Updated`;

    await page.goto('/admin/faq-categories/create');
    await page.locator('#name').fill(categoryName);
    await page.locator('#sort_order').fill('8');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, categoryName);
    await expectRow(page, categoryName);

    await page.goto('/admin/faqs/create');
    await page.locator('#faq_category_id').selectOption({ label: categoryName });
    await page.locator('#question').fill(question);
    await page.locator('#answer').fill('This FAQ was created by the Playwright admin suite.');
    await page.locator('#sort_order').fill('3');
    await page.locator('#show_on_homepage').check();
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/faqs$/);
    await searchTable(page, question);
    let row = await expectRow(page, question);

    await row.getByLabel('Edit').click();
    await page.locator('#question').fill(updatedQuestion);
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedQuestion);
    row = await expectRow(page, updatedQuestion);

    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedQuestion);
});
