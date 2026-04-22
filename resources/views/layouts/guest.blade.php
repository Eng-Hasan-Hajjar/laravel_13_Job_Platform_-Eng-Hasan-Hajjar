<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'تسجيل الدخول — نظام تشخيص السيارات')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #f97316;
            --accent:  #06b6d4;
            --bg:      #0a0d14;
            --surface: #111827;
            --border:  rgba(255,255,255,0.08);
            --text:    #e2e8f0;
            --muted:   #94a3b8;
        }
        body {
            font-family: 'Tajawal', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animated background mesh */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 20%, rgba(249,115,22,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(6,182,212,0.10) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
        }

        .brand-mark {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 30px;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(249,115,22,0.35);
        }

        .login-title {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            color: #fff;
            margin-bottom: 4px;
        }

        .login-sub {
            text-align: center;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 28px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text);
            margin-bottom: 6px;
        }

        .form-control {
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            color: var(--text);
            border-radius: 10px;
            padding: 11px 14px;
            font-family: 'Tajawal', sans-serif;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus {
            background: rgba(255,255,255,0.06);
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(249,115,22,0.15);
            color: var(--text);
        }

        .form-control::placeholder { color: var(--muted); }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, var(--primary), #ea580c);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-family: 'Tajawal', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 6px 20px rgba(249,115,22,0.4);
            margin-top: 8px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(249,115,22,0.5);
        }

        .invalid-feedback {
            font-size: 12px;
            color: #f87171;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #ef4444 !important;
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: var(--muted);
        }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 20px 0;
            color: var(--muted); font-size: 12px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1;
            height: 1px; background: var(--border);
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        @yield('content')
        <p class="text-center mt-4" style="font-size:12px;color:var(--muted)">
            © {{ date('Y') }} نظام تشخيص السيارات الذكي — جميع الحقوق محفوظة
        </p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>