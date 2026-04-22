@extends('layouts.app')
@section('title', __('messages.admin_dashboard'))

@section('content')
<div class="page-container">

    <!-- Header -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">🛡️ {{ __('messages.admin_panel') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.system_overview') }}</p>
        </div>
        <div style="display:flex;gap:.75rem">
            <button onclick="sendBulkNotification()" class="btn btn-outline">
                <i class="fas fa-bell"></i> {{ __('messages.send_notification') }}
            </button>
            <a href="{{ route('admin.settings') }}" class="btn btn-ghost">
                <i class="fas fa-cog"></i>
            </a>
        </div>
    </div>

    <!-- System Stats -->
    <div class="grid grid-4" style="margin-bottom:2rem">
        @foreach([
            ['label'=>__('messages.total_users'),       'value'=>$stats['users'],        'icon'=>'fa-users',          'color'=>'primary', 'trend'=>'+'.($stats['new_users_today']).' '.__('messages.today')],
            ['label'=>__('messages.total_companies'),   'value'=>$stats['companies'],    'icon'=>'fa-building',       'color'=>'success', 'trend'=>$stats['verified_companies'].' '.__('messages.verified')],
            ['label'=>__('messages.active_jobs'),       'value'=>$stats['active_jobs'],  'icon'=>'fa-briefcase',      'color'=>'warning', 'trend'=>$stats['jobs_today'].' '.__('messages.posted_today')],
            ['label'=>__('messages.total_applications'),'value'=>$stats['applications'], 'icon'=>'fa-file-alt',       'color'=>'purple',  'trend'=>$stats['apps_today'].' '.__('messages.today')],
        ] as $s)
        <div class="stat-card {{ $s['color'] }}">
            <div class="stat-card-icon"><i class="fas {{ $s['icon'] }}"></i></div>
            <div>
                <div class="stat-value">{{ number_format($s['value']) }}</div>
                <div class="stat-label">{{ $s['label'] }}</div>
                <div class="stat-trend up"><i class="fas fa-arrow-up"></i> {{ $s['trend'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-2" style="margin-bottom:2rem">
        <div class="card">
            <div class="card-header">
                <span class="card-title">{{ __('messages.registrations_30_days') }}</span>
            </div>
            <div class="card-body">
                <canvas id="registrationsChart" height="220"></canvas>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <span class="card-title">{{ __('messages.jobs_by_type') }}</span>
            </div>
            <div class="card-body" style="display:flex;align-items:center;justify-content:center">
                <canvas id="jobTypesChart" height="220" style="max-height:220px"></canvas>
            </div>
        </div>
    </div>

    <!-- Management Tabs -->
    <div style="display:flex;gap:.25rem;border-bottom:1px solid var(--border);margin-bottom:1.5rem;overflow-x:auto">
        @foreach([
            ['id'=>'users','label'=>__('messages.users'),'icon'=>'fa-users'],
            ['id'=>'companies','label'=>__('messages.companies'),'icon'=>'fa-building'],
            ['id'=>'jobs','label'=>__('messages.jobs'),'icon'=>'fa-briefcase'],
        ] as $t)
        <button onclick="adminTab('{{ $t['id'] }}')" id="atab-{{ $t['id'] }}"
                style="padding:.75rem 1.25rem;border:none;background:none;cursor:pointer;font-size:.875rem;font-weight:600;color:var(--text-muted);border-bottom:2px solid transparent;white-space:nowrap;transition:var(--transition)">
            <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
        </button>
        @endforeach
    </div>

    <!-- Users Panel -->
    <div id="apanel-users" class="admin-panel">
        <div style="display:flex;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.75rem">
            <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex;gap:.5rem">
                <input type="text" name="search" class="form-control" style="width:240px"
                       placeholder="{{ __('messages.search_users') }}" value="{{ request('search') }}">
                <select name="role" class="form-control" style="width:auto">
                    <option value="">{{ __('messages.all_roles') }}</option>
                    @foreach(['user','company','admin'] as $role)
                    <option value="{{ $role }}" {{ request('role') === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.user') }}</th>
                        <th>{{ __('messages.role') }}</th>
                        <th>{{ __('messages.joined') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestUsers as $u)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem">
                                <div class="avatar avatar-sm">{{ mb_strtoupper(mb_substr($u->name,0,2)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:.875rem">{{ $u->name }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $u->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="status-badge {{ $u->role === 'admin' ? 'rejected' : ($u->role === 'company' ? 'reviewed' : 'active') }}">{{ $u->role }}</span></td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">{{ $u->created_at->format('d M Y') }}</td>
                        <td><span class="status-badge {{ $u->is_active ? 'active' : 'inactive' }}">{{ $u->is_active ? __('messages.active') : __('messages.inactive') }}</span></td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('admin.users.show', $u) }}" class="btn btn-ghost btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="toggleUserStatus({{ $u->id }}, this)"
                                        class="btn btn-ghost btn-sm {{ $u->is_active ? '' : 'btn-success' }}">
                                    <i class="fas {{ $u->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                                </button>
                                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                                            data-confirm-delete="{{ __('messages.delete_user_confirm') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;text-align:center">
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">
                {{ __('messages.view_all_users') }}
            </a>
        </div>
    </div>

    <!-- Companies Panel -->
    <div id="apanel-companies" class="admin-panel" style="display:none">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.industry') }}</th>
                        <th>{{ __('messages.jobs') }}</th>
                        <th>{{ __('messages.verified') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestCompanies as $c)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem">
                                <div class="avatar avatar-sm" style="border-radius:var(--radius-sm)">{{ mb_strtoupper(mb_substr($c->name,0,2)) }}</div>
                                <div>
                                    <div style="font-weight:600;font-size:.875rem">{{ $c->name }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $c->user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.875rem">{{ $c->industry }}</td>
                        <td style="font-size:.875rem">{{ $c->jobs_count }}</td>
                        <td>
                            @if($c->is_verified)
                                <span class="status-badge accepted"><i class="fas fa-check"></i> {{ __('messages.verified') }}</span>
                            @else
                                <button onclick="verifyCompany({{ $c->id }}, this)" class="btn btn-outline btn-sm" style="font-size:.75rem">
                                    {{ __('messages.verify') }}
                                </button>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('admin.companies.show', $c) }}" class="btn btn-ghost btn-sm"><i class="fas fa-eye"></i></a>
                                <a href="{{ route('companies.show', $c) }}" class="btn btn-ghost btn-sm" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;text-align:center">
            <a href="{{ route('admin.companies.index') }}" class="btn btn-outline btn-sm">{{ __('messages.view_all_companies') }}</a>
        </div>
    </div>

    <!-- Jobs Panel -->
    <div id="apanel-jobs" class="admin-panel" style="display:none">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.job') }}</th>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.applicants') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.featured') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($latestJobs as $job)
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:.875rem;max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $job->title }}</div>
                            <div style="font-size:.75rem;color:var(--text-muted)">{{ $job->created_at->format('d M Y') }}</div>
                        </td>
                        <td style="font-size:.875rem">{{ $job->company->name }}</td>
                        <td style="font-size:.875rem;text-align:center">{{ $job->applications_count }}</td>
                        <td><span class="status-badge {{ $job->is_active ? 'active' : 'inactive' }}">{{ $job->is_active ? __('messages.active') : __('messages.inactive') }}</span></td>
                        <td>
                            <button onclick="toggleFeatured({{ $job->id }}, this)"
                                    class="btn btn-sm {{ $job->is_featured ? 'btn-warning' : 'btn-ghost' }}"
                                    style="font-size:.7rem">
                                <i class="fas fa-star"></i>
                            </button>
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('jobs.show', $job) }}" class="btn btn-ghost btn-sm" target="_blank"><i class="fas fa-eye"></i></a>
                                <form action="{{ route('admin.jobs.destroy', $job) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                                            data-confirm-delete>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:1rem;text-align:center">
            <a href="{{ route('admin.jobs.index') }}" class="btn btn-outline btn-sm">{{ __('messages.view_all_jobs') }}</a>
        </div>
    </div>
</div>

<!-- Bulk Notification Modal -->
<div id="notifModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,.5);backdrop-filter:blur(4px)">
    <div class="card animate-scale-in" style="width:100%;max-width:480px;margin:1rem">
        <div class="card-header">
            <span class="card-title"><i class="fas fa-bell"></i> {{ __('messages.send_bulk_notification') }}</span>
            <button onclick="document.getElementById('notifModal').style.display='none'" class="nav-icon-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.notifications.send') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">{{ __('messages.target') }}</label>
                    <select name="target" class="form-control">
                        <option value="all">{{ __('messages.all_users') }}</option>
                        <option value="users">{{ __('messages.job_seekers_only') }}</option>
                        <option value="companies">{{ __('messages.companies_only') }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.notification_title') }} <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.notification_body') }} <span class="required">*</span></label>
                    <textarea name="body" class="form-control" rows="3" required maxlength="500"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('messages.action_url') }}</label>
                    <input type="url" name="action_url" class="form-control" placeholder="https://...">
                </div>
                <div style="display:flex;gap:.75rem">
                    <button type="button" onclick="document.getElementById('notifModal').style.display='none'" class="btn btn-ghost" style="flex:1">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" style="flex:2">
                        <i class="fas fa-paper-plane"></i> {{ __('messages.send') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
function adminTab(id) {
    document.querySelectorAll('.admin-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('[id^="atab-"]').forEach(t => { t.style.color = 'var(--text-muted)'; t.style.borderBottomColor = 'transparent'; });
    document.getElementById('apanel-' + id).style.display = 'block';
    const btn = document.getElementById('atab-' + id);
    btn.style.color = 'var(--primary)';
    btn.style.borderBottomColor = 'var(--primary)';
}
adminTab('users');

function sendBulkNotification() {
    document.getElementById('notifModal').style.display = 'flex';
}

// Charts
const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
const textColor = isDark ? '#94a3b8' : '#64748b';

const regCtx = document.getElementById('registrationsChart')?.getContext('2d');
if (regCtx) {
    new Chart(regCtx, {
        type: 'bar',
        data: {
            labels: @json($regChart['labels'] ?? []),
            datasets: [{
                label: '{{ __("messages.registrations") }}',
                data: @json($regChart['data'] ?? []),
                backgroundColor: 'rgba(37,99,235,0.7)',
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
                y: { grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1 }, beginAtZero: true },
            }
        }
    });
}

const typeCtx = document.getElementById('jobTypesChart')?.getContext('2d');
if (typeCtx) {
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: @json($jobTypesChart['labels'] ?? []),
            datasets: [{
                data: @json($jobTypesChart['data'] ?? []),
                backgroundColor: ['#2563eb','#7c3aed','#06b6d4','#10b981','#f59e0b'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { color: textColor, font: { size: 11 }, padding: 12 } }
            }
        }
    });
}

// Admin actions
async function toggleUserStatus(id, btn) {
    const res = await fetch(`/admin/users/${id}/toggle`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    toastr.success(data.message);
}

async function verifyCompany(id, btn) {
    const res = await fetch(`/admin/companies/${id}/verify`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.success) {
        btn.outerHTML = `<span class="status-badge accepted"><i class="fas fa-check"></i> {{ __("messages.verified") }}</span>`;
        toastr.success('{{ __("messages.company_verified") }}');
    }
}

async function toggleFeatured(id, btn) {
    const res = await fetch(`/admin/jobs/${id}/featured`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    btn.classList.toggle('btn-warning', data.is_featured);
    btn.classList.toggle('btn-ghost', !data.is_featured);
}
</script>
@endpush
@endsection