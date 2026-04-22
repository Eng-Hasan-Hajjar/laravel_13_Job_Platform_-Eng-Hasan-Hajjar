{{-- resources/views/settings.blade.php --}}
@extends('layouts.app')
@section('title', __('messages.settings'))

@section('content')
<div class="page-container" style="max-width:700px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">⚙️ {{ __('messages.settings') }}</h1>

    <!-- Appearance -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-header"><span class="card-title">🎨 {{ __('messages.appearance') }}</span></div>
        <div class="card-body">
            <!-- Theme -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 0;border-bottom:1px solid var(--border)">
                <div>
                    <div style="font-weight:600;font-size:.9rem">{{ __('messages.theme') }}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:.125rem">{{ __('messages.theme_desc') }}</div>
                </div>
                <div style="display:flex;gap:.5rem">
                    <button onclick="ThemeManager.apply('light')" id="lightBtn"
                            class="btn btn-sm" style="gap:.375rem">
                        <i class="fas fa-sun" style="color:var(--warning)"></i> {{ __('messages.light') }}
                    </button>
                    <button onclick="ThemeManager.apply('dark')" id="darkBtn"
                            class="btn btn-sm" style="gap:.375rem">
                        <i class="fas fa-moon" style="color:var(--primary)"></i> {{ __('messages.dark') }}
                    </button>
                </div>
            </div>

            <!-- Language -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 0">
                <div>
                    <div style="font-weight:600;font-size:.9rem">{{ __('messages.language') }}</div>
                    <div style="font-size:.8rem;color:var(--text-muted);margin-top:.125rem">{{ __('messages.language_desc') }}</div>
                </div>
                <div style="display:flex;gap:.5rem">
                    <a href="{{ route('lang.switch', 'en') }}"
                       class="btn btn-sm {{ app()->getLocale() === 'en' ? 'btn-primary' : 'btn-ghost' }}">
                        🇬🇧 English
                    </a>
                    <a href="{{ route('lang.switch', 'ar') }}"
                       class="btn btn-sm {{ app()->getLocale() === 'ar' ? 'btn-primary' : 'btn-ghost' }}">
                        🇸🇦 العربية
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications Settings -->
    @auth
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-header"><span class="card-title">🔔 {{ __('messages.notification_settings') }}</span></div>
        <div class="card-body">
            <form action="{{ route('settings.notifications') }}" method="POST">
                @csrf @method('PATCH')
                @foreach([
                    ['key'=>'email_new_job',    'label'=>__('messages.notif_new_job'),    'desc'=>__('messages.notif_new_job_desc')],
                    ['key'=>'email_app_status', 'label'=>__('messages.notif_app_status'), 'desc'=>__('messages.notif_app_status_desc')],
                    ['key'=>'email_newsletter', 'label'=>__('messages.notif_newsletter'), 'desc'=>__('messages.notif_newsletter_desc')],
                ] as $notif)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.875rem 0;border-bottom:1px solid var(--border)">
                    <div>
                        <div style="font-weight:600;font-size:.875rem">{{ $notif['label'] }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">{{ $notif['desc'] }}</div>
                    </div>
                    <label style="position:relative;width:44px;height:24px;cursor:pointer;flex-shrink:0">
                        <input type="checkbox" name="{{ $notif['key'] }}" value="1"
                               checked style="opacity:0;position:absolute;inset:0;cursor:pointer;z-index:1"
                               onchange="updateToggleUI(this,'bar-{{ $notif['key'] }}','thumb-{{ $notif['key'] }}')">
                        <div id="bar-{{ $notif['key'] }}" style="position:absolute;inset:0;border-radius:12px;background:var(--success);transition:var(--transition)"></div>
                        <div id="thumb-{{ $notif['key'] }}" style="position:absolute;top:2px;left:22px;width:20px;height:20px;border-radius:50%;background:white;transition:var(--transition)"></div>
                    </label>
                </div>
                @endforeach
                <div style="padding-top:1rem">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-save"></i> {{ __('messages.save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Danger Zone -->
    <div class="card" style="border-color:rgba(239,68,68,.3)">
        <div class="card-header" style="background:rgba(239,68,68,.04)">
            <span class="card-title" style="color:var(--danger)">⚠️ {{ __('messages.danger_zone') }}</span>
        </div>
        <div class="card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem">
                <div>
                    <div style="font-weight:600;font-size:.875rem">{{ __('messages.delete_account') }}</div>
                    <div style="font-size:.8rem;color:var(--text-muted)">{{ __('messages.delete_account_desc') }}</div>
                </div>
                <button class="btn btn-danger btn-sm"
                        data-confirm-delete="{{ __('messages.delete_account_confirm') }}">
                    <i class="fas fa-trash"></i> {{ __('messages.delete_account') }}
                </button>
            </div>
        </div>
    </div>
    @endauth
</div>

@push('scripts')
<script>
// Highlight active theme button
document.addEventListener('DOMContentLoaded', () => {
    const theme = localStorage.getItem('theme') || 'light';
    document.getElementById(theme + 'Btn')?.classList.add('btn-primary');
    document.getElementById(theme === 'dark' ? 'light' : 'dark' + 'Btn')?.classList.add('btn-ghost');
});

function updateToggleUI(input, barId, thumbId) {
    const bar = document.getElementById(barId);
    const thumb = document.getElementById(thumbId);
    if (input.checked) {
        bar.style.background = 'var(--success)'; thumb.style.left = '22px';
    } else {
        bar.style.background = 'var(--border)'; thumb.style.left = '2px';
    }
}
</script>
@endpush
@endsection


{{-- ========== resources/views/user/saved-jobs.blade.php ========== --}}
@extends('layouts.app')
@section('title', __('messages.saved_jobs'))

@section('content')
<div class="page-container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">🔖 {{ __('messages.saved_jobs') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ $jobs->total() }} {{ __('messages.saved') }}</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn btn-outline">
            <i class="fas fa-search"></i> {{ __('messages.browse_more') }}
        </a>
    </div>

    @forelse($jobs as $job)
        @include('components.job-card', ['job' => $job])
    @empty
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-bookmark"></i></div>
        <h3>{{ __('messages.no_saved_jobs') }}</h3>
        <p>{{ __('messages.save_jobs_hint') }}</p>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary" style="margin-top:1rem">
            <i class="fas fa-search"></i> {{ __('messages.browse_jobs') }}
        </a>
    </div>
    @endforelse

    {{ $jobs->links() }}
</div>
@endsection