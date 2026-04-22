@extends('layouts.app')
@section('title', __('messages.my_applications'))

@section('content')
<div class="page-container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ __('messages.my_applications') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.track_your_applications') }}</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary">
            <i class="fas fa-search"></i> {{ __('messages.find_more_jobs') }}
        </a>
    </div>

    <!-- Status Stats -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['status'=>'all',        'label'=>__('messages.total'),      'icon'=>'fa-layer-group', 'color'=>'primary', 'count'=>$stats['total']],
            ['status'=>'pending',    'label'=>__('messages.pending'),    'icon'=>'fa-clock',        'color'=>'warning', 'count'=>$stats['pending']],
            ['status'=>'shortlisted','label'=>__('messages.shortlisted'),'icon'=>'fa-star',         'color'=>'info',    'count'=>$stats['shortlisted']],
            ['status'=>'accepted',   'label'=>__('messages.accepted'),   'icon'=>'fa-check-circle', 'color'=>'success', 'count'=>$stats['accepted']],
        ] as $s)
        <div class="stat-card {{ $s['color'] }}" style="cursor:pointer" onclick="filterByStatus('{{ $s['status'] }}')">
            <div class="stat-card-icon"><i class="fas {{ $s['icon'] }}"></i></div>
            <div>
                <div class="stat-value">{{ $s['count'] }}</div>
                <div class="stat-label">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:.875rem 1.25rem">
            <form method="GET" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                    @foreach(['all','pending','reviewed','shortlisted','accepted','rejected'] as $status)
                    <a href="{{ route('user.applications', array_merge(request()->except('status'), ['status' => $status === 'all' ? null : $status])) }}"
                       class="btn btn-sm {{ (request('status') ?? 'all') === $status ? 'btn-primary' : 'btn-ghost' }}"
                       id="filter-{{ $status }}">
                        {{ __('messages.' . $status) }}
                    </a>
                    @endforeach
                </div>
                <div style="margin-left:auto">
                    <select name="sort" class="form-control" style="font-size:.8rem;width:auto"
                            onchange="this.form.submit()">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                        <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Applications List -->
    @forelse($applications as $application)
    <div class="card animate-slide-up" style="margin-bottom:1rem;overflow:visible">
        <div class="card-body">
            <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
                <!-- Company Logo -->
                @if($application->job->company->logo)
                    <img src="{{ Storage::url($application->job->company->logo) }}" alt="{{ $application->job->company->name }}"
                         style="width:56px;height:56px;border-radius:var(--radius);object-fit:cover;border:1px solid var(--border);flex-shrink:0">
                @else
                    <div class="avatar avatar-lg" style="border-radius:var(--radius);flex-shrink:0">
                        {{ mb_strtoupper(mb_substr($application->job->company->name,0,2)) }}
                    </div>
                @endif

                <!-- Info -->
                <div style="flex:1;min-width:200px">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                        <div>
                            <h3 style="font-size:1rem;font-weight:700;margin-bottom:.2rem">
                                <a href="{{ route('jobs.show', $application->job) }}"
                                   style="text-decoration:none;color:var(--text-primary)">
                                    {{ $application->job->title }}
                                </a>
                            </h3>
                            <div style="font-size:.875rem;color:var(--primary);font-weight:600">
                                {{ $application->job->company->name }}
                            </div>
                        </div>
                        <span class="status-badge {{ $application->status }}">{{ __('messages.' . $application->status) }}</span>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:.75rem;font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem">
                        <span><i class="fas fa-map-marker-alt"></i> {{ $application->job->location }}</span>
                        <span><i class="fas fa-briefcase"></i> {{ __('messages.' . str_replace('-','_', $application->job->type)) }}</span>
                        <span><i class="fas fa-calendar"></i> {{ __('messages.applied') }}: {{ $application->created_at->format('d M Y') }}</span>
                        @if($application->responded_at)
                        <span><i class="fas fa-reply"></i> {{ __('messages.responded') }}: {{ $application->responded_at->format('d M Y') }}</span>
                        @endif
                    </div>

                    <!-- Progress Timeline -->
                    <div style="display:flex;align-items:center;gap:0;margin-bottom:.875rem;overflow-x:auto">
                        @foreach(['pending','reviewed','shortlisted','accepted'] as $i => $step)
                        @php
                            $stepOrder = ['pending'=>0,'reviewed'=>1,'shortlisted'=>2,'accepted'=>3,'rejected'=>4];
                            $currentOrder = $stepOrder[$application->status] ?? 0;
                            $thisOrder = $stepOrder[$step] ?? 0;
                            $isActive = $thisOrder <= $currentOrder && $application->status !== 'rejected';
                            $isCurrent = $step === $application->status;
                        @endphp
                        <div style="display:flex;align-items:center;flex:1;min-width:0">
                            <div style="flex-shrink:0;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:700;transition:var(--transition);
                                        {{ $isCurrent ? 'background:var(--primary);color:white;box-shadow:0 0 0 4px rgba(37,99,235,.2)' : ($isActive ? 'background:var(--success);color:white' : 'background:var(--border);color:var(--text-muted)') }}">
                                @if($isActive && !$isCurrent)
                                    <i class="fas fa-check"></i>
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </div>
                            <div style="font-size:.6rem;color:{{ $isActive ? 'var(--text-primary)' : 'var(--text-muted)' }};white-space:nowrap;margin:0 .25rem;display:none">
                                {{ __('messages.' . $step) }}
                            </div>
                            @if(!$loop->last)
                            <div style="flex:1;height:2px;background:{{ $isActive ? 'var(--success)' : 'var(--border)' }};min-width:8px"></div>
                            @endif
                        </div>
                        @endforeach
                        @if($application->status === 'rejected')
                        <div style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:var(--danger);color:white;display:flex;align-items:center;justify-content:center;font-size:.7rem">
                            <i class="fas fa-times"></i>
                        </div>
                        @endif
                    </div>

                    <!-- Company Notes -->
                    @if($application->admin_notes)
                    <div style="padding:.75rem;background:var(--bg-hover);border-radius:var(--radius);border-left:3px solid var(--primary);font-size:.8rem;color:var(--text-secondary);margin-bottom:.75rem">
                        <strong>{{ __('messages.company_note') }}:</strong> {{ $application->admin_notes }}
                    </div>
                    @endif

                    <!-- Actions -->
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <a href="{{ route('jobs.show', $application->job) }}" class="btn btn-ghost btn-sm">
                            <i class="fas fa-eye"></i> {{ __('messages.view_job') }}
                        </a>
                        <a href="{{ route('user.application.cv', $application) }}" class="btn btn-ghost btn-sm">
                            <i class="fas fa-file-alt"></i> {{ __('messages.view_cv') }}
                        </a>
                        @if($application->status === 'accepted')
                        <div class="btn btn-success btn-sm" style="cursor:default">
                            🎉 {{ __('messages.congratulations') }}!
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-file-alt"></i></div>
        <h3>{{ __('messages.no_applications_yet') }}</h3>
        <p>{{ __('messages.start_applying') }}</p>
        <a href="{{ route('jobs.index') }}" class="btn btn-primary" style="margin-top:1rem">
            <i class="fas fa-search"></i> {{ __('messages.browse_jobs') }}
        </a>
    </div>
    @endforelse

    {{ $applications->links() }}
</div>
@endsection