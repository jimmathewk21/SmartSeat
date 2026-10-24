const { test, expect } = require('@playwright/test');
const { execFileSync } = require('node:child_process');
const fs = require('node:fs/promises');
const path = require('node:path');
const root = path.resolve(__dirname, '../..');

// Real Blade output and assets, intercepted locally; no application server or database.
test.beforeAll(() => {
    const output = execFileSync('php', ['tests/Support/render-train-ui-fixtures.php'], {
        cwd: root,
        env: { ...process.env, APP_ENV: 'testing', DB_CONNECTION: 'mysql', DB_DATABASE: 'smartseat_train_test', SESSION_DRIVER: 'array' },
        stdio: 'pipe',
    });
    if (!output.toString().includes('Rendered five UI fixtures')) throw new Error(output.toString());
});
test.beforeEach(async ({ page }) => {
    await page.route('http://smartseat.test/**', async (route) => {
        const url = new URL(route.request().url());
        if (route.request().method() !== 'GET') return route.abort();
        const fixtures = { '/': 'home', '/trains': 'results', '/my-train-bookings': 'bookings', '/empty-results': 'empty-results', '/no-stations': 'no-stations' };
        const file = fixtures[url.pathname]
            ? path.join(root, 'test-results/train-ui-fixtures', `${fixtures[url.pathname]}.html`)
            : path.join(root, 'public', url.pathname);
        const types = { '.html': 'text/html', '.css': 'text/css', '.js': 'application/javascript', '.webp': 'image/webp', '.svg': 'image/svg+xml' };
        try { await route.fulfill({ body: await fs.readFile(file), contentType: types[path.extname(file)] || 'application/octet-stream' }); }
        catch { await route.fulfill({ status: 404, body: 'Fixture resource not found' }); }
    });
});

async function noOverflow(page) {
    expect(await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth)).toBeTruthy();
}

test('keyboard autocomplete and swap submit the existing station IDs', async ({ page }, testInfo) => {
    const errors = [];
    page.on('pageerror', (error) => errors.push(error.message));
    await page.goto('/');
    await expect(page.getByRole('heading', { name: /Find your way/ })).toBeVisible();
    const from = page.getByRole('combobox', { name: 'From station' });
    const to = page.getByRole('combobox', { name: 'To station' });
    await from.fill('CSMT');
    await from.press('ArrowDown');
    await from.press('Enter');
    await to.fill('Pune');
    await to.press('Enter');
    await page.getByRole('button', { name: 'Swap origin and destination' }).click();
    await expect(page.locator('#from_station_id')).toHaveValue('204');
    await expect(page.locator('#to_station_id')).toHaveValue('103');
    await expect(from).toHaveValue(/Pune Junction/);
    await from.press('Escape');
    await expect(from).toHaveAttribute('aria-expanded', 'false');
    await noOverflow(page);
    for (const element of await page.locator('[data-reveal]').all()) {
        await element.scrollIntoViewIfNeeded();
        await expect(element).toHaveCSS('opacity', '1');
    }
    await expect.poll(() => page.locator('img[data-rail-image]').evaluateAll((images) =>
        images.every((image) => image.complete && image.naturalWidth > 0))).toBeTruthy();
    await page.evaluate(() => window.scrollTo(0, 0));
    await page.screenshot({ path: testInfo.outputPath('homepage.png'), fullPage: true });
    await page.getByRole('button', { name: 'Search trains' }).click();
    const url = new URL(page.url());
    expect([...url.searchParams.keys()].sort()).toEqual(['date', 'from_station_id', 'to_station_id']);
    expect(url.searchParams.get('from_station_id')).toBe('204');
    expect(url.searchParams.get('to_station_id')).toBe('103');
    expect(errors).toEqual([]);
});

test('free text is rejected and suggestion dismissal is accessible', async ({ page }) => {
    await page.goto('/');
    const from = page.getByRole('combobox', { name: 'From station' });
    await from.fill('Not a catalog station');
    await expect(page.getByText('No matching stations.')).toBeVisible();
    expect(await from.evaluate((input) => input.checkValidity())).toBeFalsy();
    await expect(page.locator('#from_station_id')).toHaveValue('');
    await from.press('Tab');
    await expect(from).toHaveAttribute('aria-expanded', 'false');
});

