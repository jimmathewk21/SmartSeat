<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains over 2000 video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the Laravel [Patreon page](https://patreon.com/taylorotwell).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Project License Setup

This project enforces a runtime license check via middleware.

1. Configure `.env`:
   - `LICENSE_API_URL` (optional; keep empty to skip remote validation in local/dev)
   - `LICENSE_DEV_HOSTS` (comma-separated hosts allowed for dev)
   - `LICENSE_DEV_PREFIXES` (comma-separated subdomain prefixes allowed for dev)
2. Store a local license bundle:
   - `php artisan license:set YOUR_LICENSE_KEY --domain=127.0.0.1`

If your `APP_URL` already matches your local host, `--domain` can be omitted.

## Local XAMPP Setup

For local development on XAMPP:

1. Start Apache and MySQL from the XAMPP Control Panel.
2. Set the database values in `.env` to match your local MySQL server:
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=laravel`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=` if your root account has no password
3. Run the migrations:

```bash
php artisan migrate
```

4. Load the demo data:

```bash
php artisan db:seed
```

The default seed run loads shared roles, permissions, users, and countries, plus train-booking FAQs, passenger testimonials, and sample contacts. Legacy event demo seeders are no longer included. `DownloadDemoSeeder` is retained for reference but is not called by `DatabaseSeeder` because its current sample files and copy are event-specific.

For a local database that needs an administrator without loading event demo data, see [Local administrator bootstrap](docs/local-admin-bootstrap.md). Its explicit command is guarded to `APP_ENV=local` and the connected `smartseat_dev` database; do not use the general `db:seed` command solely to create an administrator.

### Public Speaker API

The mobile app consumes these public JSON endpoints:

- `GET /api/v1/speakers`
- `GET /api/v1/speakers/{speaker}`

List filters match the public speaker page rules and accept:

- `keyword`
- `country`
- `category`
- `speaker_type`
- `speaker_role`
- `is_key_figure`
- `sort_by`
- `page`
- `per_page`

### Public Agenda API

The mobile app also consumes these public JSON endpoints:

- `GET /api/v1/agenda`
- `GET /api/v1/agenda/{agendaItem}`

The list endpoint returns the active event, active days, active rooms, and active sessions for the configured agenda page event.

### Public Sponsor API

The mobile app also consumes these public JSON endpoints:

- `GET /api/v1/sponsors`
- `GET /api/v1/sponsors/{sponsor}`

The list endpoint returns active sponsor categories with their active sponsors, the title sponsor, and scrollbar speaker highlights. The detail endpoint returns the selected sponsor profile payload, contact fields, resources, and related speakers.

### Public Downloads API

The mobile app also consumes this public JSON endpoint:

- `GET /api/v1/downloads`

The response returns the download page settings plus grouped media sections with normalized asset URLs.

### Public World Events API

The mobile app also consumes this public JSON endpoint:

- `GET /api/v1/world-events`

The response returns the world events page settings, active filter options, and the current monthly and yearly event lists.

### Public Notifications API

The mobile app also consumes this public JSON endpoint:

- `GET /api/v1/notifications`

The response returns a lightweight notifications feed derived from current agenda, speaker, world event, and download content.

### Authenticated Profile API

The mobile app also uses this authenticated profile endpoint:

- `PUT /api/v1/auth/profile`

It updates the signed-in user's name, company name, mobile number, and profile picture URL, then returns the refreshed user payload.

### Agenda Attachments

Admin agenda sessions include two upload fields for agenda materials. Both fields accept:

- PDF
- JPG / JPEG
- Word (`DOC`, `DOCX`)
- PowerPoint (`PPT`, `PPTX`)

Uploaded files are stored under `public/uploads/agenda`.

### Agenda Page Settings Upload

The admin `Agenda Page Settings` screen also includes an upload field for the agenda download button. Uploaded files replace the download URL setting and support the same file types listed above.

5. Start the app:

```bash
php artisan serve
```

## Ticket Management + Razorpay (INR Settlement, INR/USD Display)

This project supports event-wise ticket booking with multi-plan pricing and Razorpay payments.

## Smart Train Booking

Train search and seat booking use a separate train domain. See [docs/train-booking-system.md](docs/train-booking-system.md) for the schema, workflow, migration order, and MySQL/InnoDB concurrency notes. The train tables are additive and do not reuse event bookings or ticket codes.

### Environment Variables

Add these values in `.env`:

- `APP_NAME=IGI Summit` so queued notifications and mail templates use the site name instead of the Laravel default
- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_WEBHOOK_SECRET`
- `RAZORPAY_CURRENCY=INR`
- `MAIL_ALWAYS_CC=info@igisummit.com` (CC all outgoing emails to this address)
- `MAIL_FROM_NAME=IGI Summit` if you override the sender name explicitly
- `QUEUE_CONNECTION=database` (recommended for queued email/notification delivery)

### Queue Worker Setup (Email + Notifications)

Email and notification delivery in this project is queued. Configure and run a queue worker:

```bash
php artisan migrate
php artisan queue:work --tries=3
```

If your project does not yet have queue tables, generate them first:

```bash
php artisan queue:table
php artisan queue:failed-table
php artisan migrate
```

For production, run `queue:work` under a process manager (Supervisor/systemd) so it stays alive.

### Mail Branding

- Password reset and email verification notifications are branded for IGI Summit.
- Markdown mail components are overridden in `resources/views/vendor/mail/` so the email header and footer use the IGI Summit logo and name instead of the Laravel default logo.

### Admin Email Templates

- Admin can manage form notification templates from the `Settings > Email Templates` screen.
- Approval emails for approved users now use the editable `User Approval` template with placeholders such as `{{name}}`, `{{email}}`, `{{mobile}}`, `{{company_name}}`, `{{login_url}}`, and `{{support_email}}`.
- The same template system also covers sponsor inquiries, contact submissions, registrations, speaker applications, testimonials, and ticket payment notices.

### Frontend Form Captcha

- The public contact, sponsor inquiry, testimonial, and my-contact forms use a simple session-backed math captcha.
- The captcha question is generated server-side on page load and must match the submitted answer before the form is accepted.

### systemd Setup (Production)

Use `systemd` to keep Laravel queue workers running across restarts.

1. Create service file `/etc/systemd/system/event-management-queue.service`:

```ini
[Unit]
Description=Laravel Queue Worker (event-management)
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
WorkingDirectory=/var/www/event-management-laravel
ExecStart=/usr/bin/php /var/www/event-management-laravel/artisan queue:work --sleep=3 --tries=3 --timeout=120
ExecReload=/usr/bin/php /var/www/event-management-laravel/artisan queue:restart
KillSignal=SIGTERM
TimeoutStopSec=60

[Install]
WantedBy=multi-user.target
```

2. Reload and enable:

```bash
sudo systemctl daemon-reload
sudo systemctl enable event-management-queue
sudo systemctl start event-management-queue
```

3. Verify status/logs:

```bash
sudo systemctl status event-management-queue
sudo journalctl -u event-management-queue -f
```

4. After each deploy/code change:

```bash
php artisan queue:restart
```

Notes:
- Replace `www-data` and `/var/www/event-management-laravel` with your server user/path.
- Keep `QUEUE_CONNECTION` set to `database` (or another async driver) in `.env`.

### Migrations / Setup

Run:

```bash
php artisan migrate
```

This creates:

- `event_ticket_plans`
- `event_bookings`
- `booking_tickets`
- `payment_webhooks`

### Pricing Model

- Each event supports **multiple ticket plans** (admin can add/remove as needed).
- Each plan stores:
  - INR MRP + INR Sale
  - USD MRP + USD Sale
- Discount is derived from `MRP - Sale`.
- Gateway charge is always INR (paise).

### Country-Based Checkout

- Country `IN` -> user sees INR plan prices.
- Country non-`IN` -> user sees USD plan prices.
- For non-India checkout, server converts USD sale price to INR paise using setting key `ticket_fx_usd_to_inr`.
- Conversion is stored in booking audit fields (`display_currency`, `display_total_amount_minor`, `fx_rate_usd_to_inr`).

### Payment Flow

1. User opens `/events/{slug}/tickets`.
2. User selects plan + quantity and starts checkout.
3. Server creates Razorpay order and `pending` booking.
4. Callback signature is verified server-side at `/payments/razorpay/verify`.
5. Booking becomes `paid` and ticket codes are generated.
6. Webhook `/payments/razorpay/webhook` reconciles delayed/async payment events.

### Notes

- Settlement currency is INR-only and stored in paise.
- Display currency can be INR or USD (based on selected country).
- Amount is always calculated on the server from selected plan, quantity, country, and FX setting.
- `My Tickets` page is available at `/my-tickets` for logged-in users.

### Login Activity IP Geolocation

- Admin login activity pages and the admin users list now resolve `city` and `country` from the latest stored IP address using GeoJS.
- Configure the lookup service in `.env` if needed:
  - `GEOJS_BASE_URL=https://get.geojs.io`
  - `GEOJS_TIMEOUT_SECONDS=5`
  - `GEOJS_CACHE_TTL_MINUTES=1440`
- The lookup is cached and failures fall back to `-` so login activity pages stay usable if GeoJS is slow or unavailable.

### User Management Defaults

- The admin add-user form preselects the default `User` role when the page opens.
- If the role selection is left unchanged, the backend still falls back to assigning `User` on save.

## Playwright Admin E2E

This repo now includes a Playwright browser suite for the Laravel admin panel.

### Install

```bash
npm install
npx playwright install chromium
```

### Defaults

- Base URL defaults to `http://127.0.0.1:8000`
- Seeded admin defaults to `admin@admin.com` / `password`
- Override with:
  - `PLAYWRIGHT_BASE_URL`
  - `PLAYWRIGHT_PORT`
  - `PLAYWRIGHT_ADMIN_EMAIL`
  - `PLAYWRIGHT_ADMIN_PASSWORD`

### Run

```bash
npm run test:e2e
```

Headed or interactive runs:

```bash
npm run test:e2e:headed
npm run test:e2e:ui
```

### Current Coverage

- Auth/bootstrap:
  - login storage-state setup
  - guest redirect from `/admin`
  - authenticated dashboard and sign-out
- Admin route smoke:
  - all sidebar-accessible admin modules for the seeded admin account
  - user-management, frontend-settings, content, ticketing, FAQ, venue, world-events, contacts, and settings sections
- Focused CRUD browser coverage:
  - Sponsor Categories
  - Sponsors
  - FAQ Categories + FAQs
  - Events + Ticket Plans
  - Agenda Days / Rooms / Sessions
  - World Events
  - Speakers
  - Testimonials
  - Members
  - Attractions
  - Promo Codes (create/update)
  - Frontend Pages
  - Frontend SEO Titles
  - Download Items
  - My Contacts
    - server-side star auto-assignment with additive scoring across category, experience, key person, key role, and VIP speaker tag, capped at 5
- Public frontend route smoke:
  - home, speakers, sponsors, events, agenda, world-events, attractions, FAQs, testimonials, contact, downloads, venue, and public application/contact surfaces

### Coverage Matrix

- See [`docs/playwright-coverage-matrix.md`](docs/playwright-coverage-matrix.md) for the route-by-route coverage status and remaining gaps.

### File Layout

- `playwright.config.js`
- `tests/e2e/admin-auth.setup.spec.js`
- `tests/e2e/admin-smoke.spec.js`
- `tests/e2e/admin-content-crud.spec.js`
- `tests/e2e/admin-event-ops.spec.js`
- `tests/e2e/admin-people-crud.spec.js`
- `tests/e2e/helpers/admin.js`
