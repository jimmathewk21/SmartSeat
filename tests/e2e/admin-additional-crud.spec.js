const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test.setTimeout(60_000);

async function selectFirstOptionValue(page, selector) {
    const options = page.locator(`${selector} option`);
    const count = await options.count();

    for (let index = 0; index < count; index += 1) {
        const value = await options.nth(index).getAttribute('value');
        if (value) {
            await page.locator(selector).selectOption(value);
            return;
        }
    }

    throw new Error(`No selectable option found for ${selector}`);
}

test('admin can create, update, and delete a sponsor with a dedicated sponsor category', async ({ page }) => {
    const categoryName = uniqueId('Playwright Sponsor Group');
    const sponsorName = uniqueId('Playwright Sponsor');
    const updatedSponsorName = `${sponsorName} Updated`;

    await page.goto('/admin/sponsor-categories/create');
    await page.locator('#name').fill(categoryName);
    await page.locator('#sort_order').fill('21');
    await page.locator('#logo_size').selectOption('md');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await page.goto('/admin/sponsors/create');
    await page.locator('#name').fill(sponsorName);
    await page.locator('#sub_category').fill('PW Sponsor');
    await page.locator('#sort_order').fill('7');
    await page.locator('#sponsor_category_id').selectOption({ label: categoryName });
    await page.locator('#website_url').fill('https://example.com/sponsor');
    await page.locator('#contact_name').fill('Automation Contact');
    await page.locator('#contact_email').fill('sponsor@example.com');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/sponsors$/);
    await searchTable(page, sponsorName);
    let row = await expectRow(page, sponsorName);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedSponsorName);
    await page.locator('#website_url').fill('https://example.com/sponsor-updated');
    await page.getByRole('button', { name: /update/i }).click();

    await searchTable(page, updatedSponsorName);
    row = await expectRow(page, updatedSponsorName);
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedSponsorName);

    await page.goto('/admin/sponsor-categories');
    await searchTable(page, categoryName);
    row = await expectRow(page, categoryName);
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, categoryName);
});

test('admin can create, update, and delete a testimonial', async ({ page }) => {
    const name = uniqueId('Playwright Testimonial');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/testimonials/create');
    await page.locator('#name').fill(name);
    await page.locator('#photo_url').fill('https://example.com/testimonial.webp');
    await page.locator('#company').fill('Automation Labs');
    await page.locator('#designation').fill('Director');
    await selectFirstOptionValue(page, '#country_id');
    await page.locator('#year').fill('2026');
    await page.locator('#message').fill('Playwright testimonial browser coverage.');
    await page.locator('#experience_word').fill('Excellent');
    await page.locator('#stars_highly_recommended').check();
    await page.locator('#show_on_homepage').check();
    await page.locator('#is_active').selectOption('1');
    await page.locator('#sort_order').fill('9');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/testimonials$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedName);
    await page.locator('#message').fill('Playwright testimonial browser coverage updated.');
    await page.getByRole('button', { name: /update/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedName);
});

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

test('admin can create, update, and delete an attraction', async ({ page }) => {
    const name = uniqueId('Playwright Attraction');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/attractions/create');
    await page.locator('#name').fill(name);
    await page.locator('#category').fill('Dining');
    await page.locator('#sort_order').fill('12');
    await page.locator('#image_url').fill('https://example.com/attraction.webp');
    await page.locator('#website_url').fill('https://example.com/attraction');
    await page.locator('#instagram_url').fill('https://instagram.com/attraction');
    await page.locator('#direction_url').fill('https://maps.google.com/?q=attraction');
    await page.locator('#detail').fill('Attraction created by Playwright.');
    await page.locator('#show_on_homepage').check();
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/attractions$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByLabel('Edit').click();
    await page.locator('#name').fill(updatedName);
    await page.locator('#detail').fill('Attraction updated by Playwright.');
    await page.getByRole('button', { name: /update/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, updatedName);
});