test('seat selection, passengers and journey fields retain their contracts', async ({ page }, testInfo) => {
    await page.goto('/trains');
    const nameBox = await page.locator('.journey-train-info h3').boundingBox();
    const timetableBox = await page.locator('.journey-timeline').boundingBox();
    expect(nameBox.y + nameBox.height <= timetableBox.y || nameBox.x + nameBox.width <= timetableBox.x).toBeTruthy();
    await page.getByText('Choose seats', { exact: false }).click();
    const form = page.locator('form[data-passenger-form]').first();
    await form.locator('.train-seat-option').first().click();
    await expect(form.locator('[data-seat-feedback]')).toHaveText('1 seat selected for 1 passenger.');
    await form.getByRole('button', { name: /Add passenger/ }).click();
    await form.locator('.train-seat-option').nth(1).click();
    await expect(form.locator('[data-seat-feedback]')).toHaveText('2 seats selected for 2 passengers.');
    await expect(form.locator('[name="boarding_station_id"]')).toHaveValue('103');
    await expect(form.locator('[name="destination_station_id"]')).toHaveValue('204');
    await expect(form.locator('[name="_token"]')).toHaveValue('ui-fixture-csrf-token');
    await expect(form.locator('[name="inventory_ids[]"]:checked')).toHaveCount(2);
    await noOverflow(page);
    await page.screenshot({ path: testInfo.outputPath('seat-selection.png'), fullPage: true });
    await page.getByText('Waitlist options', { exact: false }).click();
    await expect(page.getByText('Waiting requests are considered')).toBeVisible();
    await noOverflow(page);
});

test('booking confirmation and pending waitlist stay distinct', async ({ page }, testInfo) => {
    await page.goto('/my-train-bookings');
    await expect(page.getByRole('heading', { name: 'Your seats are reserved' })).toBeVisible();
    await expect(page.getByText('SS-UI-FIXTURE').first()).toBeVisible();
    await expect(page.getByText('This is not a confirmed booking.', { exact: false })).toBeVisible();
    await expect(page.getByRole('button', { name: 'Cancel booking', exact: true })).toBeVisible();
    await expect(page.getByRole('link', { name: 'Profile', exact: true })).toBeVisible();
    await noOverflow(page);
    await page.screenshot({ path: testInfo.outputPath('bookings.png'), fullPage: true });
});

test('reduced motion, failed images and empty catalog stay usable', async ({ page }) => {
    await page.emulateMedia({ reducedMotion: 'reduce' });
    await page.route('**/images/railway/jaipur.webp', (route) => route.abort());
    await page.goto('/');
    await expect(page.locator('img[alt*="Hawa Mahal"]')).toBeHidden();
    await expect(page.getByRole('heading', { name: 'Jaipur', exact: true })).toBeVisible();
    expect(await page.locator('.train-hero-copy').evaluate((node) => getComputedStyle(node).animationName)).toBe('none');
    await page.goto('/no-stations');
    await expect(page.getByRole('status')).toContainText('No stations are available');
    await expect(page.locator('#from_station_id')).toBeVisible();
    await page.goto('/empty-results');
    await expect(page.getByRole('heading', { name: 'No journeys found for this search' })).toBeVisible();
    await noOverflow(page);
});

test.describe('without JavaScript', () => {
    test.use({ javaScriptEnabled: false });
    test('native station selects still submit the original query', async ({ page }) => {
        await page.goto('/');
        await page.getByLabel('From station', { exact: true }).selectOption('103');
        await page.getByLabel('To station', { exact: true }).selectOption('204');
        await expect(page.getByRole('heading', { name: 'Jaipur', exact: true })).toBeVisible();
        await page.getByRole('button', { name: 'Search trains' }).click();
        expect(new URL(page.url()).searchParams.get('from_station_id')).toBe('103');
        await noOverflow(page);
    });
});
