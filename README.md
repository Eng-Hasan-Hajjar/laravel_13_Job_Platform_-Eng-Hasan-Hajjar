# 💼 Job Platform - منصة التوظيف
### Laravel 12 | Dark/Light Mode | Arabic/English | Real-time Notifications | AI CV Analysis

---

## 🚀 Quick Start - التثبيت السريع

```bash
# 1. Create Laravel 12 project
composer create-project laravel/laravel jobplatform "^12.0"
cd jobplatform

# 2. Install required packages
composer require smalot/pdfparser phpoffice/phpword intervention/image

# 3. Copy all project files to respective directories

# 4. Configure .env
cp .env.example .env
php artisan key:generate
```

### .env Configuration
```env
APP_NAME="Job Portal"
APP_URL=http://localhost:8000
APP_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jobplatform
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

```bash
# 5. Run migrations & seed
php artisan migrate:fresh --seed

# 6. Create storage link
php artisan storage:link

# 7. Create private disk in config/filesystems.php
# Add: 'private' => ['driver' => 'local', 'root' => storage_path('app/private')]

# 8. Register middleware in bootstrap/app.php
# Add SetLocale and CheckRole middleware

# 9. Start queue worker (for notifications & CV analysis)
php artisan queue:work --queue=default &

# 10. Start server
php artisan serve
```

---

## 📁 File Structure / هيكل الملفات

```
jobplatform/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── JobController.php
│   │   │   ├── CompanyController.php
│   │   │   ├── NotificationController.php
│   │   │   ├── LanguageController.php
│   │   │   ├── User/
│   │   │   │   ├── ProfileController.php
│   │   │   │   └── ApplicationController.php
│   │   │   ├── Company/
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── JobController.php
│   │   │   │   ├── ApplicationController.php
│   │   │   │   └── ProfileController.php
│   │   │   └── Admin/
│   │   │       ├── DashboardController.php
│   │   │       ├── UserController.php
│   │   │       ├── CompanyController.php
│   │   │       ├── JobController.php
│   │   │       └── NotificationController.php
│   │   └── Middleware/
│   │       ├── SetLocale.php
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Job.php
│   │   ├── Company.php
│   │   ├── JobApplication.php
│   │   ├── Category.php
│   │   └── CompanyReview.php
│   ├── Notifications/
│   │   ├── ApplicationReceived.php
│   │   ├── ApplicationStatusChanged.php
│   │   ├── ApplicationSubmitted.php
│   │   ├── NewJobPosted.php
│   │   └── AdminBroadcastNotification.php
│   ├── Services/
│   │   ├── CvAnalysisService.php
│   │   └── JobRecommendationService.php
│   └── Jobs/
│       ├── AnalyzeCvJob.php
│       └── NotifySubscribedUsers.php
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── app.blade.php
│   │   │   └── auth.blade.php
│   │   ├── components/
│   │   │   ├── navbar.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   ├── job-card.blade.php
│   │   │   ├── notification-panel.blade.php
│   │   │   └── flash-messages.blade.php
│   │   ├── auth/
│   │   │   ├── login.blade.php
│   │   │   └── register.blade.php
│   │   ├── home.blade.php
│   │   ├── jobs/
│   │   │   ├── index.blade.php
│   │   │   └── show.blade.php
│   │   ├── user/
│   │   │   ├── profile.blade.php
│   │   │   └── applications.blade.php
│   │   ├── company/
│   │   │   └── dashboard.blade.php
│   │   ├── admin/
│   │   │   └── dashboard.blade.php
│   │   └── notifications/
│   │       └── index.blade.php
│   └── lang/
│       ├── ar/messages.php
│       └── en/messages.php
├── public/
│   ├── css/app.css
│   └── js/app.js
├── routes/web.php
└── database/
    ├── migrations/
    └── seeders/DatabaseSeeder.php
```

---

## 🔑 Test Accounts / حسابات الاختبار

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@jobportal.com | password |
| Company | company@techcorp.com | password |
| User | user@example.com | password |

---

## ✨ Features / المميزات

### 🌐 Bilingual Support
- Arabic (RTL) / English (LTR)
- Language toggle in navbar
- Stored per user in database

### 🌙 Dark / Light Mode
- Toggle button in navbar
- Saved in localStorage
- Full CSS variable system

### 🔔 Notification System
- Real-time polling every 30 seconds
- Badge counter with pulse animation
- Notification dropdown panel
- Full notifications page with filters
- Email notifications via queue
- Types: job, application, system, alert
- Mark read / mark all read
- Delete notifications
- Admin bulk notifications

### 📄 CV Analysis (PDF/DOCX)
- Powered by `smalot/pdfparser` + `phpoffice/phpword`
- Extracts: technical skills, soft skills, languages, education
- Estimates years of experience
- Calculates CV score (0-100)
- Extracts contact info (email, phone, LinkedIn, GitHub)
- Runs in background via Laravel Queue

### 🤖 AI Job Recommendations
- Score-based matching algorithm
- Factors: skills, location, job type, experience, salary, recency
- Displayed on profile page and dedicated page

### 👤 User Features
- Register / Login
- Profile with avatar upload
- CV upload and analysis
- Browse & search jobs (with advanced filters)
- Apply for jobs with cover letter
- Track application status (visual timeline)
- Save/bookmark jobs
- AI job recommendations

### 🏢 Company Features
- Company profile with logo
- Post / Edit / Delete jobs
- Manage applications (accept/reject/shortlist)
- Application status with email notification to candidate
- Dashboard with Charts.js graphs
- Company reviews & ratings

### 🛡️ Admin Features
- System overview dashboard
- Manage users (activate/deactivate/delete)
- Manage companies (verify/delete)
- Manage jobs (feature/delete)
- Send bulk notifications
- Registration & job-type charts

---

## 📦 Dependencies

```bash
# Core
composer require smalot/pdfparser
composer require phpoffice/phpword
composer require intervention/image

# Frontend (CDN - no build step needed)
# - Font Awesome 6.5
# - Google Fonts (Cairo + Inter)
# - Chart.js 4.4
# - jQuery 3.7
# - Toastr.js
```

---

## ⚙️ Middleware Registration (bootstrap/app.php)

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,
    ]);
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

---

## 🗄️ Queue Setup

```bash
# Create jobs table
php artisan queue:table
php artisan migrate

# Run worker
php artisan queue:work

# Or with supervisor (production)
# See: https://laravel.com/docs/queues#supervisor-configuration
```

---

**Built with ❤️ for Aleppo University Graduation Project**