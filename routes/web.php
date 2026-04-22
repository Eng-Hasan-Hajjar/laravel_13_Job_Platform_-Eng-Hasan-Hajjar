<?php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    HomeController,
    JobController,
    CompanyController,
    NotificationController,
    LanguageController,
    SettingsController,
};
use App\Http\Controllers\User\{
    ProfileController as UserProfileController,
    ApplicationController as UserApplicationController,
};
use App\Http\Controllers\Company\{
    DashboardController as CompanyDashboardController,
    JobController as CompanyJobController,
    ApplicationController as CompanyApplicationController,
    ProfileController as CompanyProfileController,
    ReviewController,
};
use App\Http\Controllers\Admin\{
    DashboardController as AdminDashboardController,
    UserController as AdminUserController,
    CompanyController as AdminCompanyController,
    JobController as AdminJobController,
    NotificationController as AdminNotificationController,
};
use App\Http\Controllers\Auth\{
    LoginController,
    RegisterController,
    ForgotPasswordController,
    ResetPasswordController,
};

/* ==========================================
   PUBLIC ROUTES
   ========================================== */

Route::get('/', [HomeController::class, 'index'])->name('home');

// Language Switch
Route::get('/lang/{locale}', [LanguageController::class, 'switch'])
     ->where('locale', 'ar|en')
     ->name('lang.switch');

// Static Pages
Route::view('/terms',   'pages.terms')  ->name('terms');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/about',   'pages.about')  ->name('about');
Route::view('/contact', 'pages.contact')->name('contact');

// Jobs (Public)
Route::prefix('jobs')->name('jobs.')->group(function () {
    Route::get('/',            [JobController::class, 'index'])      ->name('index');
    Route::get('/search',      [JobController::class, 'index'])      ->name('search');
    Route::get('/recommended', [JobController::class, 'recommended'])->name('recommended')->middleware('auth');
    Route::get('/{job}',       [JobController::class, 'show'])       ->name('show');
    Route::post('/{job}/apply',[JobController::class, 'apply'])      ->name('apply')->middleware('auth');
    Route::post('/{job}/save', [JobController::class, 'save'])       ->name('save')->middleware('auth');
});

// Companies (Public)
Route::prefix('companies')->name('companies.')->group(function () {
    Route::get('/',              [CompanyController::class, 'index'])  ->name('index');
    Route::get('/{company}',     [CompanyController::class, 'show'])   ->name('show');
    Route::post('/{company}/review', [CompanyController::class, 'review'])->name('review')->middleware('auth');
});

