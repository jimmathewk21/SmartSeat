const path = require('path');
const { expect } = require('@playwright/test');

const authFile = path.join(__dirname, '..', '.auth', 'admin.json');

const adminCredentials = {
    email: process.env.PLAYWRIGHT_ADMIN_EMAIL || 'admin@admin.com',
    password: process.env.PLAYWRIGHT_ADMIN_PASSWORD || 'password',
};

const adminModules = [
    { label: 'Users', path: '/admin/users' },
    { label: 'Login Attempts', path: '/admin/login-activities' },
    { label: 'Roles', path: '/admin/roles' },
    { label: 'Permissions', path: '/admin/permissions' },
    { label: 'Header', path: '/admin/frontend/header' },
    { label: 'Sign In/Out Pages', path: '/admin/frontend/auth-page' },
    { label: 'Footer', path: '/admin/frontend/footer' },
    { label: 'Pages', path: '/admin/frontend/pages' },
    { label: 'Page Access', path: '/admin/frontend/page-access' },
    { label: 'Page Titles', path: '/admin/frontend/seo-titles' },
    { label: 'Download Page', path: '/admin/frontend/download' },
    { label: 'Download Items', path: '/admin/download-media-items' },
    { label: "FAQ's", path: '/admin/faqs' },
    { label: 'Asked Questions', path: '/admin/faq-questions' },
    { label: 'FAQ Categories', path: '/admin/faq-categories' },
    { label: 'Members', path: '/admin/members' },
    { label: 'Contact Us', path: '/admin/frontend/contact' },
    { label: 'City', path: '/admin/cities' },
    { label: 'State', path: '/admin/states' },
    { label: 'Country', path: '/admin/countries' },
    { label: 'My Contacts', path: '/admin/my-contacts' },
    { label: 'Settings', path: '/admin/settings' },
];

const frontendRoutes = [
    { label: 'Home', path: '/' },
    { label: 'FAQs', path: '/faqs' },
    { label: 'Contact Us', path: '/contact-us' },
    { label: 'Download', path: '/download' },
];

async function loginAsAdmin(page) {
    await page.goto('/login');
    await expect(page.locator('#email')).toBeVisible();
    await page.locator('#email').fill(adminCredentials.email);
    await page.locator('#password').fill(adminCredentials.password);
    await page.getByRole('button', { name: /sign in/i }).click();
    await expect(page).toHaveURL(/\/admin$/);
    await expect(page.locator('#sidebarMenuSearch')).toBeVisible();
    await expect(page.locator('#sidebar')).toContainText('Dashboard');
}

async function logout(page) {
    await page.getByRole('link', { name: /sign out/i }).click();
    await expect(page).toHaveURL(/\/($|login$)/);
}

async function expectPageHealthy(page, response, label) {
    expect(response, `${label} should return a response`).not.toBeNull();
    expect(response.ok(), `${label} should respond without server error`).toBeTruthy();
    await page.waitForLoadState('domcontentloaded');
    await expect(page.locator('body')).not.toContainText(/403 Forbidden|404 Not Found|500 Server Error|Whoops, looks like something went wrong|Fatal error/i);
}

async function acceptNextDialog(page) {
    page.once('dialog', async (dialog) => {
        await dialog.accept();
    });
}

function uniqueId(prefix) {
    return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
}

async function searchTable(page, value, selector = 'input[name="search"]') {
    const input = page.locator(`${selector}:visible`).first();
    await expect(input).toBeVisible();
    await input.fill(value);
    await input.press('Enter');
    await page.waitForLoadState('networkidle');
}

async function expectRow(page, value) {
    const row = page.locator('tbody tr').filter({ hasText: value }).first();
    await expect(row).toBeVisible();
    return row;
}

async function expectRowMissing(page, value) {
    await expect(page.locator('tbody tr').filter({ hasText: value })).toHaveCount(0);
}

module.exports = {
    acceptNextDialog,
    adminCredentials,
    adminModules,
    authFile,
    expectPageHealthy,
    expectRow,
    expectRowMissing,
    frontendRoutes,
    loginAsAdmin,
    logout,
    searchTable,
    uniqueId,
};
