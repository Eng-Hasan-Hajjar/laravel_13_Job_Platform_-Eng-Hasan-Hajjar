{{-- resources/views/layouts/auth.blade.php --}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
        }
        .auth-left {
            background: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #7c3aed 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M50 50c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10s-10-4.477-10-10 4.477-10 10-10zM10 10c0-5.523 4.477-10 10-10s10 4.477 10 10-4.477 10-10 10c0 5.523-4.477 10-10 10S0 25.523 0 20s4.477-10 10-10z' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .auth-left-content { position: relative; z-index: 1; text-align: center; max-width: 400px; }
        .auth-right {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--bg-body);
            overflow-y: auto;
        }
        .auth-card { width: 100%; max-width: 440px; }
        .auth-header { text-align: center; margin-bottom: 2rem; }
        /* Theme & Lang toggles */
        .auth-controls {
            position: fixed;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: .5rem;
            z-index: 100;
        }
        [dir="rtl"] .auth-controls { right: auto; left: 1rem; }

        @media (max-width: 768px) {
            .auth-wrapper { grid-template-columns: 1fr; }
            .auth-left { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body class="{{ app()->getLocale() === 'ar' ? 'font-arabic' : 'font-latin' }}">

    <!-- Controls -->
    <div class="auth-controls">
        <button id="themeToggle" class="nav-icon-btn"><i class="fas fa-moon"></i></button>
        <button id="langSwitcher" class="nav-icon-btn" style="font-size:.75rem;font-weight:700">
            {{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}
        </button>
    </div>

    <div class="auth-wrapper">
        <!-- Left: Decorative -->
        <div class="auth-left">
            <div class="auth-left-content animate-fade-in">
                <div style="font-size:4rem;margin-bottom:1.5rem">💼</div>
                <h2 style="font-size:2rem;font-weight:800;margin-bottom:1rem;line-height:1.2">
                    {{ __('messages.auth_left_title') }}
                </h2>
                <p style="opacity:.85;line-height:1.7;margin-bottom:2rem">
                    {{ __('messages.auth_left_desc') }}
                </p>
                <!-- Stats -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    @foreach([
                        ['value'=>'10K+','label'=>__('messages.active_jobs')],
                        ['value'=>'5K+', 'label'=>__('messages.companies')],
                        ['value'=>'50K+','label'=>__('messages.job_seekers')],
                        ['value'=>'95%', 'label'=>__('messages.success_rate')],
                    ] as $s)
                    <div style="background:rgba(255,255,255,.12);border-radius:var(--radius-lg);padding:1rem;text-align:center;backdrop-filter:blur(8px)">
                        <div style="font-size:1.5rem;font-weight:800">{{ $s['value'] }}</div>
                        <div style="font-size:.75rem;opacity:.8;margin-top:.25rem">{{ $s['label'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Right: Form -->
        <div class="auth-right">
            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>

