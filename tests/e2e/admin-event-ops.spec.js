const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

test.setTimeout(60_000);

test('admin can create, update, and delete an event with a ticket plan and linked agenda records', async ({ page }) => {
    const base = uniqueId('playwright-event');
    const eventTitle = `Playwright Event ${base}`;
    const updatedEventTitle = `${eventTitle} Updated`;
    const slug = base.toLowerCase();
    const updatedSlug = `${slug}-updated`;
    const dayTitle = `Day ${base}`;
    const updatedDayTitle = `${dayTitle} Updated`;
    const roomName = `Room ${base}`;
    const updatedRoomName = `${roomName} Updated`;
    const sessionTitle = `Session ${base}`;
    const updatedSessionTitle = `${sessionTitle} Updated`;

    const startDate = '2026-12-10';
    const endDate = '2026-12-11';

    await page.goto('/admin/events/create');
    await page.locator('#title').fill(eventTitle);
    await page.locator('#slug').fill(slug);
    await page.locator('#venue').fill('Mumbai Convention Center');
    await page.locator('#location').fill('Mumbai, India');
    await page.locator('#start_at').fill(startDate);
    await page.locator('#end_at').fill(endDate);
    await page.locator('#description').fill('Playwright event CRUD coverage');
    await page.locator('#ticket_hero_label').fill('PLAYWRIGHT TICKETS');
    await page.locator('#add-plan-btn').click();
    await page.locator('input[name="plans[0][name]"]').fill('Playwright VIP');
    await page.locator('input[name="plans[0][caption]"]').fill('VIP access for browser coverage');
    await page.locator('input[name="plans[0][mrp_inr]"]').fill('4999');
    await page.locator('input[name="plans[0][sale_inr]"]').fill('3999');
    await page.locator('input[name="plans[0][mrp_usd]"]').fill('99');
    await page.locator('input[name="plans[0][sale_usd]"]').fill('79');
    await page.locator('textarea[name="plans[0][benefits]"]').fill('Priority entry\nReserved seat');
    await page.locator('input[name="plans[0][sort_order]"]').fill('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/events$/);
    await searchTable(page, eventTitle);
    let row = await expectRow(page, eventTitle);

    await row.getByLabel('Edit').click();
    await page.locator('#title').fill(updatedEventTitle);
    await page.locator('#slug').fill(updatedSlug);
    await page.locator('input[name="plans[0][name]"]').fill('Playwright Elite');
    await page.locator('input[name="plans[0][sale_inr]"]').fill('3599');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedEventTitle);
    row = await expectRow(page, updatedEventTitle);

    await page.goto('/admin/agenda/days/create');
    await page.locator('#event_id').selectOption({ label: updatedEventTitle });
    await page.locator('#title').fill(dayTitle);
    await page.locator('#weekday').fill('Thursday');
    await page.locator('#agenda_date').fill(startDate);
    await page.locator('#sort_order').fill('1');
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, dayTitle);
    row = await expectRow(page, dayTitle);

    await row.getByRole('link', { name: /edit/i }).click();
    await page.locator('#title').fill(updatedDayTitle);
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, updatedDayTitle);
    await expectRow(page, updatedDayTitle);

    await page.goto('/admin/agenda/rooms/create');
    await page.locator('#event_id').selectOption({ label: updatedEventTitle });
    await page.locator('#name').fill(roomName);
    await page.locator('#sort_order').fill('1');
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, roomName);
    row = await expectRow(page, roomName);

    await row.getByRole('link', { name: /edit/i }).click();
    await page.locator('#name').fill(updatedRoomName);
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, updatedRoomName);
    await expectRow(page, updatedRoomName);

    await page.goto('/admin/agenda/items/create');
    await page.locator('#event_id').selectOption({ label: updatedEventTitle });
    await page.locator('#agenda_day_id').selectOption({ label: updatedDayTitle });
    await page.locator('#agenda_room_id').selectOption({ label: updatedRoomName });
    await page.locator('#title').fill(sessionTitle);
    await page.locator('#start_time').fill('09:00 AM');
    await page.locator('#end_time').fill('10:00 AM');
    await page.locator('#panel_theme').fill('Automation in events');
    await page.locator('#topic_description').fill('Agenda session created by Playwright');
    await page.locator('#sort_order').fill('1');
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, sessionTitle);
    row = await expectRow(page, sessionTitle);

    await row.getByRole('link', { name: /edit/i }).click();
    await page.locator('#title').fill(updatedSessionTitle);
    await page.getByRole('button', { name: /save/i }).click();
    await searchTable(page, updatedSessionTitle);
    let sessionRow = await expectRow(page, updatedSessionTitle);

    await acceptNextDialog(page);
    await sessionRow.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedSessionTitle);

    await page.goto('/admin/agenda/rooms');
    await searchTable(page, updatedRoomName);
    let roomRow = await expectRow(page, updatedRoomName);
    await acceptNextDialog(page);
    await roomRow.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedRoomName);

    await page.goto('/admin/agenda/days');
    await searchTable(page, updatedDayTitle);
    let dayRow = await expectRow(page, updatedDayTitle);
    await acceptNextDialog(page);
    await dayRow.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedDayTitle);

    await page.goto('/admin/events');
    await searchTable(page, updatedEventTitle);
    const updatedEventRow = await expectRow(page, updatedEventTitle);
    await acceptNextDialog(page);
    await updatedEventRow.getByLabel('Delete').click();
    await expectRowMissing(page, updatedEventTitle);
});

test('admin can create, update, and delete a world event', async ({ page }) => {
    const name = uniqueId('Playwright World Event');
    const updatedName = `${name} Updated`;

    await page.goto('/admin/world-events/create');
    await page.locator('input[name="name"]').fill(name);
    await page.locator('#world-event-start-date').fill('2026-11-05');
    await page.locator('#world-event-end-date').fill('2026-11-07');
    await page.locator('input[name="city"]').fill('Singapore');
    await page.locator('input[name="country"]').fill('Singapore');
    await page.locator('input[name="theme"]').fill('Automation');
    await page.locator('input[name="website"]').fill('https://example.com/world-event');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/world-events$/);
    await searchTable(page, name);
    let row = await expectRow(page, name);

    await row.getByRole('link', { name: /^edit$/i }).click();
    await page.locator('input[name="name"]').fill(updatedName);
    await page.locator('input[name="theme"]').fill('Automation Updated');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, updatedName);
    row = await expectRow(page, updatedName);

    await acceptNextDialog(page);
    await row.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, updatedName);
});
