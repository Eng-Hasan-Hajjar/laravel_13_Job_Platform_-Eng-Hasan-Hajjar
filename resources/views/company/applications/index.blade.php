@extends('layouts.app')
@section('title', __('messages.all_applications'))

@section('content')
<div class="page-container">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ __('messages.all_applications') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ $applications->total() }} {{ __('messages.total_applications') }}</p>
        </div>
        <!-- Export -->
        <a href="{{ route('company.applications.index', array_merge(request()->all(), ['export' => 'csv'])) }}"
           class="btn btn-ghost btn-sm">
            <i class="fas fa-download"></i> {{ __('messages.export_csv') }}
        </a>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['status'=>'all',        'icon'=>'fa-layer-group','color'=>'primary'],
            ['status'=>'pending',    'icon'=>'fa-clock',      'color'=>'warning'],
            ['status'=>'shortlisted','icon'=>'fa-star',       'color'=>'info'],
            ['status'=>'accepted',   'icon'=>'fa-check',      'color'=>'success'],
        ] as $s)
        @php
            $count = $s['status'] === 'all'
                ? $applications->total()
                : \App\Models\JobApplication::whereHas('job', fn($q) => $q->where('company_id', auth()->user()->company->id))
                    ->where('status', $s['status'])->count();
        @endphp
        <a href="{{ route('company.applications.index', ['status' => $s['status'] === 'all' ? null : $s['status']]) }}"
           class="stat-card {{ $s['color'] }}" style="text-decoration:none;cursor:pointer">
            <div class="stat-card-icon"><i class="fas {{ $s['icon'] }}"></i></div>
            <div>
                <div class="stat-value">{{ $count }}</div>
                <div class="stat-label">{{ __('messages.' . $s['status']) }}</div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:.875rem 1.25rem">
            <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
                <!-- Status Filter -->
                <div style="display:flex;gap:.375rem;flex-wrap:wrap">
                    @foreach(['all','pending','reviewed','shortlisted','accepted','rejected'] as $st)
                    <a href="{{ route('company.applications.index', array_merge(request()->except(['status','page']), ['status' => $st === 'all' ? null : $st])) }}"
                       class="btn btn-sm {{ (request('status') ?? 'all') === $st ? 'btn-primary' : 'btn-ghost' }}">
                        {{ __('messages.' . $st) }}
                    </a>
                    @endforeach
                </div>

                <!-- Job Filter -->
                <select name="job_id" class="form-control" style="width:auto;font-size:.8rem" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_jobs') }}</option>
                    @foreach($jobs as $j)
                    <option value="{{ $j->id }}" {{ request('job_id') == $j->id ? 'selected' : '' }}>
                        {{ Str::limit($j->title, 40) }}
                    </option>
                    @endforeach
                </select>

                <!-- Sort -->
                <select name="sort" class="form-control" style="width:auto;font-size:.8rem;margin-left:auto" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Applications List -->
    @forelse($applications as $app)
    <div class="card animate-slide-up" style="margin-bottom:1rem">
        <div class="card-body">
            <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">

                <!-- Avatar -->
                <div class="avatar avatar-lg" style="flex-shrink:0">
                    {{ mb_strtoupper(mb_substr($app->user->name, 0, 2)) }}
                </div>

                <!-- Info -->
                <div style="flex:1;min-width:240px">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <div>
                            <div style="font-weight:700;font-size:1rem">{{ $app->user->name }}</div>
                            <div style="font-size:.8rem;color:var(--text-muted)">{{ $app->user->email }}</div>
                        </div>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <span class="status-badge {{ $app->status }}">{{ __('messages.' . $app->status) }}</span>
                        </div>
                    </div>

                    <!-- Job & Meta -->
                    <div style="display:flex;flex-wrap:wrap;gap:.625rem;font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem">
                        <span style="background:var(--primary-light);color:var(--primary);padding:.2rem .6rem;border-radius:var(--radius-full);font-weight:600">
                            <i class="fas fa-briefcase"></i> {{ $app->job->title }}
                        </span>
                        <span><i class="fas fa-calendar"></i> {{ $app->created_at->format('d M Y') }}</span>
                        @if($app->expected_salary)
                        <span><i class="fas fa-dollar-sign"></i> {{ number_format($app->expected_salary) }} {{ __('messages.expected') }}</span>
                        @endif
                        <span><i class="fas fa-hourglass-half"></i> {{ __('messages.' . $app->availability) }}</span>
                    </div>

                    <!-- User Skills from CV Analysis -->
                    @if($app->user->cv_analyzed && !empty($app->user->cv_analyzed['technical_skills']))
                    <div style="margin-bottom:.75rem">
                        <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.375rem;font-weight:600">
                            <i class="fas fa-robot" style="color:var(--primary)"></i> {{ __('messages.detected_skills') }}:
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.25rem">
                            @foreach(array_slice($app->user->cv_analyzed['technical_skills'], 0, 8) as $skill)
                            <span style="padding:.15rem .5rem;background:var(--bg-hover);color:var(--text-secondary);border-radius:var(--radius-full);font-size:.7rem">
                                {{ $skill }}
                            </span>
                            @endforeach
                            @php $cvScore = $app->user->cv_analyzed['score'] ?? 0; @endphp
                            <span style="padding:.15rem .6rem;background:{{ $cvScore >= 70 ? 'rgba(16,185,129,.1)' : ($cvScore >= 40 ? 'rgba(245,158,11,.1)' : 'rgba(239,68,68,.1)') }};color:{{ $cvScore >= 70 ? 'var(--success)' : ($cvScore >= 40 ? 'var(--warning)' : 'var(--danger)') }};border-radius:var(--radius-full);font-size:.7rem;font-weight:700;margin-left:.25rem">
                                CV: {{ $cvScore }}/100
                            </span>
                        </div>
                    </div>
                    @endif

                    <!-- Cover Letter Preview -->
                    <div style="font-size:.8rem;color:var(--text-secondary);background:var(--bg-hover);padding:.625rem .875rem;border-radius:var(--radius);border-left:3px solid var(--primary);line-height:1.6;margin-bottom:.75rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden">
                        {{ $app->cover_letter }}
                    </div>

                    <!-- Actions Row -->
                    <div style="display:flex;align-items:center;gap:.625rem;flex-wrap:wrap">
                        <a href="{{ route('company.applications.show', $app) }}" class="btn btn-outline btn-sm">
                            <i class="fas fa-eye"></i> {{ __('messages.full_review') }}
                        </a>
                        <a href="{{ route('company.applications.cv', $app) }}" class="btn btn-ghost btn-sm">
                            <i class="fas fa-file-pdf" style="color:var(--danger)"></i> {{ __('messages.download_cv') }}
                        </a>

                        <!-- Quick Status Update -->
                        <div style="display:flex;gap:.375rem;margin-left:auto">
                            @if($app->status === 'pending' || $app->status === 'reviewed')
                            <button onclick="quickStatus({{ $app->id }}, 'shortlisted', this)"
                                    class="btn btn-ghost btn-sm" style="color:var(--info)"
                                    data-tooltip="{{ __('messages.shortlist') }}">
                                <i class="fas fa-star"></i>
                            </button>
                            @endif
                            @if($app->status !== 'accepted')
                            <button onclick="quickStatus({{ $app->id }}, 'accepted', this)"
                                    class="btn btn-ghost btn-sm" style="color:var(--success)"
                                    data-tooltip="{{ __('messages.accept') }}">
                                <i class="fas fa-check-circle"></i>
                            </button>
                            @endif
                            @if($app->status !== 'rejected')
                            <button onclick="quickStatus({{ $app->id }}, 'rejected', this)"
                                    class="btn btn-ghost btn-sm" style="color:var(--danger)"
                                    data-tooltip="{{ __('messages.reject') }}">
                                <i class="fas fa-times-circle"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-inbox"></i></div>
        <h3>{{ __('messages.no_applications_found') }}</h3>
        <p>{{ __('messages.try_different_filters') }}</p>
    </div>
    @endforelse

    <!-- Pagination -->
    @if($applications->hasPages())
    <div class="pagination" style="margin-top:1.5rem">
        @if($applications->onFirstPage())
            <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
        @else
            <a href="{{ $applications->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
        @endif
        @foreach($applications->getUrlRange(max(1,$applications->currentPage()-2), min($applications->lastPage(),$applications->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="page-link {{ $page === $applications->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        @if($applications->hasMorePages())
            <a href="{{ $applications->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
        @else
            <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
        @endif
    </div>
    @endif
</div>

@push('scripts')
<script>
async function quickStatus(id, status, btn) {
    const labels = {
        shortlisted: '{{ __("messages.shortlisted") }}',
        accepted: '{{ __("messages.accepted") }}',
        rejected: '{{ __("messages.rejected") }}'
    };

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

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
            toastr.success(`${labels[status]}: ✓`);
            // Update badge in card
            const card = btn.closest('.card');
            const badge = card.querySelector('.status-badge');
            if (badge) {
                badge.className = `status-badge ${status}`;
                badge.textContent = labels[status];
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check"></i>';
        }
    } catch (e) {
        toastr.error('{{ __("messages.error_occurred") }}');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-times"></i>';
    }
}
</script>
@endpush
@endsection