# Playwright Coverage Matrix

This matrix tracks browser E2E coverage in `tests/e2e` for the Laravel backend.

Status legend:

- `Covered`: current Playwright suite has at least one meaningful test for the surface.
- `Smoke only`: route loads in browser coverage, but no create/update/delete or form flow coverage yet.
- `Partial CRUD`: at least one create/update/delete happy path exists, but adjacent settings or variants still rely on Laravel tests.
- `Pending`: no Playwright coverage yet.
- `Out of scope for Playwright`: covered better by Laravel feature tests or a dedicated mobile E2E stack.

## Admin Auth and Shell

| Surface | Status | Notes |
| --- | --- | --- |
| Login bootstrap / storage state | Covered | `admin-auth.setup.spec.js` |
| Guest redirect from `/admin` | Covered | `admin-smoke.spec.js` |
| Dashboard shell and sign-out | Covered | `admin-smoke.spec.js` |
| Sidebar route smoke for admin modules | Covered | Expanded in `admin-smoke.spec.js` |

## Admin CRUD and Settings

| Surface | Status | Notes |
| --- | --- | --- |
| Users | Smoke only | Route coverage only |
| Login Attempts | Smoke only | Read-only route coverage |
| Roles | Smoke only | Route coverage only |
| Permissions | Smoke only | Route coverage only |
| Frontend Header / Footer / Home / Auth page | Smoke only | Settings forms load |
| Frontend Pages | Covered | CRUD happy path |
| Frontend Page Access | Smoke only | Settings route coverage |
| Frontend SEO Titles | Covered | CRUD happy path |
| Speakers | Partial CRUD | CRUD covered |
| Speaker Applications | Smoke only | Read-only/admin action route coverage |
| Sponsor Categories | Partial CRUD | CRUD covered |
| Sponsors | Covered | CRUD happy path |
| Sponsor Enquiries | Smoke only | Read-only route coverage |
| Sponsors Page Settings | Smoke only | Settings route coverage |
| Agenda Page Settings | Smoke only | Settings route coverage |
| Agenda Days / Rooms / Sessions | Partial CRUD | CRUD covered |
| Ticket Bookings | Smoke only | Read-only route coverage |
| Events / Ticket Packages | Partial CRUD | CRUD and ticket-plan happy path |
| Promo Codes | Partial CRUD | Create/update covered; delete path still needs investigation |
| Ticket Checkout Settings | Smoke only | Settings route coverage |
| Download Page | Smoke only | Settings route coverage |
| Download Media Items | Covered | CRUD happy path |
| FAQs | Partial CRUD | FAQ + category CRUD covered |
| FAQ Questions | Smoke only | Admin inbox route coverage |
| Past & Upcoming Events | Smoke only | Uses events listing flow |
| Members | Covered | CRUD happy path |
| Attractions | Covered | CRUD happy path |
| Testimonials | Covered | CRUD happy path |
| Contact page settings | Smoke only | Settings route coverage |
| Cities / States / Countries | Smoke only | Route coverage only |
| Venue page settings / reservation / tax | Smoke only | Settings route coverage |
| Venue Hotels | Pending | Not yet in Playwright CRUD |
| Hotel Bookings | Smoke only | Read-only route coverage |
| World Events | Partial CRUD | CRUD covered |
| My Contacts | Partial CRUD | CRUD covered |
| Generic Settings index | Smoke only | Index route coverage |

## Public Laravel Frontend

| Surface | Status | Notes |
| --- | --- | --- |
| Home | Covered | Route smoke |
| Speakers list | Covered | Route smoke |
| Sponsors list | Covered | Route smoke |
| Events list | Covered | Route smoke |
| World Events list | Covered | Route smoke |
| Agenda | Covered | Route smoke |
| Attractions / Things to do | Covered | Route smoke |
| FAQs | Covered | Route smoke |
| Testimonials | Covered | Route smoke |
| Contact Us | Covered | Route smoke |
| My Contacts | Covered | Route smoke |
| Add My Contact | Covered | Route smoke |
| Download | Covered | Route smoke |
| Become Speaker | Covered | Route smoke |
| Become Sponsor | Covered | Route smoke |
| Venue / Hotel Booking | Covered | Route smoke |
| Detail pages (`/events/{slug}`, `/speakers/{id}`, etc.) | Pending | Need stable seeded-data coverage |
| Public form submissions | Pending | Contact, FAQ ask, speaker/sponsor applications |
| Ticket purchase / Razorpay journey | Pending | Needs gateway mocking or test-mode contract |
| Authenticated public flows (`/my-tickets`, order summary) | Pending | Depends on seeded user/browser auth strategy |

## Mobile App

| Surface | Status | Notes |
| --- | --- | --- |
| React Native app launch via Jest | Covered | `__tests__/App.test.tsx` |
| Mobile E2E flows | Out of scope for Playwright | Needs Detox or Maestro |

## Next Priority

1. Add Venue Hotels CRUD coverage.
2. Add public form-submission happy paths.
3. Add ticketing and payment happy-path browser coverage with a mocked/test gateway boundary.
4. Add a dedicated mobile E2E stack for `mobile-app-react-native`.
