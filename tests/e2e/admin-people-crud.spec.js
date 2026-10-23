const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

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
