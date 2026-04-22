@extends('layouts.app')
@section('title', __('messages.company_dashboard'))

@section('content')
<div class="page-container">

    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
                {{ __('messages.welcome_back') }}, {{ $company->name }}!
            </h1>
            <p style="color:var(--text-secondary);font-size:.875rem">
                {{ now()->format('l, d F Y') }}
            </p>
        </div>
        <div style="display:flex;gap:.75rem">
            <a href="{{ route('company.profile') }}" class="btn btn-ghost">
                <i class="fas fa-building"></i> {{ __('messages.edit_profile') }}
            </a>
            <a href="{{ route('company.jobs.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> {{ __('messages.post_job') }}
            </a>
        </div>
    </div>

    <!-- Verification Banner -->
    @if(!$company->is_verified)
    <div class="alert alert-warning" style="margin-bottom:1.5rem">
        <i class="fas fa-exclamation-triangle"></i>
        <div>
            <strong>{{ __('messages.not_verified') }}</strong>
            {{ __('messages.verification_pending_desc') }}
        </div>
    </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-4" style="margin-bottom:2rem">
        <div class="stat-card primary">
            <div class="stat-card-icon"><i class="fas fa-briefcase"></i></div>
            <div>
                <div class="stat-value">{{ $stats['active_jobs'] }}</div>
                <div class="stat-label">{{ __('messages.active_jobs') }}</div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> {{ $stats['jobs_this_month'] }} {{ __('messages.this_month') }}</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-card-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="stat-value">{{ $stats['total_applications'] }}</div>
                <div class="stat-label">{{ __('messages.total_applications') }}</div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> {{ $stats['new_applications'] }} {{ __('messages.new') }}</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-card-icon"><i class="fas fa-clock"></i></div>
            <div>
                <div class="stat-value">{{ $stats['pending_applications'] }}</div>
                <div class="stat-label">{{ __('messages.pending_review') }}</div>
                @if($stats['pending_applications'] > 0)
                <div class="stat-trend down"><i class="fas fa-exclamation"></i> {{ __('messages.action_needed') }}</div>
                @endif
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-card-icon"><i class="fas fa-star"></i></div>
            <div>
                <div class="stat-value">{{ number_format($company->average_rating, 1) }}</div>
                <div class="stat-label">{{ __('messages.avg_rating') }}</div>
                <div class="stat-trend" style="color:var(--text-muted)">{{ $company->reviews_count }} {{ __('messages.reviews') }}</div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 340px;gap:1.5rem">

        <!-- LEFT: Charts + Recent Activity -->
        <div>
            <!-- Applications Chart -->
            <div class="card" style="margin-bottom:1.5rem">
                <div class="card-header">
                    <span class="card-title">📊 {{ __('messages.applications_trend') }}</span>
                    <div style="display:flex;gap:.5rem">
                        <button class="btn btn-ghost btn-sm" onclick="changeChartPeriod('week')">{{ __('messages.week') }}</button>
                        <button class="btn btn-primary btn-sm" id="chartPeriodBtn" onclick="changeChartPeriod('month')">{{ __('messages.month') }}</button>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="applicationsChart" height="200"></canvas>
                </div>
            </div>

            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.recent_applications') }}</span>
                    <a href="{{ route('company.applications.index') }}" class="btn btn-ghost btn-sm">
                        {{ __('messages.view_all') }}
                    </a>
                </div>
                <div style="overflow-x:auto">
                    <table>
                        <thead>
                            <tr>
                                <th>{{ __('messages.applicant') }}</th>
                                <th>{{ __('messages.job') }}</th>
                                <th>{{ __('messages.applied_date') }}</th>
                                <th>{{ __('messages.status') }}</th>
                                <th>{{ __('messages.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentApplications as $app)
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:.625rem">
                                        <div class="avatar avatar-sm">{{ mb_strtoupper(mb_substr($app->user->name,0,2)) }}</div>
                                        <div>
                                            <div style="font-weight:600;font-size:.875rem">{{ $app->user->name }}</div>
                                            <div style="font-size:.75rem;color:var(--text-muted)">{{ $app->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:.875rem;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ $app->job->title }}
                                </td>
                                <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
                                    {{ $app->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <span class="status-badge {{ $app->status }}">{{ __('messages.' . $app->status) }}</span>
                                </td>
                                <td>
                                    <div style="display:flex;gap:.375rem">
                                        <a href="{{ route('company.applications.show', $app) }}"
                                           class="btn btn-ghost btn-sm" data-tooltip="{{ __('messages.review') }}">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <select class="form-control" style="font-size:.75rem;padding:.25rem .5rem;height:auto"
                                                onchange="updateApplicationStatus({{ $app->id }}, this.value, this)">
                                            @foreach(['pending','reviewed','shortlisted','accepted','rejected'] as $status)
                                            <option value="{{ $status }}" {{ $app->status === $status ? 'selected' : '' }}>
                                                {{ __('messages.' . $status) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state" style="padding:2rem">
                                        <p>{{ __('messages.no_applications_yet') }}</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RIGHT: Active Jobs + Quick Actions -->
        <div>
            <!-- Quick Actions -->
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header"><span class="card-title">{{ __('messages.quick_actions') }}</span></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.625rem">
                    <a href="{{ route('company.jobs.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> {{ __('messages.post_new_job') }}
                    </a>
                    <a href="{{ route('company.applications.index', ['status'=>'pending']) }}" class="btn btn-outline">
                        <i class="fas fa-clock"></i> {{ __('messages.pending_applications') }}
                        @if($stats['pending_applications'] > 0)
                        <span class="item-badge" style="margin-left:auto">{{ $stats['pending_applications'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('company.profile') }}" class="btn btn-ghost">
                        <i class="fas fa-edit"></i> {{ __('messages.update_profile') }}
                    </a>
                </div>
            </div>

            <!-- Active Jobs List -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.active_jobs') }}</span>
                    <a href="{{ route('company.jobs.index') }}" style="font-size:.75rem;color:var(--primary)">{{ __('messages.view_all') }}</a>
                </div>
                <div style="padding:0">
                    @forelse($activeJobs as $job)
                    <div style="padding:.875rem 1rem;border-bottom:1px solid var(--border)">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;margin-bottom:.375rem">
                            <a href="{{ route('company.jobs.show', $job) }}"
                               style="font-weight:600;font-size:.875rem;color:var(--text-primary);text-decoration:none;flex:1">
                                {{ $job->title }}
                            </a>
                            <div style="display:flex;align-items:center;gap:.375rem">
                                <button onclick="toggleJobStatus({{ $job->id }}, this)"
                                        class="btn btn-sm {{ $job->is_active ? 'btn-success' : 'btn-ghost' }}"
                                        style="padding:.2rem .5rem;font-size:.7rem"
                                        data-tooltip="{{ $job->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    {{ $job->is_active ? __('messages.active') : __('messages.inactive') }}
                                </button>
                            </div>
                        </div>
                        <div style="display:flex;align-items:center;justify-content:space-between;font-size:.75rem;color:var(--text-muted)">
                            <span><i class="fas fa-users"></i> {{ $job->applications_count }} {{ __('messages.applicants') }}</span>
                            <span>{{ $job->created_at->diffForHumans() }}</span>
                        </div>
                        @if($job->deadline)
                        <div style="margin-top:.375rem">
                            <div class="progress">
                                @php
                                    $total = $job->created_at->diffInDays($job->deadline);
                                    $remaining = max(0, now()->diffInDays($job->deadline, false));
                                    $progress = $total > 0 ? min(100, (($total - $remaining) / $total) * 100) : 100;
                                @endphp
                                <div class="progress-bar" style="width:{{ $progress }}%;background:{{ $remaining <= 3 ? 'var(--danger)' : ($remaining <= 7 ? 'var(--warning)' : '') }}"></div>
                            </div>
                            <div style="font-size:.65rem;color:var(--text-muted);margin-top:.2rem">
                                {{ $remaining > 0 ? $remaining . ' ' . __('messages.days_left') : __('messages.expired') }}
                            </div>
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="empty-state" style="padding:2rem">
                        <p>{{ __('messages.no_active_jobs') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
// Applications Chart
const ctx = document.getElementById('applicationsChart')?.getContext('2d');
if (ctx) {
    const chartData = @json($chartData ?? []);
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#94a3b8' : '#64748b';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels ?? [],
            datasets: [{
                label: '{{ __("messages.applications") }}',
                data: chartData.data ?? [],
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.08)',
                borderWidth: 2.5,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 } } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, stepSize: 1 }, beginAtZero: true },
            }
        }
    });
}

// Update application status via AJAX
async function updateApplicationStatus(id, status, select) {
    const prev = select.dataset.prev || select.value;
    try {
        const res = await fetch(`/company/applications/${id}/status`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status })
        });
        const data = await res.json();
        if (data.success) {
            toastr.success(data.message);
            select.dataset.prev = status;
        } else {
            toastr.error('Failed to update status');
            select.value = prev;
        }
    } catch (e) {
        toastr.error('Network error');
        select.value = prev;
    }
}

// Toggle job active status
async function toggleJobStatus(id, btn) {
    const res = await fetch(`/company/jobs/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.is_active) {
        btn.classList.replace('btn-ghost', 'btn-success');
        btn.textContent = '{{ __("messages.active") }}';
    } else {
        btn.classList.replace('btn-success', 'btn-ghost');
        btn.textContent = '{{ __("messages.inactive") }}';
    }
}
</script>
@endpush
@endsection