/* ==========================================
   AUTH ROUTES
   ========================================== */
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,    'showForm'])->name('login');
    Route::post('/login',   [LoginController::class,    'login']);
    Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
    Route::post('/register',[RegisterController::class, 'register']);
    Route::get('/password/reset',       [ForgotPasswordController::class, 'showForm'])->name('password.request');
    Route::post('/password/email',      [ForgotPasswordController::class, 'sendEmail'])->name('password.email');
    Route::get('/password/reset/{token}',[ResetPasswordController::class, 'showForm'])->name('password.reset');
    Route::post('/password/reset',      [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

/* ==========================================
   AUTHENTICATED USER ROUTES
   ========================================== */
Route::middleware(['auth'])->group(function () {

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');

    // Notifications
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])       ->name('index');
        Route::get('/latest',        [NotificationController::class, 'latest'])      ->name('latest');
        Route::post('/{id}/read',    [NotificationController::class, 'markRead'])    ->name('mark-read');
        Route::post('/mark-all-read',[NotificationController::class, 'markAllRead']) ->name('mark-all-read');
        Route::post('/mark-seen',    [NotificationController::class, 'markSeen'])    ->name('mark-seen');
        Route::get('/unread-count',  [NotificationController::class, 'unreadCount']) ->name('unread-count');
        Route::delete('/{id}',       [NotificationController::class, 'destroy'])     ->name('destroy');
        Route::delete('/',           [NotificationController::class, 'destroyAll'])  ->name('destroy-all');
    });

    /* -------- USER ROUTES -------- */
    Route::middleware('role:user')->prefix('user')->name('user.')->group(function () {
        Route::get('/profile',              [UserProfileController::class, 'index'])          ->name('profile');
        Route::patch('/profile',            [UserProfileController::class, 'update'])         ->name('profile.update');
        Route::patch('/avatar',             [UserProfileController::class, 'updateAvatar'])   ->name('avatar.update');
        Route::patch('/preferences',        [UserProfileController::class, 'updatePrefs'])    ->name('preferences.update');
        Route::patch('/password',           [UserProfileController::class, 'updatePassword']) ->name('password.update');

        Route::get('/cv',                   [UserProfileController::class, 'cvPage'])         ->name('cv');
        Route::post('/cv/upload',           [UserProfileController::class, 'uploadCv'])       ->name('cv.upload');
        Route::get('/cv/download',          [UserProfileController::class, 'downloadCv'])     ->name('cv.download');
        Route::delete('/cv',                [UserProfileController::class, 'deleteCv'])       ->name('cv.delete');

        Route::get('/applications',         [UserApplicationController::class, 'index'])      ->name('applications');
        Route::get('/applications/{id}/cv', [UserApplicationController::class, 'downloadCv']) ->name('application.cv');
        Route::get('/saved-jobs',           [UserProfileController::class, 'savedJobs'])      ->name('saved-jobs');
    });

    /* -------- COMPANY ROUTES -------- */
    Route::middleware('role:company')->prefix('company')->name('company.')->group(function () {
        Route::get('/dashboard', [CompanyDashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile',   [CompanyProfileController::class, 'index']) ->name('profile');
        Route::patch('/profile',  [CompanyProfileController::class, 'update'])->name('profile.update');
        Route::post('/logo',      [CompanyProfileController::class, 'uploadLogo'])->name('logo.upload');

        // Jobs
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/',             [CompanyJobController::class, 'index'])       ->name('index');
            Route::get('/create',       [CompanyJobController::class, 'create'])      ->name('create');
            Route::post('/',            [CompanyJobController::class, 'store'])       ->name('store');
            Route::get('/{job}',        [CompanyJobController::class, 'show'])        ->name('show');
            Route::get('/{job}/edit',   [CompanyJobController::class, 'edit'])        ->name('edit');
            Route::patch('/{job}',      [CompanyJobController::class, 'update'])      ->name('update');
            Route::delete('/{job}',     [CompanyJobController::class, 'destroy'])     ->name('destroy');
            Route::patch('/{job}/toggle',[CompanyJobController::class,'toggleActive'])->name('toggle');
        });

        // Applications
        Route::prefix('applications')->name('applications.')->group(function () {
            Route::get('/',                          [CompanyApplicationController::class, 'index'])       ->name('index');
            Route::get('/{application}',             [CompanyApplicationController::class, 'show'])        ->name('show');
            Route::patch('/{application}/status',    [CompanyApplicationController::class, 'updateStatus'])->name('status');
            Route::get('/{application}/cv',          [CompanyApplicationController::class, 'downloadCv'])  ->name('cv');
        });

        // Reviews
        Route::get('/reviews', [ReviewController::class, 'index'])->name('reviews');
    });

    /* -------- ADMIN ROUTES -------- */
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Settings
        Route::get('/settings',  [AdminDashboardController::class, 'settings'])->name('settings');

        // Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',          [AdminUserController::class, 'index'])  ->name('index');
            Route::get('/{user}',    [AdminUserController::class, 'show'])   ->name('show');
            Route::patch('/{user}/toggle', [AdminUserController::class, 'toggle'])->name('toggle');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });

        // Companies
        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/',              [AdminCompanyController::class, 'index'])  ->name('index');
            Route::get('/{company}',     [AdminCompanyController::class, 'show'])   ->name('show');
            Route::patch('/{company}/verify', [AdminCompanyController::class, 'verify'])->name('verify');
            Route::delete('/{company}',  [AdminCompanyController::class, 'destroy'])->name('destroy');
        });

        // Jobs
        Route::prefix('jobs')->name('jobs.')->group(function () {
            Route::get('/',             [AdminJobController::class, 'index'])  ->name('index');
            Route::patch('/{job}/featured', [AdminJobController::class, 'toggleFeatured'])->name('featured');
            Route::delete('/{job}',     [AdminJobController::class, 'destroy'])->name('destroy');
        });

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/',   [AdminNotificationController::class, 'index'])->name('index');
            Route::post('/send', [AdminNotificationController::class, 'send'])->name('send');
        });
    });
});