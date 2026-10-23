const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test.setTimeout(60_000);

function promoCodeValue() {
    return `P${Date.now().toString().slice(-5)}`;
}

async function setTextareaValue(page, selector, value) {
    await page.locator(selector).evaluate((element, nextValue) => {
        element.value = nextValue;
        element.dispatchEvent(new Event('input', { bubbles: true }));
        element.dispatchEvent(new Event('change', { bubbles: true }));
    }, value);
}

test('admin can create and update a promo code', async ({ page }) => {
    const code = promoCodeValue();
    const updatedCode = `${code.slice(0, 5)}Z`;

    await page.goto('/admin/promo-codes/create');
    await page.locator('input[name="code"]').fill(code);
    await page.locator('input[name="name"]').fill('Playwright Promo Code');
    await page.locator('select[name="discount_type"]').selectOption('percentage');
    await page.locator('input[name="discount_percent"]').fill('15');
    await page.locator('input[name="max_uses"]').fill('20');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/promo-codes$/);
    await searchTable(page, code);
    let row = await expectRow(page, code);

    await row.getByRole('link', { name: /edit/i }).click();
    await page.locator('input[name="code"]').fill(updatedCode);
    await page.locator('input[name="name"]').fill('Playwright Promo Code Updated');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedCode);
    await expectRow(page, updatedCode);
});

test('admin can create, update, and delete a frontend page', async ({ page }) => {
    const title = uniqueId('Playwright Info Page');
    const updatedTitle = `${title} Updated`;
    const slug = `playwright-page-${Date.now()}`;
    const updatedSlug = `${slug}-updated`;

    await page.goto('/admin/frontend/pages/create');
    await page.locator('#subtitle').fill('Playwright Subtitle');
    await page.locator('#title').fill(title);
    await page.locator('#title_suffix').fill('2026');
    await page.locator('#slug').fill(slug);
    await setTextareaValue(page, '#content', '<p>Playwright frontend page content.</p>');
    await page.locator('#sort_order').fill('4');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/frontend\/pages$/);
    await searchTable(page, title);
    let row = await expectRow(page, title);

    await row.getByLabel('Edit').click();
    await page.locator('#title').fill(updatedTitle);
    await page.locator('#slug').fill(updatedSlug);
    await setTextareaValue(page, '#content', '<p>Updated Playwright frontend page content.</p>');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedTitle);
    row = await expectRow(page, updatedTitle);
    await row.locator('input[type="checkbox"][name="ids[]"]').check();
    await acceptNextDialog(page);
    await page.locator('form[action$="/admin/frontend/pages/destroy"] button[type="submit"]').first().click();
    await expectRowMissing(page, updatedTitle);
});

test('admin can create, update, and delete a frontend SEO title', async ({ page }) => {
    const path = `/playwright-seo-${Date.now()}`;
    const updatedPath = `${path}-updated`;
    const title = uniqueId('Playwright SEO Title');
    const updatedTitle = `${title} Updated`;

    await page.goto('/admin/frontend/seo-titles/create');
    await page.locator('#path').fill(path);
    await page.locator('#title').fill(title);
    await page.locator('#is_active').check();
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/frontend\/seo-titles$/);
    await searchTable(page, path);
    let row = await expectRow(page, path);

    await row.getByRole('link', { name: /edit/i }).click();
    await page.locator('#path').fill(updatedPath);
    await page.locator('#title').fill(updatedTitle);
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedPath);
    row = await expectRow(page, updatedPath);
    await acceptNextDialog(page);
    await row.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedPath);
});

test('admin can create, update, and delete a download item', async ({ page }) => {
    const sectionTitle = uniqueId('Playwright Downloads');
    const itemTitle = uniqueId('Playwright Download Item');
    const updatedItemTitle = `${itemTitle} Updated`;

    await page.goto('/admin/download-media-items/create');
    await page.locator('#section_title').fill(sectionTitle);
    await page.locator('#audience_type').selectOption('general');
    await page.locator('#item_title').fill(itemTitle);
    await page.locator('#item_subtitle').fill('Subtitle');
    await page.locator('#sort_order').fill('2');
    await page.locator('#image_url').fill('https://example.com/download-item.webp');
    await page.locator('#primary_button_text').fill('Download PDF');
    await page.locator('#primary_file_url').fill('https://example.com/download.pdf');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/download-media-items$/);
    let row = page.locator('tbody tr').filter({ hasText: itemTitle }).first();
    await expect(row).toBeVisible();

    await row.getByLabel('Edit').click();
    await page.locator('#item_title').fill(updatedItemTitle);
    await page.locator('#secondary_button_text').fill('Download PNG');
    await page.locator('#secondary_file_url').fill('https://example.com/download.png');
    await page.getByRole('button', { name: /update/i }).click();

    row = page.locator('tbody tr').filter({ hasText: updatedItemTitle }).first();
    await expect(row).toBeVisible();
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expect(page.locator('tbody tr').filter({ hasText: updatedItemTitle })).toHaveCount(0);
});
