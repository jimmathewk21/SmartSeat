const { test, expect } = require('@playwright/test');
const {
    acceptNextDialog,
    expectPageHealthy,
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
