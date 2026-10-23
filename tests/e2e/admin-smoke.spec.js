const { test, expect } = require('@playwright/test');
const { expectPageHealthy, frontendRoutes, loginAsAdmin, logout } = require('./helpers/admin');

test('guest is redirected away from admin routes', async ({ browser }) => {
    const context = await browser.newContext({ storageState: undefined });
    const page = await context.newPage();

    await page.goto('/admin');
    await expect(page).toHaveURL(/\/login$/);
    await expect(page.locator('body')).toContainText('Sign In');

    await context.close();
});

test('authenticated admin dashboard loads stable shell anchors', async ({ page }) => {
    await page.goto('/admin');

    await expect(page.locator('body')).toContainText('Dashboard');
    await expect(page.locator('#sidebarMenuSearch')).toBeVisible();
    await expect(page.getByRole('link', { name: /sign out/i })).toBeVisible();
    await expect(page.locator('#sidebar')).toContainText('Users');
});

test('admin can navigate planned module routes from the authenticated shell', async ({ page }) => {
    await page.goto('/admin');

    const removedEventAdminPrefixes = [
        '/admin/frontend/home',
        '/admin/speakers',
        '/admin/speaker-applications',
        '/admin/sponsors',
        '/admin/sponsor-categories',
        '/admin/sponsor-inquiries',
        '/admin/agenda',
        '/admin/event-bookings',
        '/admin/events',
        '/admin/promo-codes',
        '/admin/settings/ticket-checkout',
        '/admin/past-upcoming-events',
        '/admin/attractions',
        '/admin/testimonials',
        '/admin/venue',
        '/admin/venue-hotels',
        '/admin/hotel-bookings',
        '/admin/world-events',
    ];

    const moduleLinks = await page.locator('#sidebar a.c-sidebar-nav-link[href]').evaluateAll((links, removedPrefixes) => (
        links
            .map((link) => ({
                href: link.getAttribute('href'),
                text: link.textContent ? link.textContent.trim().replace(/\s+/g, ' ') : '',
            }))
            .filter((link) => link.href && link.href.startsWith('/admin'))
            .filter((link) => !removedPrefixes.some((prefix) => link.href.startsWith(prefix)))
            .filter((link) => !['/admin', '/admin/settings'].includes(link.href) || link.text !== 'Dashboard')
            .filter((link) => link.text !== 'Sign Out')
    ), removedEventAdminPrefixes);

    const uniqueModules = Array.from(new Map(moduleLinks.map((link) => [link.href, link])).values());

    for (const module of uniqueModules) {
        const response = await page.goto(module.href);
        await expectPageHealthy(page, response, module.text || module.href);
        await expect(page).toHaveURL(new RegExp(`${module.href.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`));
    }
});

test('admin can sign out cleanly', async ({ browser }) => {
    const context = await browser.newContext({ storageState: undefined });
    const page = await context.newPage();

    await loginAsAdmin(page);
    await logout(page);
    await page.goto('/admin');
    await expect(page).toHaveURL(/\/login$/);

    await context.close();
});

test('public frontend routes render without fatal browser errors', async ({ page }) => {
    for (const route of frontendRoutes) {
        const response = await page.goto(route.path);
        await expectPageHealthy(page, response, route.label);
        await expect(page).toHaveURL(new RegExp(`${route.path === '/' ? '/?$' : route.path.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}`));
    }
});
