const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test('admin can create, update, and delete a speaker profile', async ({ page }) => {
    const name = uniqueId('Playwright Speaker');
    const updatedName = `${name} Updated`;
    const email = `${name.toLowerCase().replace(/[^a-z0-9]+/g, '-') }@example.com`;

    await page.goto('/admin/speakers/create');
    await page.locator('#name').fill(name);
    await page.locator('#designation').fill('QA Lead');
    await page.locator('#gender').selectOption('male');
    await page.locator('#company_name').fill('Automation Labs');
    await page.locator('#email').fill(email);
    await page.locator('#phone').fill('9999999999');
    await page.locator('#topic').fill('Playwright at scale');
    await page.locator('#topic_category').fill('Testing');
    await page.locator('#category').fill('Engineering');
    await page.locator('#sort_order').fill('11');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/speakers$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedName);
    await page.locator('#designation').fill('Principal QA Lead');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);

    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedName);
});

test('admin can create, update, and delete a my-contact record', async ({ page }) => {
    const name = uniqueId('Playwright Contact');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/my-contacts/create');
    await page.locator('input[name="name"]').fill(name);
    await page.locator('input[name="designation"]').fill('Partnership Manager');
    await page.locator('input[name="company_name"]').fill('Events Co');
    await page.locator('input[name="category"]').fill('Speaker');
    await page.locator('input[name="specialization"]').fill('Automation');
    await page.locator('input[name="speaker_tag"]').fill('VIP');
    await page.locator('input[name="country"]').fill('India');
    await page.locator('input[name="event_name"]').fill('IGI Summit');
    await page.locator('input[name="official_email"]').fill(`${name.toLowerCase().replace(/[^a-z0-9]+/g, '-') }@example.com`);
    await page.locator('input[name="mobile_number"]').fill('8888888888');
    await page.locator('select[name="contact_status"]').selectOption({ index: 0 });
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/my-contacts$/);
    await searchTable(page, name);
    let row = page.locator('.contact-card, tbody tr').filter({ hasText: name }).first();
    await expect(row).toBeVisible();

    await row.getByRole('link', { name: /^edit$/i }).click();
    await page.locator('input[name="name"]').fill(updatedName);
    await page.locator('input[name="company_name"]').fill('Events Company Updated');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedName);
    row = page.locator('.contact-card, tbody tr').filter({ hasText: updatedName }).first();
    await expect(row).toBeVisible();

    await acceptNextDialog(page);
    await row.getByRole('button', { name: /^delete$/i }).click();
    await expect(page.locator('.contact-card, tbody tr').filter({ hasText: updatedName })).toHaveCount(0);
});
