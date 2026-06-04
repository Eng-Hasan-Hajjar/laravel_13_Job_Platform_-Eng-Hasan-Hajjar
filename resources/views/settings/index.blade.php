@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
<div class="page-container" style="max-width:1000px; margin:0 auto;">
    
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">
        <i class="fas fa-cog"></i> {{ __('messages.settings') }}
    </h1>
    
    <div class="grid" style="grid-template-columns:280px 1fr;gap:1.5rem">
        
        {{-- Settings Sidebar --}}
        <div>
            <div class="card" style="position:sticky;top:80px">
                <div class="card-body" style="padding:0">
                    <div class="settings-nav">
                        <a href="#profile" class="settings-nav-item active" data-tab="profile">
                            <i class="fas fa-user"></i>
                            <span>{{ __('messages.profile') }}</span>
                        </a>
                        <a href="#preferences" class="settings-nav-item" data-tab="preferences">
                            <i class="fas fa-globe"></i>
                            <span>{{ __('messages.preferences') }}</span>
                        </a>
                        <a href="#password" class="settings-nav-item" data-tab="password">
                            <i class="fas fa-lock"></i>
                            <span>{{ __('messages.password') }}</span>
                        </a>
                        <a href="#notifications" class="settings-nav-item" data-tab="notifications">
                            <i class="fas fa-bell"></i>
                            <span>{{ __('messages.notifications') }}</span>
                        </a>
                        <a href="#data" class="settings-nav-item" data-tab="data">
                            <i class="fas fa-database"></i>
                            <span>{{ __('messages.data') }}</span>
                        </a>
                        <a href="#danger" class="settings-nav-item" data-tab="danger" style="color:var(--danger)">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ __('messages.danger_zone') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Settings Content --}}
        <div>
            
            {{-- Profile Tab --}}
            <div id="profile-tab" class="settings-tab active">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.profile_information') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            {{-- Avatar Section --}}
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.avatar') }}</label>
                                <div style="display:flex;align-items:center;gap:1rem">
                                    <div class="avatar avatar-xl" style="width:80px;height:80px;font-size:2rem">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                                        @else
                                            {{ mb_strtoupper(mb_substr($user->name,0,2)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <input type="file" id="avatar-input" accept="image/*" style="display:none">
                                        <button type="button" class="btn btn-outline" onclick="document.getElementById('avatar-input').click()">
                                            <i class="fas fa-upload"></i> {{ __('messages.change_avatar') }}
                                        </button>
                                        <div class="progress-bar" id="avatar-progress" style="display:none; margin-top:10px; width:200px">
                                            <div class="progress-bar-fill" style="width:0%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.name') }} <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.email') }} <span class="required">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                </div>
                            </div>
                            
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.location') }}</label>
                                    <input type="text" name="location" class="form-control" value="{{ old('location', $user->location) }}">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.bio') }}</label>
                                <textarea name="bio" class="form-control" rows="4">{{ old('bio', $user->bio) }}</textarea>
                            </div>
                            
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('messages.save_changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Preferences Tab --}}
            <div id="preferences-tab" class="settings-tab" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.preferences') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.preferences.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.language') }}</label>
                                    <select name="language" class="form-control">
                                        <option value="en" {{ ($preferences['language'] ?? 'en') === 'en' ? 'selected' : '' }}>English</option>
                                        <option value="ar" {{ ($preferences['language'] ?? 'en') === 'ar' ? 'selected' : '' }}>العربية</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.timezone') }}</label>
                                    <select name="timezone" class="form-control">
                                        @foreach(timezone_identifiers_list() as $tz)
                                            <option value="{{ $tz }}" {{ ($preferences['timezone'] ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="job_alerts" value="1" class="form-check-input" {{ ($preferences['job_alerts'] ?? true) ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ __('messages.job_alerts') }}</span>
                                </label>
                                <p class="form-hint">{{ __('messages.job_alerts_desc') }}</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="newsletter" value="1" class="form-check-input" {{ ($preferences['newsletter'] ?? false) ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ __('messages.newsletter') }}</span>
                                </label>
                                <p class="form-hint">{{ __('messages.newsletter_desc') }}</p>
                            </div>
                            
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('messages.save_preferences') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Password Tab --}}
            <div id="password-tab" class="settings-tab" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.change_password') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.password.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.current_password') }}</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.new_password') }}</label>
                                <input type="password" name="password" class="form-control" required>
                                <p class="form-hint">{{ __('messages.password_requirements') }}</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.confirm_password') }}</label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> {{ __('messages.update_password') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Notifications Tab --}}
            <div id="notifications-tab" class="settings-tab" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.notification_settings') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('settings.preferences.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="email_notifications" value="1" class="form-check-input" {{ ($preferences['email_notifications'] ?? true) ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ __('messages.email_notifications') }}</span>
                                </label>
                                <p class="form-hint">{{ __('messages.email_notifications_desc') }}</p>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-check">
                                    <input type="checkbox" name="push_notifications" value="1" class="form-check-input" {{ ($preferences['push_notifications'] ?? true) ? 'checked' : '' }}>
                                    <span class="form-check-label">{{ __('messages.push_notifications') }}</span>
                                </label>
                                <p class="form-hint">{{ __('messages.push_notifications_desc') }}</p>
                            </div>
                            
                            <div style="display:flex;justify-content:flex-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ __('messages.save_notifications') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Data Tab --}}
            <div id="data-tab" class="settings-tab" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.your_data') }}</span>
                    </div>
                    <div class="card-body">
                        <div class="data-export">
                            <h3>{{ __('messages.export_your_data') }}</h3>
                            <p>{{ __('messages.export_data_desc') }}</p>
                            <a href="{{ route('settings.export-data') }}" class="btn btn-outline">
                                <i class="fas fa-download"></i> {{ __('messages.export_data') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Danger Zone Tab --}}
            <div id="danger-tab" class="settings-tab" style="display:none">
                <div class="card" style="border:1px solid var(--danger)">
                    <div class="card-header" style="border-bottom-color:var(--danger);color:var(--danger)">
                        <span class="card-title">
                            <i class="fas fa-exclamation-triangle"></i> {{ __('messages.danger_zone') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="delete-account">
                            <h3 style="color:var(--danger);margin-bottom:0.5rem">{{ __('messages.delete_account') }}</h3>
                            <p>{{ __('messages.delete_account_desc') }}</p>
                            
                            <form action="{{ route('settings.delete-account') }}" method="POST" 
                                  onsubmit="return confirm('{{ __('messages.delete_account_confirm') }}')">
                                @csrf
                                @method('DELETE')
                                
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.confirm_password') }}</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.type_delete_to_confirm') }}</label>
                                    <input type="text" name="confirmation" class="form-control" placeholder="DELETE" required>
                                </div>
                                
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> {{ __('messages.permanently_delete_account') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Tab switching
document.querySelectorAll('.settings-nav-item').forEach(item => {
    item.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Remove active class from all tabs
        document.querySelectorAll('.settings-nav-item').forEach(nav => nav.classList.remove('active'));
        document.querySelectorAll('.settings-tab').forEach(tab => tab.style.display = 'none');
        
        // Add active class to clicked tab
        item.classList.add('active');
        
        // Show corresponding tab
        const tabName = item.dataset.tab;
        document.getElementById(`${tabName}-tab`).style.display = 'block';
        
        // Update URL hash
        window.location.hash = tabName;
    });
});

