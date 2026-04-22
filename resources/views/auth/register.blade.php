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


{{-- ========== resources/views/auth/register.blade.php ========== --}}
@extends('layouts.auth')
@section('title', __('messages.register'))

@section('content')
<div class="auth-card animate-scale-in">
    <div class="auth-header">
        <a href="{{ url('/') }}" class="navbar-brand" style="justify-content:center;margin-bottom:1.5rem">
            <div class="brand-icon"><i class="fas fa-briefcase"></i></div>
            <span>{{ config('app.name') }}</span>
        </a>
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.375rem">{{ __('messages.create_account') }}</h1>
        <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.register_subtitle') }}</p>
    </div>

    <!-- Account Type Selection -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.5rem">
        <label id="type-user" style="border:2px solid var(--primary);border-radius:var(--radius-lg);padding:1rem;cursor:pointer;text-align:center;transition:var(--transition);background:var(--primary-light)">
            <input type="radio" name="role" value="user" checked style="display:none">
            <div style="font-size:1.75rem;margin-bottom:.375rem">👤</div>
            <div style="font-weight:700;font-size:.875rem;color:var(--primary)">{{ __('messages.job_seeker') }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.125rem">{{ __('messages.find_jobs') }}</div>
        </label>
        <label id="type-company" style="border:2px solid var(--border);border-radius:var(--radius-lg);padding:1rem;cursor:pointer;text-align:center;transition:var(--transition)">
            <input type="radio" name="role" value="company" style="display:none">
            <div style="font-size:1.75rem;margin-bottom:.375rem">🏢</div>
            <div style="font-weight:700;font-size:.875rem">{{ __('messages.employer') }}</div>
            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.125rem">{{ __('messages.post_jobs') }}</div>
        </label>
    </div>

    <form action="{{ route('register') }}" method="POST" data-validate id="registerForm">
        @csrf
        <input type="hidden" name="role" id="roleInput" value="user">

        <div class="form-group">
            <label class="form-label">{{ __('messages.full_name') }} <span class="required">*</span></label>
            <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   value="{{ old('name') }}" required placeholder="{{ __('messages.name_placeholder') }}" autofocus>
            @error('name')<div class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.email') }} <span class="required">*</span></label>
            <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                   value="{{ old('email') }}" required placeholder="you@example.com">
            @error('email')<div class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <!-- Company name (shown only when company selected) -->
        <div class="form-group" id="companyField" style="display:none">
            <label class="form-label">{{ __('messages.company_name') }} <span class="required">*</span></label>
            <input type="text" name="company_name" class="form-control {{ $errors->has('company_name') ? 'is-invalid' : '' }}"
                   value="{{ old('company_name') }}" placeholder="{{ __('messages.company_name_placeholder') }}">
            @error('company_name')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.password') }} <span class="required">*</span></label>
            <div class="input-group">
                <input type="password" name="password" id="reg-password"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       required placeholder="••••••••" oninput="checkPasswordStrength(this.value)">
                <button type="button" class="input-group-text" onclick="togglePassword('reg-password', 'reg-eye')" style="cursor:pointer">
                    <i class="fas fa-eye" id="reg-eye"></i>
                </button>
            </div>
            <!-- Password Strength -->
            <div style="margin-top:.5rem">
                <div class="progress" style="margin-bottom:.25rem">
                    <div id="strengthBar" class="progress-bar" style="width:0;transition:width .3s,background .3s"></div>
                </div>
                <div id="strengthLabel" style="font-size:.75rem;color:var(--text-muted)"></div>
            </div>
            @error('password')<div class="form-error">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label">{{ __('messages.confirm_password') }} <span class="required">*</span></label>
            <input type="password" name="password_confirmation" class="form-control" required placeholder="••••••••">
        </div>

        <div style="display:flex;align-items:flex-start;gap:.5rem;margin-bottom:1.5rem">
            <input type="checkbox" name="terms" id="terms" required style="accent-color:var(--primary);width:16px;height:16px;margin-top:2px;flex-shrink:0">
            <label for="terms" style="font-size:.8rem;color:var(--text-secondary);cursor:pointer;line-height:1.4">
                {{ __('messages.agree_to') }}
                <a href="{{ route('terms') }}" target="_blank" style="color:var(--primary)">{{ __('messages.terms') }}</a>
                {{ __('messages.and') }}
                <a href="{{ route('privacy') }}" target="_blank" style="color:var(--primary)">{{ __('messages.privacy') }}</a>
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">
            <i class="fas fa-user-plus"></i> {{ __('messages.create_account') }}
        </button>
    </form>

    <p style="text-align:center;font-size:.875rem;color:var(--text-secondary);margin-top:1.5rem">
        {{ __('messages.have_account') }}
        <a href="{{ route('login') }}" style="color:var(--primary);font-weight:600">{{ __('messages.login') }}</a>
    </p>
</div>

@push('scripts')
<script>
// Account type toggle
document.querySelectorAll('[name="role"]').forEach(radio => {
    radio.closest('label').addEventListener('click', function() {
        document.querySelectorAll('[id^="type-"]').forEach(l => {
            l.style.borderColor = 'var(--border)';
            l.style.background = '';
        });
        this.style.borderColor = 'var(--primary)';
        this.style.background = 'var(--primary-light)';

        const role = this.querySelector('input').value;
        document.getElementById('roleInput').value = role;
        document.getElementById('companyField').style.display = role === 'company' ? 'block' : 'none';
        document.querySelector('[name="company_name"]').required = role === 'company';
    });
});

function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

function checkPasswordStrength(val) {
    const bar = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    let strength = 0;
    if (val.length >= 8)  strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const levels = [
        { w: '25%',  color: '#ef4444', text: '{{ __("messages.weak") }}' },
        { w: '50%',  color: '#f59e0b', text: '{{ __("messages.fair") }}' },
        { w: '75%',  color: '#3b82f6', text: '{{ __("messages.good") }}' },
        { w: '100%', color: '#10b981', text: '{{ __("messages.strong") }}' },
    ];
    const lvl = levels[Math.max(0, strength - 1)] || levels[0];
    bar.style.width = val ? lvl.w : '0';
    bar.style.background = lvl.color;
    label.textContent = val ? lvl.text : '';
    label.style.color = val ? lvl.color : 'var(--text-muted)';
}
</script>
@endpush
@endsection