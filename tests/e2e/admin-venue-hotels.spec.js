const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test('admin can create, update, and delete a venue hotel with room inventory', async ({ page }) => {
    const name = uniqueId('Playwright Venue Hotel');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/venue-hotels/create');
    await page.locator('input[name="name"]').fill(name);
    await page.locator('input[name="stars"]').fill('5');
    await page.locator('input[name="sort_order"]').fill('7');
    await page.locator('#is_active').check();
    await page.locator('input[name="city"]').fill('Mumbai');
    await page.locator('input[name="country"]').fill('India');
    await page.locator('input[name="hotel_image"]').fill('images/misc/1.webp');
    await page.locator('textarea[name="hotel_address"]').fill('BKC, Mumbai, India');
    await page.locator('input[name="room_section_heading"]').fill('Choose Your Room');
    await page.locator('textarea[name="hotel_gallery_text"]').fill('images/misc/1.webp');
    await page.locator('input[name="booking_available_from"]').fill('2026-12-01');
    await page.locator('input[name="booking_available_to"]').fill('2026-12-31');
    await page.locator('input[name="direction_from"]').fill('Mumbai Airport');
    await page.locator('input[name="direction_to"]').fill('BKC, Mumbai');
    await page.locator('input[name="rooms[0][name]"]').fill('Deluxe Room');
    await page.locator('input[name="rooms[0][caption]"]').fill('Conference stay room');
    await page.locator('input[name="rooms[0][size]"]').fill('41 sqm');
    await page.locator('input[name="rooms[0][badge]"]').fill('Popular');
    await page.locator('input[name="rooms[0][single_rate_inr]"]').fill('13000');
    await page.locator('input[name="rooms[0][double_rate_inr]"]').fill('15000');
    await page.locator('input[name="rooms[0][single_rate_usd]"]').fill('180');
    await page.locator('input[name="rooms[0][double_rate_usd]"]').fill('210');
    await page.locator('textarea[name="rooms[0][benefits_text]"]').fill('Breakfast included\nAirport transfer');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/venue-hotels$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByRole('link', { name: /^edit$/i }).click();
    await page.locator('input[name="name"]').fill(updatedName);
    await page.locator('input[name="city"]').fill('Navi Mumbai');
    await page.locator('input[name="rooms[0][double_rate_inr]"]').fill('15500');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);
    await expect(row).toContainText('Navi Mumbai');

    await acceptNextDialog(page);
    await row.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedName);
});
