<?php

use App\Http\Controllers\Admin\AuditLogsController;
use App\Http\Controllers\Admin\AgendaDaysController;
use App\Http\Controllers\Admin\AgendaItemsController;
use App\Http\Controllers\Admin\AgendaPageSettingsController;
use App\Http\Controllers\Admin\AuthPageSettingsController;
use App\Http\Controllers\Admin\AgendaRoomsController;
use App\Http\Controllers\Admin\AttractionPageSettingsController;
use App\Http\Controllers\Admin\AttractionsController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\CountriesController;
use App\Http\Controllers\Admin\ContactPageSettingsController;
use App\Http\Controllers\Admin\DownloadMediaItemsController;
use App\Http\Controllers\Admin\DownloadPageSettingsController;
use App\Http\Controllers\Admin\EmailTemplateSettingsController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LoginActivitiesController;
use App\Http\Controllers\Admin\MaintenanceController;
use App\Http\Controllers\Admin\MaintenancePageSettingsController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\MyContactPageSettingsController;
use App\Http\Controllers\Admin\MyContactsController;
use App\Http\Controllers\Admin\ParticipantsController;
use App\Http\Controllers\Admin\PermissionsController;
use App\Http\Controllers\Admin\PromoCodesController;
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SpeakersController;
use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Admin\FaqQuestionsController;
use App\Http\Controllers\Admin\FaqCategoriesController;
use App\Http\Controllers\Admin\FaqPageSettingsController;
use App\Http\Controllers\Admin\EventsController;
use App\Http\Controllers\Admin\EventBookingsController;
use App\Http\Controllers\Admin\EventPageSettingsController;
use App\Http\Controllers\Admin\FrontendFooterController;
use App\Http\Controllers\Admin\FrontendHeaderController;
use App\Http\Controllers\Admin\FrontendHomeController;
use App\Http\Controllers\Admin\FrontendPageAccessController;
use App\Http\Controllers\Admin\FrontendPagesController;
use App\Http\Controllers\Admin\FrontendSeoTitlesController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\SpeakerPageSettingsController;
use App\Http\Controllers\Admin\SpeakerApplicationsController;
use App\Http\Controllers\Admin\SponsorPageSettingsController;
use App\Http\Controllers\Admin\SponsorCategoriesController;
use App\Http\Controllers\Admin\SponsorsController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\TestimonialPageSettingsController;
use App\Http\Controllers\Admin\TestimonialsController;
use App\Http\Controllers\Admin\TicketCheckoutSettingsController;
use App\Http\Controllers\Admin\HotelBookingSettingsController;
use App\Http\Controllers\Admin\HotelBookingsController;
use App\Http\Controllers\Admin\VenueHotelsController;
use App\Http\Controllers\Admin\VenuePageSettingsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\WorldEventsController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\BecomeSponsorController;
use App\Http\Controllers\FrontendContactController;
use App\Http\Controllers\FrontendDownloadController;
use App\Http\Controllers\FrontendEventController;
use App\Http\Controllers\FrontendAgendaController;
use App\Http\Controllers\FrontendAttractionController;
use App\Http\Controllers\FrontendFaqController;
use App\Http\Controllers\FrontendMyTicketsController;
use App\Http\Controllers\FrontendNewsletterController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\FrontendMyContactController;
use App\Http\Controllers\FrontendSponsorController;
use App\Http\Controllers\FrontendSpeakerController;
use App\Http\Controllers\FrontendSpeakerApplicationController;
use App\Http\Controllers\FrontendTestimonialController;
use App\Http\Controllers\FrontendTicketController;
use App\Http\Controllers\FrontendTrainController;
use App\Http\Controllers\TrainAuthController;
use App\Http\Controllers\TrainOwnerAuthController;
use App\Http\Controllers\TrainOwnerTrainsController;
use App\Http\Controllers\TrainBookingController;
use App\Http\Controllers\FrontendWorldEventsController;
use App\Http\Controllers\FrontendHotelBookingController;
use App\Http\Controllers\Admin\SponsorInquiriesController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The train search is the public landing page; keep the event homepage on its own route.
Route::get('/', [FrontendTrainController::class, 'index'])->name('frontend.trains.home');
Route::view('/event-home', 'frontend')->name('frontend.home')->middleware('page.access');
Route::view('/index.html', 'frontend')->middleware('page.access');
Route::post('/newsletter-subscribe', [FrontendNewsletterController::class, 'store'])->name('frontend.newsletter.store')->middleware('page.access');
Route::get('/pages/{slug}', [FrontendPageController::class, 'show'])->name('frontend.page.show')->middleware('page.access');
Route::get('/speakers', [FrontendSpeakerController::class, 'index'])->name('frontend.speakers.index')->middleware('page.access');
Route::get('/speakers/{speaker}', [FrontendSpeakerController::class, 'show'])->name('frontend.speakers.show')->middleware('page.access');
Route::get('/become-speaker', [FrontendSpeakerApplicationController::class, 'create'])->name('frontend.speakers.apply.create')->middleware('page.access');
Route::post('/become-speaker', [FrontendSpeakerApplicationController::class, 'store'])->name('frontend.speakers.apply')->middleware('page.access');
Route::get('/sponsors', [FrontendSponsorController::class, 'index'])->name('frontend.sponsors.index')->middleware('page.access');
Route::get('/sponsors/{sponsor}', [FrontendSponsorController::class, 'show'])->name('frontend.sponsors.show')->middleware('page.access');
Route::get('/events', [FrontendEventController::class, 'index'])->name('frontend.events.index')->middleware('page.access');
Route::get('/events/{slug}', [FrontendEventController::class, 'show'])->name('frontend.events.show')->middleware('page.access');
Route::get('/world-events', [FrontendWorldEventsController::class, 'index'])->name('frontend.world-events.index')->middleware('page.access');
Route::get('/agenda', [FrontendAgendaController::class, 'index'])->name('frontend.agenda.index')->middleware('page.access');
Route::get('/attractions', [FrontendAttractionController::class, 'index'])->name('frontend.attractions.index')->middleware('page.access');
Route::get('/things-to-do', [FrontendAttractionController::class, 'index'])->name('frontend.attractions.things')->middleware('page.access');
Route::get('/faqs', [FrontendFaqController::class, 'index'])->name('frontend.faqs.index')->middleware('page.access');
Route::post('/faqs/ask', [FrontendFaqController::class, 'storeQuestion'])->name('frontend.faqs.store')->middleware('page.access');
Route::get('/testimonials', [FrontendTestimonialController::class, 'index'])->name('frontend.testimonials.index')->middleware('page.access');
Route::post('/testimonials', [FrontendTestimonialController::class, 'store'])->name('frontend.testimonials.store')->middleware('page.access');
Route::get('/contact-us', [FrontendContactController::class, 'index'])->name('frontend.contact.index')->middleware('page.access');
Route::post('/contact-us', [FrontendContactController::class, 'store'])->name('frontend.contact.store')->middleware('page.access');
Route::get('/my-contacts', [FrontendMyContactController::class, 'index'])->name('frontend.my-contacts.index')->middleware('page.access');
Route::get('/add-my-contact', [FrontendMyContactController::class, 'create'])->name('frontend.my-contacts.create')->middleware('page.access');
Route::get('/add-my-contact/check-email', [FrontendMyContactController::class, 'checkOfficialEmail'])->name('frontend.my-contacts.check-email')->middleware('page.access');
Route::post('/add-my-contact', [FrontendMyContactController::class, 'store'])->name('frontend.my-contacts.store')->middleware('page.access');
Route::get('/download', [FrontendDownloadController::class, 'index'])->name('frontend.download.index')->middleware('page.access');
Route::get('/events/{event:slug}/tickets', [FrontendTicketController::class, 'show'])->name('frontend.events.tickets')->middleware('page.access');
Route::post('/events/{event:slug}/summary', [FrontendTicketController::class, 'summary'])->middleware('auth')->name('frontend.events.summary');
Route::post('/events/{event:slug}/orders', [FrontendTicketController::class, 'createOrder'])->middleware('auth')->name('frontend.events.orders');
Route::post('/payments/razorpay/verify', [FrontendTicketController::class, 'verifyPayment'])->middleware('auth')->name('frontend.payments.verify');
Route::post('/payments/razorpay/failed', [FrontendTicketController::class, 'paymentFailed'])->middleware('auth')->name('frontend.payments.failed');
Route::post('/payments/razorpay/webhook', [FrontendTicketController::class, 'webhook'])->name('frontend.payments.webhook');
Route::get('/my-tickets', [FrontendMyTicketsController::class, 'index'])->middleware('auth')->name('my-tickets.index');
Route::get('/trains', [FrontendTrainController::class, 'index'])->name('frontend.trains.search');
Route::get('/trains/sign-in', [TrainAuthController::class, 'showLogin'])->name('frontend.trains.login');
Route::get('/trains/register', [TrainAuthController::class, 'showRegister'])->name('frontend.trains.register');
// Intentionally unadvertised owner entry point; authorization is enforced on every protected route.
Route::get('/internal/rail-owner/entry', [TrainOwnerAuthController::class, 'showLogin'])->middleware(['guest', 'throttle:10,1'])->name('train-owner.login');
Route::post('/internal/rail-owner/entry', [TrainOwnerAuthController::class, 'login'])->middleware(['guest', 'throttle:6,1'])->name('train-owner.login.submit');
Route::middleware(['auth', 'train.owner:train_management_access'])->prefix('internal/rail-owner')->name('train-owner.')->group(function () {
    Route::get('/trains', [TrainOwnerTrainsController::class, 'index'])->name('trains.index');
    Route::get('/trains/{train}/edit', [TrainOwnerTrainsController::class, 'edit'])->middleware('train.owner:train_management_edit')->name('trains.edit');
    Route::put('/trains/{train}', [TrainOwnerTrainsController::class, 'update'])->middleware('train.owner:train_management_edit')->name('trains.update');
    Route::post('/logout', [TrainOwnerAuthController::class, 'logout'])->name('logout');
});
Route::middleware('auth')->group(function () {
    Route::get('/my-train-bookings', [FrontendTrainController::class, 'myBookings'])->name('frontend.trains.bookings');
    Route::post('/trips/{trip}/book', [TrainBookingController::class, 'store'])->name('frontend.trains.book');
    Route::post('/trips/{trip}/waitlist', [TrainBookingController::class, 'joinWaitlist'])->name('frontend.trains.waitlist');
    Route::post('/my-train-bookings/{booking}/cancel', [TrainBookingController::class, 'cancel'])->name('frontend.trains.cancel');
    Route::post('/my-train-waitlist/{entry}/cancel', [TrainBookingController::class, 'cancelWaitlist'])->name('frontend.trains.waitlist.cancel');
});
Route::get('/become-sponsor', [BecomeSponsorController::class, 'create'])->name('frontend.become-sponsor.create')->middleware('page.access');
Route::post('/become-sponsor', [BecomeSponsorController::class, 'store'])->name('frontend.become-sponsor.store')->middleware('page.access');
Route::get('/venue', [FrontendHotelBookingController::class, 'index'])->name('frontend.venue.index')->middleware('page.access');
Route::get('/venue/{venueHotel}', [FrontendHotelBookingController::class, 'show'])->name('frontend.venue.show')->middleware('page.access');
Route::get('/venue/{venueHotel}/reserve', [FrontendHotelBookingController::class, 'reserve'])->name('frontend.venue.reserve')->middleware('page.access');
Route::post('/venue/{venueHotel}/reserve', [FrontendHotelBookingController::class, 'store'])->name('frontend.venue.reserve.store')->middleware('page.access');
Route::get('/hotel-booking', [FrontendHotelBookingController::class, 'index'])->middleware('page.access');
Route::get('/home', function () {
    if (auth()->check() && ! auth()->user()->is_admin) {
        return redirect()->route('frontend.home');
    }

    return session('status')
        ? redirect()->route('admin.home')->with('status', session('status'))
        : redirect()->route('admin.home');
})->name('home');