// Check URL hash on load
if (window.location.hash) {
    const hash = window.location.hash.substring(1);
    const tabLink = document.querySelector(`.settings-nav-item[data-tab="${hash}"]`);
    if (tabLink) {
        tabLink.click();
    }
}

// Avatar upload with AJAX
document.getElementById('avatar-input')?.addEventListener('change', async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('avatar', file);
    
    const progressBar = document.getElementById('avatar-progress');
    progressBar.style.display = 'block';
    
    try {
        const response = await fetch('{{ route("settings.avatar.upload") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error('Failed to upload avatar');
        }
    } catch (error) {
        toastr.error('Network error');
    } finally {
        progressBar.style.display = 'none';
    }
});
</script>
@endpush

<style>
.settings-nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem 1.25rem;
    text-decoration: none;
    color: var(--text-secondary);
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
}

.settings-nav-item:hover {
    background: var(--bg-hover);
    color: var(--text-primary);
}

.settings-nav-item.active {
    background: var(--bg-hover);
    color: var(--primary);
    border-left-color: var(--primary);
}

.settings-nav-item i {
    width: 20px;
    font-size: 1rem;
}

.progress-bar {
    width: 100%;
    background: var(--border);
    border-radius: 10px;
    overflow: hidden;
}

.progress-bar-fill {
    background: var(--primary);
    height: 4px;
    transition: width 0.3s ease;
}

.data-export {
    text-align: center;
    padding: 2rem;
}

.delete-account {
    text-align: center;
    padding: 1rem;
}
</style>
@endsection