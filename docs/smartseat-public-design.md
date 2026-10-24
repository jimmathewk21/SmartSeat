# SmartSeat public railway interface

The train search homepage (`/`, `/trains`, and `/index.html`) and My Bookings use the dedicated train header/footer and `public/css/train-search.css`. The design uses railway blue, warm ivory, and saffron accents, system sans-serif text, and Georgia display headings. Train pages do not load the retired event template's global stylesheet. No hosted fonts, animation libraries, or video are required.

## Search and booking

- `partials/search-panel.blade.php` submits the existing GET parameters: `from_station_id`, `to_station_id`, and `date`. Passenger count belongs to the existing booking/waitlist forms; search has no passenger-count parameter.
- `public/js/train-search.js` progressively enhances server-rendered station selects into comboboxes. Suggestions come exclusively from those options, filtered by station name, city, or code. Arrow keys browse, Enter selects, Escape dismisses, and Tab moves on. The original selects submit the station IDs and remain usable without JavaScript. Server validation remains authoritative.
- The destination photographs are travel inspiration. They do not advertise bookable services, prices, or nationwide coverage. Search and results still depend on the actual station catalog and scheduled trips. No demo data is created by the UI.
- Booking forms expand inline, including on mobile. Inventory IDs, journey endpoints, CSRF fields, passenger validation, cancellation conditions, and waitlist status semantics are retained. Seats remain exclusive to the whole trip.
- Motion respects `prefers-reduced-motion`; scroll reveals do not hide content without JavaScript. Local SVG backgrounds preserve image layouts if a photograph fails. All images have alt text; destination images are lazy loaded.

## Photography sources and license

The following local WebP files were downloaded from Pexels, resized, and converted to WebP at quality 78. Display crops use CSS. Combined size is approximately 806 KiB. Sources were checked on 24 October 2026. All seven photographs use the [Pexels License](https://www.pexels.com/license/), which permits website use and modification. Do not sell the original photographs, redistribute them as stock, use them as a trademark, or imply photographer/subject endorsement. The homepage includes accessible photography credits.

| Local path under `public/images/railway/` | Subject | Photographer and original |
| --- | --- | --- |
| `railway-hero.webp` | Darjeeling Himalayan Railway | [Soumalya Halder](https://www.pexels.com/photo/darjeeling-toy-train-on-scenic-route-33565206/) |
| `jaipur.webp` | Hawa Mahal | [Aditya Oberai](https://www.pexels.com/photo/hawa-mahal-palace-facade-in-jaipur-india-30171014/) |
| `varanasi.webp` | Riverfront ghats | [The Capturist](https://www.pexels.com/photo/vibrant-scene-at-varanasi-ghats-in-india-35868472/) |
| `udaipur.webp` | Lake Pichola palaces | [Anshul Kaushik](https://www.pexels.com/photo/scenic-view-of-udaipur-s-historic-lakefront-palaces-33658453/) |
| `mumbai.webp` | Gateway of India | [Shivam](https://www.pexels.com/photo/gateway-of-india-in-mumbai-in-india-21319632/) |
| `kochi.webp` | Chinese fishing nets | [Jinu Safarin](https://www.pexels.com/photo/a-view-of-a-harbor-with-boats-and-a-dock-27833051/) |
| `darjeeling.webp` | Himalayan peaks | [Vinayak Khithani](https://www.pexels.com/photo/landscape-photo-of-a-snowy-mountaintop-2408962/) |

`fallback.svg` is an original, code-drawn railway illustration for this project. No Wikimedia files were included: its image server refused the download. When replacing a photograph, update the alt text, credits, and this table, and verify its license. Keep assets local and optimized; avoid live third-party image URLs.

## Verification

Run the focused frontend/routing, passenger authentication, booking workflow, and owner authorization tests with process-only `APP_ENV=testing`, `DB_CONNECTION=mysql`, and `DB_DATABASE=smartseat_train_test`. Verify the actual connected database before executing database-backed tests. Do not run migrations or seeders as part of visual verification.

At desktop and mobile widths, check the station picker (including keyboard use and swapping), date picker, results, expanded seat/passenger forms, confirmation, booking history, empty states, and reduced motion. Test without JavaScript to verify native station selects. The public stylesheet and JavaScript are served directly; no asset build is needed for these files.

The isolated browser suite is `npx playwright test --config=tests/e2e/train-ui.config.js`. It covers desktop and mobile Chromium using real Blade output, local assets, and in-memory models. Its renderer rejects all SQL, and Playwright intercepts every local request; it does not start an application server or use admin credentials. It checks autocomplete, keyboard selection, swap/query preservation, invalid free text, passenger/seat controls, confirmation versus waitlist status, no-JavaScript fallback, failed images, reduced motion, and horizontal overflow. Screenshots are written beneath ignored `test-results/`. Set `PLAYWRIGHT_CHROMIUM_EXECUTABLE` if using an existing local Chromium binary. These are presentation checks; the Laravel regression suite verifies backend behavior separately.