// 🔹 Authentication Routes
Auth::routes();

// 🔹 Maintenance Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    // Route::get('/run-migrate', [MaintainanceController::class, 'runMigrate'])->name('run.migrate');
    // Route::get('/cache-clear', [MaintainanceController::class, 'cacheClear'])->name('cache.clear');
    // Route::get('/composer-install', [MaintainanceController::class, 'composerInstall'])->name('composer.install');
});

// 🔹 Admin Panel Routes
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('/dashboard/payment-gateway', [HomeController::class, 'togglePaymentGateway'])->name('dashboard.payment-gateway.toggle');
    Route::post('/dashboard/fx-rate', [HomeController::class, 'updateFxRate'])->name('dashboard.fx-rate.update');
    Route::get('/dashboard/live-fx-rate', [HomeController::class, 'liveFxRate'])->name('dashboard.live-fx-rate');
    Route::post('/maintenance/migrate', [MaintenanceController::class, 'migrate'])->name('maintenance.migrate');
    Route::post('/maintenance/cache-refresh', [MaintenanceController::class, 'cacheRefresh'])->name('maintenance.cacheRefresh');
    Route::post('/maintenance/optimize', [MaintenanceController::class, 'optimize'])->name('maintenance.optimize');
    Route::post('/maintenance/frontend-mode', [MaintenanceController::class, 'toggleFrontend'])->name('maintenance.frontendMode');
    Route::get('frontend/header', [FrontendHeaderController::class, 'edit'])->name('frontend.header.edit');
    Route::put('frontend/header', [FrontendHeaderController::class, 'update'])->name('frontend.header.update');
    Route::get('frontend/footer', [FrontendFooterController::class, 'edit'])->name('frontend.footer.edit');
    Route::put('frontend/footer', [FrontendFooterController::class, 'update'])->name('frontend.footer.update');
    Route::get('frontend/home', [FrontendHomeController::class, 'edit'])->name('frontend.home.edit');
    Route::put('frontend/home', [FrontendHomeController::class, 'update'])->name('frontend.home.update');
    Route::get('frontend/auth-page', [AuthPageSettingsController::class, 'edit'])->name('frontend.auth-page.edit');
    Route::put('frontend/auth-page', [AuthPageSettingsController::class, 'update'])->name('frontend.auth-page.update');
    Route::get('frontend/contact', [ContactPageSettingsController::class, 'edit'])->name('frontend.contact.edit');
    Route::put('frontend/contact', [ContactPageSettingsController::class, 'update'])->name('frontend.contact.update');
    Route::get('frontend/download', [DownloadPageSettingsController::class, 'edit'])->name('frontend.download.edit');
    Route::put('frontend/download', [DownloadPageSettingsController::class, 'update'])->name('frontend.download.update');
    Route::get('frontend/page-access', [FrontendPageAccessController::class, 'edit'])->name('frontend.page-access.edit');
    Route::put('frontend/page-access', [FrontendPageAccessController::class, 'update'])->name('frontend.page-access.update');
    Route::resource('frontend/seo-titles', FrontendSeoTitlesController::class)
        ->parameters(['seo-titles' => 'seoTitle'])
        ->except(['show'])
        ->names('frontend.seo-titles');
    Route::delete('frontend/pages/destroy', [FrontendPagesController::class, 'massDestroy'])->name('frontend.pages.massDestroy');
    Route::resource('frontend/pages', FrontendPagesController::class)->names('frontend.pages');
    Route::delete('download-media-items/destroy', [DownloadMediaItemsController::class, 'massDestroy'])->name('download-media-items.massDestroy');
    Route::post('download-media-items/bulk-status', [DownloadMediaItemsController::class, 'bulkStatus'])->name('download-media-items.bulkStatus');
    Route::post('download-media-items/reorder', [DownloadMediaItemsController::class, 'reorder'])->name('download-media-items.reorder');
    Route::resource('download-media-items', DownloadMediaItemsController::class)->except(['show']);

    //  Permissions
    Route::delete('permissions/destroy', [PermissionsController::class, 'massDestroy'])->name('permissions.massDestroy');
    Route::resource('permissions', PermissionsController::class);

    //  Roles
    Route::delete('roles/destroy', [RolesController::class, 'massDestroy'])->name('roles.massDestroy');
    Route::resource('roles', RolesController::class);

    //  Users
    Route::delete('users/destroy', [UsersController::class, 'massDestroy'])->name('users.massDestroy');
    Route::post('users/bulk-approval', [UsersController::class, 'bulkApproval'])->name('users.bulkApproval');
    Route::get('users/export', [UsersController::class, 'export'])->name('users.export');
    Route::resource('users', UsersController::class);
    Route::post('users/approve-verification', [UsersController::class, 'approveVerification'])->name('users.approveVerification');

    //  Participants (Approval)
    Route::resource('participants', ParticipantsController::class)->only(['index', 'show', 'update']);

    //  Audit Logs (Read-Only)
    Route::resource('audit-logs', AuditLogsController::class)->except(['create', 'store', 'edit', 'update', 'destroy']);
    Route::delete('login-activities/clear-all', [LoginActivitiesController::class, 'clearAll'])->name('login-activities.clearAll');
    Route::resource('login-activities', LoginActivitiesController::class)->only(['index', 'show']);

    //  Settings
    Route::delete('settings/destroy', [SettingsController::class, 'massDestroy'])->name('settings.massDestroy');
    Route::post('settings/parse-csv-import', [SettingsController::class, 'parseCsvImport'])->name('settings.parseCsvImport');
    Route::post('settings/process-csv-import', [SettingsController::class, 'processCsvImport'])->name('settings.processCsvImport');
    Route::get('settings/site-meta', [SiteSettingsController::class, 'edit'])->name('settings.site-meta.edit');
    Route::put('settings/site-meta', [SiteSettingsController::class, 'update'])->name('settings.site-meta.update');
    Route::get('settings/email-templates', [EmailTemplateSettingsController::class, 'edit'])->name('settings.email-templates.edit');
    Route::put('settings/email-templates', [EmailTemplateSettingsController::class, 'update'])->name('settings.email-templates.update');
    Route::get('settings/maintenance-page', [MaintenancePageSettingsController::class, 'edit'])->name('settings.maintenance-page.edit');
    Route::put('settings/maintenance-page', [MaintenancePageSettingsController::class, 'update'])->name('settings.maintenance-page.update');
    Route::get('settings/ticket-checkout', [TicketCheckoutSettingsController::class, 'edit'])->name('settings.ticket-checkout.edit');
    Route::put('settings/ticket-checkout', [TicketCheckoutSettingsController::class, 'update'])->name('settings.ticket-checkout.update');
    Route::get('settings/hotel-booking', [HotelBookingSettingsController::class, 'edit'])->name('settings.hotel-booking.edit')->middleware('can:hotel_booking_settings_access');
    Route::put('settings/hotel-booking', [HotelBookingSettingsController::class, 'update'])->name('settings.hotel-booking.update')->middleware('can:hotel_booking_settings_access');
    Route::get('venue/page-settings', [VenuePageSettingsController::class, 'edit'])->name('venue.page-settings.edit')->middleware('can:venue_access');
    Route::put('venue/page-settings', [VenuePageSettingsController::class, 'update'])->name('venue.page-settings.update')->middleware('can:venue_access');
    Route::get('venue/reservation-settings', [VenuePageSettingsController::class, 'editReservation'])->name('venue.reservation-settings.edit')->middleware('can:venue_access');
    Route::put('venue/reservation-settings', [VenuePageSettingsController::class, 'updateReservation'])->name('venue.reservation-settings.update')->middleware('can:venue_access');
    Route::get('venue/tax-settings', [VenuePageSettingsController::class, 'editTax'])->name('venue.tax-settings.edit')->middleware('can:venue_access');
    Route::put('venue/tax-settings', [VenuePageSettingsController::class, 'updateTax'])->name('venue.tax-settings.update')->middleware('can:venue_access');
    Route::resource('venue-hotels', VenueHotelsController::class)->middleware('can:venue_access');
    Route::resource('settings', SettingsController::class);
    Route::get('hotel-bookings', [HotelBookingsController::class, 'index'])->name('hotel-bookings.index')->middleware('can:hotel_booking_access');
    Route::get('hotel-bookings/export', [HotelBookingsController::class, 'export'])->name('hotel-bookings.export')->middleware('can:hotel_booking_access');
    Route::post('hotel-bookings/bulk-status', [HotelBookingsController::class, 'bulkStatus'])->name('hotel-bookings.bulkStatus')->middleware('can:hotel_booking_access');
    Route::delete('hotel-bookings/destroy', [HotelBookingsController::class, 'massDestroy'])->name('hotel-bookings.massDestroy')->middleware('can:hotel_booking_access');
    Route::get('my-contacts/page-settings', [MyContactPageSettingsController::class, 'edit'])->name('my-contacts.page-settings.edit');
    Route::put('my-contacts/page-settings', [MyContactPageSettingsController::class, 'update'])->name('my-contacts.page-settings.update');
    Route::get('my-contacts/check-email', [MyContactsController::class, 'checkEmail'])->name('my-contacts.check-email');
    Route::get('my-contacts/export', [MyContactsController::class, 'export'])->name('my-contacts.export');
    Route::post('my-contacts/import', [MyContactsController::class, 'import'])->name('my-contacts.import');
    Route::post('my-contacts/bulk-action', [MyContactsController::class, 'bulkAction'])->name('my-contacts.bulkAction');
    Route::resource('my-contacts', MyContactsController::class);
    Route::get('world-events/page-settings', [WorldEventsController::class, 'editPageSettings'])->name('world-events.page-settings.edit')->middleware('can:venue_access');
    Route::put('world-events/page-settings', [WorldEventsController::class, 'updatePageSettings'])->name('world-events.page-settings.update')->middleware('can:venue_access');
    Route::delete('world-events/destroy', [WorldEventsController::class, 'massDestroy'])->name('world-events.massDestroy')->middleware('can:venue_access');
    Route::post('world-events/bulk-status', [WorldEventsController::class, 'bulkStatus'])->name('world-events.bulkStatus')->middleware('can:venue_access');
    Route::get('world-events/export', [WorldEventsController::class, 'export'])->name('world-events.export')->middleware('can:venue_access');
    Route::resource('world-events', WorldEventsController::class)->middleware('can:venue_access');

    //  City
    Route::delete('cities/destroy', [CityController::class, 'massDestroy'])
        ->name('cities.massDestroy');
    Route::post('cities/parse-csv-import', [CityController::class, 'parseCsvImport'])
        ->name('cities.parseCsvImport');
    Route::post('cities/process-csv-import', [CityController::class, 'processCsvImport'])
        ->name('cities.processCsvImport');
    Route::resource('cities', CityController::class);

    //  State
    Route::delete('states/destroy', [StateController::class, 'massDestroy'])
        ->name('states.massDestroy');
    Route::post('states/parse-csv-import', [StateController::class, 'parseCsvImport'])
        ->name('states.parseCsvImport');
    Route::post('states/process-csv-import', [StateController::class, 'processCsvImport'])
        ->name('states.processCsvImport');
    Route::resource('states', StateController::class);

    //  Countries
    Route::delete('countries/destroy', [CountriesController::class, 'massDestroy'])
        ->name('countries.massDestroy');
    Route::post('countries/parse-csv-import', [CountriesController::class, 'parseCsvImport'])
        ->name('countries.parseCsvImport');
    Route::post('countries/process-csv-import', [CountriesController::class, 'processCsvImport'])
        ->name('countries.processCsvImport');
    Route::resource('countries', CountriesController::class);

    //  Sponsor Categories
    Route::delete('sponsor-categories/destroy', [SponsorCategoriesController::class, 'massDestroy'])
        ->name('sponsor-categories.massDestroy');
    Route::resource('sponsor-categories', SponsorCategoriesController::class);

    //  Sponsors
    Route::get('sponsors/page-settings', [SponsorPageSettingsController::class, 'edit'])->name('sponsors.page-settings.edit');
    Route::put('sponsors/page-settings', [SponsorPageSettingsController::class, 'update'])->name('sponsors.page-settings.update');
    Route::resource('sponsor-inquiries', SponsorInquiriesController::class)->only(['index', 'show', 'destroy']);
    Route::delete('sponsors/destroy', [SponsorsController::class, 'massDestroy'])
        ->name('sponsors.massDestroy');
    Route::post('sponsors/reorder', [SponsorsController::class, 'reorder'])->name('sponsors.reorder');
    Route::post('sponsors/bulk-status', [SponsorsController::class, 'bulkStatus'])->name('sponsors.bulkStatus');
    Route::get('sponsors/export', [SponsorsController::class, 'export'])->name('sponsors.export');
    Route::resource('sponsors', SponsorsController::class);

    //  Events
    Route::delete('events/destroy', [EventsController::class, 'massDestroy'])
        ->name('events.massDestroy');
    Route::post('events/bulk-status', [EventsController::class, 'bulkStatus'])
        ->name('events.bulkStatus');
    Route::get('events/export', [EventsController::class, 'export'])
        ->name('events.export');
    Route::get('events/page-settings', [EventPageSettingsController::class, 'edit'])->name('events.page-settings.edit');
    Route::put('events/page-settings', [EventPageSettingsController::class, 'update'])->name('events.page-settings.update');
    Route::get('past-upcoming-events', [EventsController::class, 'index'])->name('past-upcoming-events.index');
    Route::resource('events', EventsController::class);
    Route::resource('promo-codes', PromoCodesController::class)->except(['show']);
    Route::get('agenda/page-settings', [AgendaPageSettingsController::class, 'edit'])->name('agenda.page-settings.edit');
    Route::put('agenda/page-settings', [AgendaPageSettingsController::class, 'update'])->name('agenda.page-settings.update');
    Route::post('agenda/days/bulk-delete', [AgendaDaysController::class, 'bulkDelete'])->name('agenda.days.bulkDelete');
    Route::post('agenda/days/bulk-publish', [AgendaDaysController::class, 'bulkPublish'])->name('agenda.days.bulkPublish');
    Route::post('agenda/days/bulk-unpublish', [AgendaDaysController::class, 'bulkUnpublish'])->name('agenda.days.bulkUnpublish');
    Route::post('agenda/days/export', [AgendaDaysController::class, 'export'])->name('agenda.days.export');
    Route::resource('agenda/days', AgendaDaysController::class)->except(['show'])->names('agenda.days');
    Route::post('agenda/rooms/bulk-delete', [AgendaRoomsController::class, 'bulkDelete'])->name('agenda.rooms.bulkDelete');
    Route::post('agenda/rooms/bulk-publish', [AgendaRoomsController::class, 'bulkPublish'])->name('agenda.rooms.bulkPublish');
    Route::post('agenda/rooms/bulk-unpublish', [AgendaRoomsController::class, 'bulkUnpublish'])->name('agenda.rooms.bulkUnpublish');
    Route::post('agenda/rooms/export', [AgendaRoomsController::class, 'export'])->name('agenda.rooms.export');
    Route::resource('agenda/rooms', AgendaRoomsController::class)->except(['show'])->names('agenda.rooms');
    Route::post('agenda/items/bulk-delete', [AgendaItemsController::class, 'bulkDelete'])->name('agenda.items.bulkDelete');
    Route::post('agenda/items/bulk-publish', [AgendaItemsController::class, 'bulkPublish'])->name('agenda.items.bulkPublish');
    Route::post('agenda/items/bulk-unpublish', [AgendaItemsController::class, 'bulkUnpublish'])->name('agenda.items.bulkUnpublish');
    Route::post('agenda/items/export', [AgendaItemsController::class, 'export'])->name('agenda.items.export');
    Route::resource('agenda/items', AgendaItemsController::class)->except(['show'])->names('agenda.items');
    Route::get('event-bookings/export', [EventBookingsController::class, 'export'])->name('event-bookings.export');
    Route::resource('event-bookings', EventBookingsController::class)->only(['index', 'show']);

    //  Speakers
    Route::get('speakers/page-settings', [SpeakerPageSettingsController::class, 'edit'])->name('speakers.page-settings.edit');
    Route::put('speakers/page-settings', [SpeakerPageSettingsController::class, 'update'])->name('speakers.page-settings.update');
    Route::resource('speaker-applications', SpeakerApplicationsController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::get('speakers/check-email', [SpeakersController::class, 'checkEmail'])->name('speakers.check-email');
    Route::get('speakers/export', [SpeakersController::class, 'export'])->name('speakers.export');
    Route::post('speakers/reorder', [SpeakersController::class, 'reorder'])->name('speakers.reorder');
    Route::post('speakers/bulk-activate', [SpeakersController::class, 'bulkActivate'])->name('speakers.bulkActivate');
    Route::post('speakers/bulk-deactivate', [SpeakersController::class, 'bulkDeactivate'])->name('speakers.bulkDeactivate');
    Route::patch('speakers/{speaker}/toggle-status', [SpeakersController::class, 'toggleStatus'])->name('speakers.toggleStatus');
    Route::patch('speakers/{speaker}/toggle-homepage', [SpeakersController::class, 'toggleHomepage'])->name('speakers.toggleHomepage');
    Route::delete('speakers/destroy', [SpeakersController::class, 'massDestroy'])
        ->name('speakers.massDestroy');
    Route::resource('speakers', SpeakersController::class);

    //  Testimonials
    Route::get('testimonials/page-settings', [TestimonialPageSettingsController::class, 'edit'])
        ->name('testimonials.page-settings.edit');
    Route::put('testimonials/page-settings', [TestimonialPageSettingsController::class, 'update'])
        ->name('testimonials.page-settings.update');
    Route::post('testimonials/bulk-status', [TestimonialsController::class, 'bulkStatus'])
        ->name('testimonials.bulkStatus');
    Route::delete('testimonials/destroy', [TestimonialsController::class, 'massDestroy'])
        ->name('testimonials.massDestroy');
    Route::resource('testimonials', TestimonialsController::class);

    //  Members
    Route::delete('members/destroy', [MembersController::class, 'massDestroy'])
        ->name('members.massDestroy');
    Route::resource('members', MembersController::class);

    //  Attractions
    Route::get('attractions/page-settings', [AttractionPageSettingsController::class, 'edit'])->name('attractions.page-settings.edit');
    Route::put('attractions/page-settings', [AttractionPageSettingsController::class, 'update'])->name('attractions.page-settings.update');
    Route::get('attractions/export', [AttractionsController::class, 'export'])
        ->name('attractions.export');
    Route::post('attractions/bulk-status', [AttractionsController::class, 'bulkStatus'])
        ->name('attractions.bulkStatus');
    Route::delete('attractions/destroy', [AttractionsController::class, 'massDestroy'])
        ->name('attractions.massDestroy');
    Route::resource('attractions', AttractionsController::class);

    //  FAQs
    Route::delete('faq-categories/destroy', [FaqCategoriesController::class, 'massDestroy'])
        ->name('faq-categories.massDestroy');
    Route::resource('faq-categories', FaqCategoriesController::class);

    Route::get('faqs/export', [FaqsController::class, 'export'])
        ->name('faqs.export');
    Route::get('faqs/page-settings', [FaqPageSettingsController::class, 'edit'])
        ->name('faqs.page-settings.edit');
    Route::put('faqs/page-settings', [FaqPageSettingsController::class, 'update'])
        ->name('faqs.page-settings.update');
    Route::post('faqs/bulk-status', [FaqsController::class, 'bulkStatus'])
        ->name('faqs.bulkStatus');
    Route::delete('faqs/destroy', [FaqsController::class, 'massDestroy'])
        ->name('faqs.massDestroy');
    Route::resource('faqs', FaqsController::class);
    Route::resource('faq-questions', FaqQuestionsController::class)->only(['index', 'edit', 'update']);

});

// 🔹 Profile Routes (Password Reset & Profile Update)
Route::prefix('profile')->name('profile.')->middleware('auth')->group(function () {
    if (file_exists(app_path('Http/Controllers/Auth/ChangePasswordController.php'))) {
        Route::get('password', [ChangePasswordController::class, 'edit'])->name('password.edit');
        Route::post('password', [ChangePasswordController::class, 'update'])->name('password.update');
        Route::post('profile', [ChangePasswordController::class, 'updateProfile'])->name('password.updateProfile');
        Route::post('profile/destroy', [ChangePasswordController::class, 'destroy'])->name('password.destroyProfile');
    }
});
