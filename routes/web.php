<?php

use App\Http\Controllers\Admin\AuditLogsController;
use App\Http\Controllers\Admin\AuthPageSettingsController;
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
use App\Http\Controllers\Admin\RolesController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Admin\FaqQuestionsController;
use App\Http\Controllers\Admin\FaqCategoriesController;
use App\Http\Controllers\Admin\FaqPageSettingsController;
use App\Http\Controllers\Admin\FrontendFooterController;
use App\Http\Controllers\Admin\FrontendHeaderController;
use App\Http\Controllers\Admin\FrontendPageAccessController;
use App\Http\Controllers\Admin\FrontendPagesController;
use App\Http\Controllers\Admin\FrontendSeoTitlesController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\ChangePasswordController;
use App\Http\Controllers\FrontendContactController;
use App\Http\Controllers\FrontendDownloadController;
use App\Http\Controllers\FrontendFaqController;
use App\Http\Controllers\FrontendNewsletterController;
use App\Http\Controllers\FrontendPageController;
use App\Http\Controllers\FrontendMyContactController;
use App\Http\Controllers\FrontendTrainController;
use App\Http\Controllers\TrainAuthController;
use App\Http\Controllers\TrainOwnerAuthController;
use App\Http\Controllers\TrainOwnerTrainsController;
use App\Http\Controllers\TrainBookingController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// The train search is the public landing page.
Route::get('/', [FrontendTrainController::class, 'index'])->name('frontend.trains.home')->middleware('page.access');
Route::get('/index.html', [FrontendTrainController::class, 'index'])->name('frontend.trains.legacy-home')->middleware('page.access');
Route::post('/newsletter-subscribe', [FrontendNewsletterController::class, 'store'])->name('frontend.newsletter.store')->middleware('page.access');
Route::get('/pages/{slug}', [FrontendPageController::class, 'show'])->name('frontend.page.show')->middleware('page.access');
Route::get('/faqs', [FrontendFaqController::class, 'index'])->name('frontend.faqs.index')->middleware('page.access');
Route::post('/faqs/ask', [FrontendFaqController::class, 'storeQuestion'])->name('frontend.faqs.store')->middleware('page.access');
Route::get('/contact-us', [FrontendContactController::class, 'index'])->name('frontend.contact.index')->middleware('page.access');
Route::post('/contact-us', [FrontendContactController::class, 'store'])->name('frontend.contact.store')->middleware('page.access');
Route::get('/my-contacts', [FrontendMyContactController::class, 'index'])->name('frontend.my-contacts.index')->middleware('page.access');
Route::get('/add-my-contact', [FrontendMyContactController::class, 'create'])->name('frontend.my-contacts.create')->middleware('page.access');
Route::get('/add-my-contact/check-email', [FrontendMyContactController::class, 'checkOfficialEmail'])->name('frontend.my-contacts.check-email')->middleware('page.access');
Route::post('/add-my-contact', [FrontendMyContactController::class, 'store'])->name('frontend.my-contacts.store')->middleware('page.access');
Route::get('/download', [FrontendDownloadController::class, 'index'])->name('frontend.download.index')->middleware('page.access');
Route::get('/trains', [FrontendTrainController::class, 'index'])->name('frontend.trains.search')->middleware('page.access');
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
Route::get('/home', function () {
    if (auth()->check() && ! auth()->user()->is_admin) {
        return redirect()->route('frontend.trains.home');
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
    Route::resource('settings', SettingsController::class);
    Route::get('my-contacts/page-settings', [MyContactPageSettingsController::class, 'edit'])->name('my-contacts.page-settings.edit');
    Route::put('my-contacts/page-settings', [MyContactPageSettingsController::class, 'update'])->name('my-contacts.page-settings.update');
    Route::get('my-contacts/check-email', [MyContactsController::class, 'checkEmail'])->name('my-contacts.check-email');
    Route::get('my-contacts/export', [MyContactsController::class, 'export'])->name('my-contacts.export');
    Route::post('my-contacts/import', [MyContactsController::class, 'import'])->name('my-contacts.import');
    Route::post('my-contacts/bulk-action', [MyContactsController::class, 'bulkAction'])->name('my-contacts.bulkAction');
    Route::resource('my-contacts', MyContactsController::class);
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

    //  Members
    Route::delete('members/destroy', [MembersController::class, 'massDestroy'])
        ->name('members.massDestroy');
    Route::resource('members', MembersController::class);

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
