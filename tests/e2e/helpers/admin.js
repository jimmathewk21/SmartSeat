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
    { label: 'Homepage', path: '/admin/frontend/home' },
    { label: 'Sign In/Out Pages', path: '/admin/frontend/auth-page' },
    { label: 'Footer', path: '/admin/frontend/footer' },
    { label: 'Pages', path: '/admin/frontend/pages' },
    { label: 'Page Access', path: '/admin/frontend/page-access' },
    { label: 'Page Titles', path: '/admin/frontend/seo-titles' },
    { label: 'Speakers Page', path: '/admin/speakers/page-settings' },
    { label: 'Speakers', path: '/admin/speakers' },
    { label: 'Speaker Applications', path: '/admin/speaker-applications' },
    { label: 'Sponsors Page', path: '/admin/sponsors/page-settings' },
    { label: 'Sponsor Categories', path: '/admin/sponsor-categories' },
    { label: 'Sponsors', path: '/admin/sponsors' },
    { label: 'Sponsor Enquiries', path: '/admin/sponsor-inquiries' },
    { label: 'Agenda Page', path: '/admin/agenda/page-settings' },
    { label: 'Agenda Days', path: '/admin/agenda/days' },
    { label: 'Agenda Rooms', path: '/admin/agenda/rooms' },
    { label: 'Agenda Sessions', path: '/admin/agenda/items' },
    { label: 'Ticket Bookings', path: '/admin/event-bookings' },
    { label: 'Ticket Packages', path: '/admin/events' },
    { label: 'Promo Code', path: '/admin/promo-codes' },
    { label: 'Ticket Checkout Settings', path: '/admin/settings/ticket-checkout' },
    { label: 'Download Page', path: '/admin/frontend/download' },
    { label: 'Download Items', path: '/admin/download-media-items' },
    { label: "FAQ's", path: '/admin/faqs' },
    { label: 'Asked Questions', path: '/admin/faq-questions' },
    { label: 'FAQ Categories', path: '/admin/faq-categories' },
    { label: 'Past & Upcoming Events', path: '/admin/past-upcoming-events' },
    { label: 'Members', path: '/admin/members' },
    { label: 'Attractions', path: '/admin/attractions' },
    { label: 'Testimonials', path: '/admin/testimonials' },
    { label: 'Contact Us', path: '/admin/frontend/contact' },
    { label: 'City', path: '/admin/cities' },
    { label: 'State', path: '/admin/states' },
    { label: 'Country', path: '/admin/countries' },
    { label: 'Venue Page', path: '/admin/venue/page-settings' },
    { label: 'Reservation Page', path: '/admin/venue/reservation-settings' },
    { label: 'Tax Option', path: '/admin/venue/tax-settings' },
    { label: 'Manage Hotels/Rooms', path: '/admin/venue-hotels' },
    { label: 'Hotel Bookings', path: '/admin/hotel-bookings' },
    { label: 'World Events', path: '/admin/world-events' },
    { label: 'My Contacts', path: '/admin/my-contacts' },
    { label: 'Settings', path: '/admin/settings' },
];

const frontendRoutes = [
    { label: 'Home', path: '/' },
    { label: 'Speakers', path: '/speakers' },
    { label: 'Sponsors', path: '/sponsors' },
    { label: 'Events', path: '/events' },
    { label: 'World Events', path: '/world-events' },
    { label: 'Agenda', path: '/agenda' },
    { label: 'Attractions', path: '/attractions' },
    { label: 'Things To Do', path: '/things-to-do' },
    { label: 'FAQs', path: '/faqs' },
    { label: 'Testimonials', path: '/testimonials' },
    { label: 'Contact Us', path: '/contact-us' },
    { label: 'Download', path: '/download' },
    { label: 'Venue', path: '/venue' },
    { label: 'Hotel Booking Alias', path: '/hotel-booking' },
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
