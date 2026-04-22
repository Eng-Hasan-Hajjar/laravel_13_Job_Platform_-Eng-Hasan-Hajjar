{{-- resources/views/auth/login.blade.php --}}
@extends('layouts.auth')
@section('title', __('messages.login'))

@section('content')
<div class="auth-card animate-scale-in">
    <div class="auth-header">
        <a href="{{ url('/') }}" class="navbar-brand" style="justify-content:center;margin-bottom:1.5rem">
            <div class="brand-icon"><i class="fas fa-briefcase"></i></div>
            <span>{{ config('app.name') }}</span>
        </a>
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.375rem">{{ __('messages.welcome_back') }}</h1>
        <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.login_subtitle') }}</p>
    </div>

    <form action="{{ route('login') }}" method="POST" data-validate>
        @csrf
        <div class="form-group">
            <label class="form-label">{{ __('messages.email') }} <span class="required">*</span></label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                       value="{{ old('email') }}" required autocomplete="email" autofocus
                       placeholder="you@example.com">
            </div>
            @error('email')<div class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between">
                {{ __('messages.password') }} <span class="required">*</span>
                <a href="{{ route('password.request') }}" style="font-size:.8rem;color:var(--primary);font-weight:500">
                    {{ __('messages.forgot_password') }}
                </a>
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" name="password" id="passwordInput"
                       class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                       required autocomplete="current-password">
                <button type="button" class="input-group-text" onclick="togglePassword()" style="cursor:pointer;border-left:none">
                    <i class="fas fa-eye" id="eyeIcon"></i>
                </button>
            </div>
            @error('password')<div class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
        </div>

        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1.25rem">
            <input type="checkbox" name="remember" id="remember" style="accent-color:var(--primary);width:16px;height:16px">
            <label for="remember" style="font-size:.875rem;color:var(--text-secondary);cursor:pointer">
                {{ __('messages.remember_me') }}
            </label>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;padding:.875rem">
            <i class="fas fa-sign-in-alt"></i> {{ __('messages.login') }}
        </button>
    </form>

    <div style="margin:1.5rem 0;display:flex;align-items:center;gap:.75rem;color:var(--text-muted);font-size:.8rem">
        <div style="flex:1;height:1px;background:var(--border)"></div>
        {{ __('messages.or') }}
        <div style="flex:1;height:1px;background:var(--border)"></div>
    </div>

    <p style="text-align:center;font-size:.875rem;color:var(--text-secondary)">
        {{ __('messages.no_account') }}
        <a href="{{ route('register') }}" style="color:var(--primary);font-weight:600">{{ __('messages.create_account') }}</a>
    </p>
</div>

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}
</script>
@endpush
@endsection