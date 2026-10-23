const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectPageHealthy,
    expectRow,
    expectRowMissing,
    searchTable,
    uniqueId,
} = require('./helpers/admin');

const baseURL = process.env.PLAYWRIGHT_BASE_URL || `http://127.0.0.1:${process.env.PLAYWRIGHT_PORT || '8000'}`;

async function createGuestPage(browser) {
    const context = await browser.newContext({
        baseURL,
        storageState: undefined,
    });

    return {
        context,
        page: await context.newPage(),
    };
}

async function createFaqContent(page, categoryName, question) {
    await page.goto('/admin/faq-categories/create');
    await page.locator('#name').fill(categoryName);
    await page.locator('#sort_order').fill('9');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, categoryName);
    await expectRow(page, categoryName);

    await page.goto('/admin/faqs/create');
    await page.locator('#faq_category_id').selectOption({ label: categoryName });
    await page.locator('#question').fill(question);
    await page.locator('#answer').fill('This FAQ entry exists so the public FAQ form renders during Playwright coverage.');
    await page.locator('#sort_order').fill('4');
    await page.locator('#is_active').selectOption('1');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/faqs$/);
    await searchTable(page, question);
    await expectRow(page, question);
}

async function cleanupFaqContent(page, categoryName, question) {
    await page.goto('/admin/faqs');
    await searchTable(page, question);
    const faqRow = await expectRow(page, question);
    await acceptNextDialog(page);
    await faqRow.getByRole('button', { name: /^delete$/i }).click();
    await expectRowMissing(page, question);

    await page.goto('/admin/faq-categories');
    await searchTable(page, categoryName);
    const categoryRow = await expectRow(page, categoryName);
    await acceptNextDialog(page);
    await categoryRow.getByRole('button', { name: /^delete$/i }).click();
}

async function createVenueHotel(page, hotelName) {
    await page.goto('/admin/venue-hotels/create');
    await page.locator('input[name="name"]').fill(hotelName);
    await page.locator('input[name="stars"]').fill('4');
    await page.locator('input[name="sort_order"]').fill('11');
    await page.locator('#is_active').check();
    await page.locator('input[name="city"]').fill('Delhi');
    await page.locator('input[name="country"]').fill('India');
    await page.locator('input[name="hotel_image"]').fill('images/misc/1.webp');
    await page.locator('textarea[name="hotel_address"]').fill('Aerocity, New Delhi');
    await page.locator('input[name="room_section_heading"]').fill('Choose Your Room');
    await page.locator('textarea[name="hotel_gallery_text"]').fill('images/misc/1.webp');
    await page.locator('input[name="booking_available_from"]').fill('2026-12-01');
    await page.locator('input[name="booking_available_to"]').fill('2026-12-31');
    await page.locator('input[name="direction_from"]').fill('Delhi Airport');
    await page.locator('input[name="direction_to"]').fill('Aerocity, New Delhi');
    await page.locator('input[name="rooms[0][name]"]').fill('Executive Room');
    await page.locator('input[name="rooms[0][caption]"]').fill('Room reserved by Playwright');
    await page.locator('input[name="rooms[0][size]"]').fill('38 sqm');
    await page.locator('input[name="rooms[0][single_rate_inr]"]').fill('12000');
    await page.locator('input[name="rooms[0][double_rate_inr]"]').fill('14500');
    await page.locator('input[name="rooms[0][single_rate_usd]"]').fill('170');
    await page.locator('input[name="rooms[0][double_rate_usd]"]').fill('205');
    await page.locator('textarea[name="rooms[0][benefits_text]"]').fill('Breakfast\nWi-Fi');
    await page.getByRole('button', { name: /save/i }).click();

    await searchTable(page, hotelName);
    const row = await expectRow(page, hotelName);
    const viewHref = await row.getByRole('link', { name: /^view$/i }).getAttribute('href');
    const hotelId = viewHref ? viewHref.match(/\/admin\/venue-hotels\/(\d+)/)?.[1] : null;

    expect(hotelId).toBeTruthy();

    return hotelId;
}

async function createTicketEvent(page, eventTitle, slug) {
    await page.goto('/admin/events/create');
    await page.locator('#title').fill(eventTitle);
    await page.locator('#slug').fill(slug);
    await page.locator('#venue').fill('Bengaluru Convention Center');
    await page.locator('#location').fill('Bengaluru, India');
    await page.locator('#start_at').fill('2026-12-15');
    await page.locator('#end_at').fill('2026-12-16');
    await page.locator('#description').fill('Public ticketing boundary flow event created by Playwright.');
    await page.locator('#ticket_hero_label').fill('PLAYWRIGHT TICKETS');
    await page.locator('#is_published').selectOption('1');
    await page.locator('#add-plan-btn').click();
    await page.locator('input[name="plans[0][name]"]').fill('Playwright Pass');
    await page.locator('input[name="plans[0][caption]"]').fill('Public ticketing boundary validation');
    await page.locator('input[name="plans[0][mrp_inr]"]').fill('4999');
    await page.locator('input[name="plans[0][sale_inr]"]').fill('3999');
    await page.locator('input[name="plans[0][mrp_usd]"]').fill('99');
    await page.locator('input[name="plans[0][sale_usd]"]').fill('79');
    await page.locator('textarea[name="plans[0][benefits]"]').fill('Main conference access\nNetworking sessions');
    await page.locator('input[name="plans[0][valid_till_text]"]').fill('Valid till conference week');
    await page.getByRole('button', { name: /save/i }).click();

    await expect(page).toHaveURL(/\/admin\/events$/);
    await searchTable(page, eventTitle);
    await expectRow(page, eventTitle);
}

