<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // ── تحديد صفحات لوحة التحكم تلقائياً (بدون تعديل كل View) ──
        //
        // أي صفحة تبدأ بـ: company.* أو admin.* أو user.* = لوحة تحكم
        // باقي الصفحات = عامة مع فوتر
        //
        View::composer('*', function ($view) {
            $name = $view->getName();

            $isDashboard = str_starts_with($name, 'company.')
                        || str_starts_with($name, 'admin.')
                        || str_starts_with($name, 'user.')
                        || in_array($name, [
                            'notifications.index',
                            'settings',
                        ]);

            $view->with('isDashboard', $isDashboard);
        });
    }
}