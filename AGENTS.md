# AGENTS.md

## Project Context
Build a responsive Laravel-based event management website with:
- Public event showcase pages
- Multi-plan paid registrations
- Razorpay integration (INR only)
- Admin panel for end-to-end content and event operations
- Structured sponsor category and sponsor sorting

## Core Objective
Deliver a maintainable, production-ready event platform where public users can browse event details and complete secure paid registrations, while admins can manage all event data from an authenticated panel.

## Functional Scope

### Public Website Pages
Implement and maintain these public pages:
- Home
- About Us
- Venue
- Registration (multiple pricing plans + Razorpay)
- Speakers
- Contact
- Testimonials (filter by year)
- FAQ
- Terms & Conditions
- Privacy Policy

### Registration and Payments
Required behavior:
- Support multiple pricing plans per event
- Store and display all plan prices in INR only
- Create Razorpay orders via Razorpay Order API
- Verify payment signature server-side before marking as paid
- Process webhook events for payment confirmation/sync
- Track payment status: `pending`, `paid`, `failed`
- Persist participant and payment metadata in database
- Provide participant CSV export from admin

Explicitly out of scope unless later requested:
- Refund automation
- Multi-currency
- Partial payments
- Wallet systems

### Admin Panel (Authenticated)
Admin must be able to manage:
- Events (create, edit, publish)
- Pricing plans (active/inactive + sortable)
- Sponsors
- Sponsor categories (active/inactive + sortable)
- Sponsor sorting within each category
- Speakers
- Agenda
- Members
- Participants (including payment details)
- Testimonials (with year-based filtering support)
- FAQs

## Sponsor Management Rules (Critical)
- Sponsor categories have `active`/`inactive` status
- Categories are displayed in configured sort order
- Sponsors are sortable within each category
- Public site must show only active categories
- Public site must show only active sponsors under active categories

## Technical and Delivery Rules
- Responsive design is mandatory for web (desktop), tablet, and mobile breakpoints
- Ensure layouts, typography, spacing, navigation, forms, and tables adapt cleanly across desktop, tablet, and mobile devices
- Test and support current versions of modern browsers on desktop and mobile
- Use clear validation and error handling for registration/payment flows
- Treat payment verification and webhook handling as security-critical
- Prefer minimal, well-scoped changes that preserve existing behavior
- Add/update tests when changing payment, sorting, filtering, or publish-state logic
- Document new environment variables and integration setup in `README.md`

## Definition of Done (Feature Level)
A feature is complete only when:
- Functional behavior matches the scope above
- Role/access boundaries are respected (public vs admin)
- Payment state transitions are reliable and auditable
- Sponsor/category visibility and sorting rules are enforced
- Relevant tests pass (or blockers are explicitly documented)