async function cleanupEvent(page, eventTitle) {
    await page.goto('/admin/events');
    await searchTable(page, eventTitle);
    const row = await expectRow(page, eventTitle);
    await acceptNextDialog(page);
    await row.getByLabel('Delete').click();
    await expectRowMissing(page, eventTitle);
}

test('guest can submit the public contact form', async ({ browser }) => {
    const { context, page } = await createGuestPage(browser);

    try {
        const response = await page.goto('/contact-us');
        await expectPageHealthy(page, response, 'Contact Us');
        await page.locator('input[name="name"]').fill('Playwright Contact User');
        await page.locator('input[name="email"]').fill('contact@example.com');
        await page.locator('input[name="subject"]').fill('Playwright coverage check');
        await page.locator('textarea[name="message"]').fill('Verifying that the public contact form submits successfully.');
        await page.getByRole('button', { name: /submit/i }).click();

        await expect(page).toHaveURL(/\/contact-us$/);
        await expect(page.locator('.alert-success')).toBeVisible();
    } finally {
        await context.close();
    }
});

test('guest can submit a FAQ question when public FAQ content exists', async ({ page, browser }) => {
    const categoryName = uniqueId('Playwright FAQ Public Category');
    const question = uniqueId('Public FAQ Seed');

    await createFaqContent(page, categoryName, question);

    const { context, page: guestPage } = await createGuestPage(browser);

    try {
        const response = await guestPage.goto('/faqs');
        await expectPageHealthy(guestPage, response, 'FAQs');
        await guestPage.locator('input[name="name"]').fill('Playwright FAQ User');
        await guestPage.locator('input[name="email"]').fill('faq@example.com');
        await guestPage.locator('textarea[name="question"]').fill('Can I verify the FAQ question form with Playwright?');
        await guestPage.getByRole('button', { name: /send question/i }).click();

        await expect(guestPage).toHaveURL(/\/faqs$/);
        await expect(guestPage.locator('.faq-submit-feedback')).toContainText(/submitted successfully/i);
    } finally {
        await context.close();
    }

    await cleanupFaqContent(page, categoryName, question);
});

test('guest can browse a venue hotel and submit a reservation request', async ({ page, browser }) => {
    const hotelName = uniqueId('Playwright Public Venue Hotel');
    const hotelId = await createVenueHotel(page, hotelName);

    const { context, page: guestPage } = await createGuestPage(browser);

    try {
        let response = await guestPage.goto(`/venue/${hotelId}`);
        await expectPageHealthy(guestPage, response, hotelName);
        await expect(guestPage.locator('body')).toContainText(hotelName);
        await guestPage.getByRole('link', { name: /select room/i }).first().click();

        await expect(guestPage).toHaveURL(new RegExp(`/venue/${hotelId}/reserve\\?room=0`));
        await guestPage.locator('input[name="full_name"]').fill('Playwright Hotel Guest');
        await guestPage.locator('input[name="email"]').fill('hotelguest@gmail.com');
        await guestPage.locator('input[name="phone"]').fill('9999999999');
        await guestPage.locator('select[name="country"]').selectOption('India');
        await guestPage.locator('input[name="number_of_rooms"]').fill('1');
        await guestPage.locator('input[name="check_in_date"]').fill('2026-12-10');
        await guestPage.locator('input[name="check_out_date"]').fill('2026-12-12');
        await guestPage.locator('select[name="occupancy"]').selectOption('double');
        await guestPage.locator('select[name="bed_size"]').selectOption('King Size Bed');
        await guestPage.locator('select[name="preference"]').selectOption('Non-Smoking Room');
        await guestPage.locator('textarea[name="special_requests"]').fill('High floor if available.');
        await guestPage.getByRole('button', { name: /submit reservation/i }).click();

        await expect(guestPage).toHaveURL(new RegExp(`/venue/${hotelId}/reserve\\?room=0`));
        await expect(guestPage.locator('.alert-success')).toContainText(/thank you for submitting your details/i);
    } finally {
        await context.close();
    }
});

test('guest can view public ticket plans and is gated at the authenticated checkout step', async ({ page, browser }) => {
    const slug = uniqueId('playwright-ticket-boundary').toLowerCase().replace(/[^a-z0-9]+/g, '-');
    const eventTitle = `Playwright Ticket Boundary ${slug}`;

    await createTicketEvent(page, eventTitle, slug);

    const { context, page: guestPage } = await createGuestPage(browser);

    try {
        const response = await guestPage.goto(`/events/${slug}/tickets`);
        expect(response).not.toBeNull();
        await expect(guestPage).toHaveURL(/\/login$/);
        await expect(guestPage.locator('body')).toContainText('Please sign in to access this page.');
    } finally {
        await context.close();
    }

    await cleanupEvent(page, eventTitle);
});
