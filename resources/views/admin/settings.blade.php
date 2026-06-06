@extends('layouts.app')
@section('title', __('messages.system_settings'))

@section('content')
<div class="page-container" style="max-width:900px">

    <div style="margin-bottom:1.5rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
            <i class="fas fa-cog" style="color:var(--primary)"></i> {{ __('messages.system_settings') }}
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.system_settings_desc') }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.25rem">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <!-- System Info -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><span class="card-title">{{ __('messages.system_information') }}</span></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
                @foreach([
                    ['label'=>'Application',   'value'=>config('app.name'),     'icon'=>'fa-tag',        'color'=>'primary'],
                    ['label'=>'Environment',   'value'=>config('app.env'),      'icon'=>'fa-server',     'color'=>'success'],
                    ['label'=>'Laravel',       'value'=>app()->version(),       'icon'=>'fa-layer-group','color'=>'danger'],
                    ['label'=>'PHP',           'value'=>PHP_VERSION,            'icon'=>'fa-code',       'color'=>'info'],
                    ['label'=>'Database',      'value'=>config('database.default'), 'icon'=>'fa-database', 'color'=>'warning'],
                    ['label'=>'Cache Driver',  'value'=>config('cache.default'),'icon'=>'fa-bolt',       'color'=>'primary'],
                    ['label'=>'Queue Driver',  'value'=>config('queue.default'),'icon'=>'fa-list',       'color'=>'success'],
                    ['label'=>'Timezone',      'value'=>config('app.timezone'), 'icon'=>'fa-clock',      'color'=>'info'],
                ] as $item)
                <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="width:36px;height:36px;background:var(--{{ $item['color'] }});color:white;border-radius:var(--radius);display:flex;align-items:center;justify-content:center">
                        <i class="fas {{ $item['icon'] }}"></i>
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em">{{ $item['label'] }}</div>
                        <div style="font-weight:700;font-size:.875rem">{{ $item['value'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-header"><span class="card-title">{{ __('messages.platform_overview') }}</span></div>
        <div class="card-body">
            @php
                $totalUsers     = \App\Models\User::count();
                $totalCompanies = \App\Models\Company::count();
                $totalJobs      = \App\Models\Job::count();
                $totalApps      = \App\Models\JobApplication::count();
                $totalReviews   = class_exists(\App\Models\CompanyReview::class) ? \App\Models\CompanyReview::count() : 0;
                $diskFree       = function_exists('disk_free_space') ? round(disk_free_space('/') / 1024 / 1024 / 1024, 1) : 0;
            @endphp
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1rem">
                <div style="text-align:center;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--primary)">{{ $totalUsers }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.users') }}</div>
                </div>
                <div style="text-align:center;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--warning)">{{ $totalCompanies }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.companies') }}</div>
                </div>
                <div style="text-align:center;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--success)">{{ $totalJobs }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.jobs') }}</div>
                </div>
                <div style="text-align:center;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--info)">{{ $totalApps }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.applications') }}</div>
                </div>
                <div style="text-align:center;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:1.5rem;font-weight:800;color:var(--danger)">{{ $totalReviews }}</div>
                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.reviews') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Maintenance Actions -->
    <div class="card">
        <div class="card-header"><span class="card-title">{{ __('messages.maintenance') }}</span></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
                <div style="padding:1rem;border:1px solid var(--border);border-radius:var(--radius)">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <i class="fas fa-broom" style="color:var(--primary)"></i>
                        <strong style="font-size:.875rem">{{ __('messages.clear_cache') }}</strong>
                    </div>
                    <code style="display:block;padding:.375rem .625rem;background:var(--bg-hover);border-radius:var(--radius-sm);font-size:.75rem;color:var(--text-secondary)">php artisan cache:clear</code>
                </div>

                <div style="padding:1rem;border:1px solid var(--border);border-radius:var(--radius)">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <i class="fas fa-sync" style="color:var(--success)"></i>
                        <strong style="font-size:.875rem">{{ __('messages.optimize') }}</strong>
                    </div>
                    <code style="display:block;padding:.375rem .625rem;background:var(--bg-hover);border-radius:var(--radius-sm);font-size:.75rem;color:var(--text-secondary)">php artisan optimize</code>
                </div>

                <div style="padding:1rem;border:1px solid var(--border);border-radius:var(--radius)">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <i class="fas fa-database" style="color:var(--info)"></i>
                        <strong style="font-size:.875rem">{{ __('messages.run_migrations') }}</strong>
                    </div>
                    <code style="display:block;padding:.375rem .625rem;background:var(--bg-hover);border-radius:var(--radius-sm);font-size:.75rem;color:var(--text-secondary)">php artisan migrate</code>
                </div>

                <div style="padding:1rem;border:1px solid var(--border);border-radius:var(--radius)">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <i class="fas fa-bolt" style="color:var(--warning)"></i>
                        <strong style="font-size:.875rem">{{ __('messages.live_stats') }}</strong>
                    </div>
                    <a href="{{ route('live-stats') }}" style="font-size:.75rem;color:var(--primary);text-decoration:none">{{ __('messages.view_live') }} →</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection