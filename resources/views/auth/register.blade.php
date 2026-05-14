
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