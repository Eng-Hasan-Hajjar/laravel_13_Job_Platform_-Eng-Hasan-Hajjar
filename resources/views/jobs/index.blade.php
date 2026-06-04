@extends('layouts.app')
@section('title', __('messages.browse_jobs'))

@section('content')
<div class="page-container">

    <div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start">

        {{-- ════ FILTERS SIDEBAR ════ --}}
        <div style="position:sticky;top:calc(var(--navbar-height) + 1rem)">
            <div class="card">
                <div class="card-header">
                    <span class="card-title">
                        <i class="fas fa-sliders-h" style="color:var(--primary)"></i>
                        {{ __('messages.filters') }}
                    </span>
                    @if(request()->hasAny(['type','location','category','salary_min','salary_max','experience','posted']))
                    <a href="{{ route('jobs.index') }}" style="font-size:.75rem;color:var(--danger);text-decoration:none">
                        <i class="fas fa-times"></i> {{ __('messages.reset') }}
                    </a>
                    @endif
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('jobs.index') }}" id="filterForm">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif

                        {{-- Job Type --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.job_type') }}</label>
                            @foreach(['full-time','part-time','freelance','remote','internship'] as $type)
                            <label style="display:flex;align-items:center;gap:.5rem;padding:.3rem 0;cursor:pointer;font-size:.875rem">
                                <input type="checkbox" name="type[]" value="{{ $type }}"
                                       {{ in_array($type, (array) request('type', [])) ? 'checked' : '' }}
                                       style="accent-color:var(--primary)"
                                       onchange="document.getElementById('filterForm').submit()">
                                {{ __('messages.' . str_replace('-','_', $type)) }}
                            </label>
                            @endforeach
                        </div>

                        {{-- Location --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.location') }}</label>
                            <input type="text" name="location" class="form-control" style="font-size:.85rem"
                                   placeholder="{{ __('messages.city_or_country') }}"
                                   value="{{ request('location') }}"
                                   onchange="this.form.submit()">
                        </div>

                        {{-- Category --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.category') }}</label>
                            <select name="category" class="form-control" style="font-size:.85rem"
                                    onchange="this.form.submit()">
                                <option value="">{{ __('messages.all') }}</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                    {{ $cat->icon }} {{ $cat->name }}
                                    ({{ $cat->jobs_count }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Salary Range --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.salary_range') }}</label>
                            <div style="display:flex;gap:.5rem;align-items:center">
                                <input type="number" name="salary_min" class="form-control"
                                       placeholder="{{ __('messages.min') }}" min="0" step="500"
                                       value="{{ request('salary_min') }}" style="font-size:.8rem">
                                <span style="color:var(--text-muted);flex-shrink:0">—</span>
                                <input type="number" name="salary_max" class="form-control"
                                       placeholder="{{ __('messages.max') }}" min="0" step="500"
                                       value="{{ request('salary_max') }}" style="font-size:.8rem">
                            </div>
                        </div>

                        {{-- Experience --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.experience_level') }}</label>
                            <select name="experience" class="form-control" style="font-size:.85rem"
                                    onchange="this.form.submit()">
                                <option value="">{{ __('messages.all') }}</option>
                                @foreach(['entry','junior','mid','senior','lead'] as $level)
                                <option value="{{ $level }}" {{ request('experience') === $level ? 'selected' : '' }}>
                                    {{ __('messages.exp_' . $level) }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Posted Date --}}
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.posted_date') }}</label>
                            <select name="posted" class="form-control" style="font-size:.85rem"
                                    onchange="this.form.submit()">
                                <option value="">{{ __('messages.all') }}</option>
                                <option value="1"  {{ request('posted') === '1'  ? 'selected' : '' }}>{{ __('messages.posted_today') }}</option>
                                <option value="7"  {{ request('posted') === '7'  ? 'selected' : '' }}>{{ __('messages.posted_week') }}</option>
                                <option value="30" {{ request('posted') === '30' ? 'selected' : '' }}>{{ __('messages.posted_month') }}</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%">
                            <i class="fas fa-search"></i> {{ __('messages.apply_filters') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ════ JOBS LIST ════ --}}
        <div>
            {{-- Search + Sort Bar --}}
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-body" style="padding:.875rem 1.25rem">
                    <div style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                        <form method="GET" action="{{ route('jobs.index') }}" style="flex:1;min-width:200px;display:flex;gap:.5rem">
                            @foreach(request()->except(['q','page']) as $key => $val)
                                @if(is_array($val))
                                    @foreach($val as $v)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                                @endif
                            @endforeach
                            <div style="position:relative;flex:1">
                                <i class="fas fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem"></i>
                                <input type="text" name="q" class="form-control" style="padding-left:2.25rem"
                                       placeholder="{{ __('messages.search_placeholder') }}"
                                       value="{{ request('q') }}">
                            </div>
                            <button type="submit" class="btn btn-primary">{{ __('messages.search_btn') }}</button>
                        </form>

                        <select name="sort" class="form-control" style="width:auto;font-size:.85rem"
                                onchange="window.location='{{ route('jobs.index') }}?{{ http_build_query(request()->except(['sort','page'])) }}&sort='+this.value">
                            <option value="latest"     {{ request('sort','latest') === 'latest'     ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                            <option value="salary_high"{{ request('sort') === 'salary_high' ? 'selected' : '' }}>{{ __('messages.sort_salary_high') }}</option>
                            <option value="salary_low" {{ request('sort') === 'salary_low'  ? 'selected' : '' }}>{{ __('messages.sort_salary_low') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Results Count --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
                <div style="font-size:.875rem;color:var(--text-secondary)">
                    <strong style="color:var(--text-primary)">{{ $jobs->total() }}</strong>
                    {{ __('messages.jobs_found') }}
                    @if(request('q'))
                        {{ __('messages.for') }} "<strong>{{ request('q') }}</strong>"
                    @endif
                </div>
            </div>

            {{-- Jobs Grid --}}
            @forelse($jobs as $job)
            @php
                // Ensure skills is always an array
                $jobSkills = is_array($job->skills) ? $job->skills : (
                    is_string($job->skills) && !empty($job->skills)
                        ? (json_decode($job->skills, true) ?? array_map('trim', explode(',', $job->skills)))
                        : []
                );
            @endphp
            <div class="card animate-slide-up" style="margin-bottom:1rem">
                <div class="card-body">
                    <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">

                        {{-- Logo --}}
                        @if($job->company->logo)
                            <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}"
                                 style="width:56px;height:56px;border-radius:var(--radius);object-fit:cover;border:1px solid var(--border);flex-shrink:0">
                        @else
                            <div class="avatar avatar-lg" style="border-radius:var(--radius);flex-shrink:0">
                                {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
                            </div>
                        @endif

                        {{-- Info --}}
                        <div style="flex:1;min-width:200px">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;flex-wrap:wrap;margin-bottom:.5rem">
                                <div>
                                    <h3 style="font-size:1rem;font-weight:700;margin-bottom:.2rem">
                                        <a href="{{ route('jobs.show', $job) }}"
                                           style="text-decoration:none;color:var(--text-primary)">
                                            {{ $job->title }}
                                        </a>
                                    </h3>
                                    <div style="font-size:.875rem;color:var(--primary);font-weight:600">
                                        <a href="{{ route('companies.show', $job->company) }}"
                                           style="color:var(--primary);text-decoration:none">
                                            {{ $job->company->name }}
                                        </a>
                                    </div>
                                </div>

                                <div style="display:flex;gap:.375rem;align-items:center">
                                    @if($job->is_featured)
                                    <span style="font-size:.7rem;background:rgba(245,158,11,.1);color:var(--warning);padding:.2rem .6rem;border-radius:var(--radius-full);font-weight:700;border:1px solid rgba(245,158,11,.3)">
                                        ⭐ {{ __('messages.featured') }}
                                    </span>
                                    @endif

                                    {{-- Save Button --}}
                                    @auth
                                    @if(auth()->user()->isUser())
                                    <button onclick="toggleSave({{ $job->id }}, this)"
                                            class="btn btn-ghost btn-sm {{ auth()->user()->hasSaved($job) ? 'saved' : '' }}"
                                            style="color:{{ auth()->user()->hasSaved($job) ? 'var(--warning)' : 'var(--text-muted)' }}"
                                            data-tooltip="{{ __('messages.save_job') }}">
                                        <i class="fas fa-bookmark"></i>
                                    </button>
                                    @endif
                                    @endauth
                                </div>
                            </div>

                            {{-- Tags --}}
                            <div style="display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:.75rem">
                                @if($job->location)
                                <span class="job-tag location"><i class="fas fa-map-marker-alt"></i> {{ $job->location }}</span>
                                @endif
                                <span class="job-tag type">
                                    <i class="fas fa-clock"></i>
                                    {{ __('messages.' . str_replace('-','_', $job->type)) }}
                                </span>
                                @if($job->is_remote)
                                <span class="job-tag remote"><i class="fas fa-wifi"></i> {{ __('messages.remote') }}</span>
                                @endif
                                @if($job->salary_min || $job->salary_max)
                                <span class="job-tag salary">
                                    <i class="fas fa-dollar-sign"></i>
                                    @if($job->salary_min && $job->salary_max)
                                        {{ number_format($job->salary_min) }} – {{ number_format($job->salary_max) }}
                                    @elseif($job->salary_min)
                                        {{ number_format($job->salary_min) }}+
                                    @else
                                        {{ __('messages.up_to') }} {{ number_format($job->salary_max) }}
                                    @endif
                                    {{ $job->salary_currency }}
                                </span>
                                @endif
                            </div>

                            {{-- Skills --}}
                            @if(!empty($jobSkills))
                            <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.75rem">
                                @foreach(array_slice($jobSkills, 0, 5) as $skill)
                                <span style="font-size:.72rem;padding:.2rem .6rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-weight:600">
                                    {{ $skill }}
                                </span>
                                @endforeach
                                @if(count($jobSkills) > 5)
                                <span style="font-size:.72rem;padding:.2rem .6rem;background:var(--bg-hover);color:var(--text-muted);border-radius:var(--radius-full)">
                                    +{{ count($jobSkills) - 5 }}
                                </span>
                                @endif
                            </div>
                            @endif

                            {{-- Footer Row --}}
                            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
                                <div style="font-size:.8rem;color:var(--text-muted);display:flex;gap:.875rem">
                                    <span><i class="fas fa-clock"></i> {{ $job->created_at->diffForHumans() }}</span>
                                    @if($job->applications_count > 0)
                                    <span><i class="fas fa-users"></i> {{ $job->applications_count }} {{ __('messages.applicants') }}</span>
                                    @endif
                                    @if($job->deadline)
                                    @php $diff = now()->diffInDays($job->deadline, false); @endphp
                                    <span style="color:{{ $diff <= 3 ? 'var(--danger)' : ($diff <= 7 ? 'var(--warning)' : 'inherit') }}">
                                        <i class="fas fa-hourglass-half"></i>
                                        {{ $diff > 0 ? $diff . ' ' . __('messages.days_left') : __('messages.expired') }}
                                    </span>
                                    @endif
                                </div>

                                <div style="display:flex;gap:.5rem">
                                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-ghost btn-sm">
                                        {{ __('messages.view_details') }}
                                    </a>
                                    @auth
                                    @if(auth()->user()->isUser())
                                        @if(auth()->user()->hasAppliedTo($job))
                                        <span class="btn btn-ghost btn-sm" style="cursor:default;color:var(--success)">
                                            <i class="fas fa-check"></i> {{ __('messages.applied') }}
                                        </span>
                                        @else
                                        <a href="{{ route('jobs.show', $job) }}" class="btn btn-primary btn-sm">
                                            {{ __('messages.apply_now') }}
                                        </a>
                                        @endif
                                    @endif
                                    @else
                                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm">
                                        {{ __('messages.apply_now') }}
                                    </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                <h3>{{ __('messages.no_jobs_found') }}</h3>
                <p>{{ __('messages.try_different_filters') }}</p>
                <a href="{{ route('jobs.index') }}" class="btn btn-outline" style="margin-top:1rem">
                    <i class="fas fa-times"></i> {{ __('messages.clear_filters') }}
                </a>
            </div>
            @endforelse

            {{-- Pagination --}}
            @if($jobs->hasPages())
            <div class="pagination" style="margin-top:1.5rem">
                @if(!$jobs->onFirstPage())
                    <a href="{{ $jobs->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                @endif
                @foreach($jobs->getUrlRange(max(1,$jobs->currentPage()-2), min($jobs->lastPage(),$jobs->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page === $jobs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
                @if($jobs->hasMorePages())
                    <a href="{{ $jobs->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
async function toggleSave(jobId, btn) {
    btn.disabled = true;
    try {
        const res = await fetch(`/jobs/${jobId}/save`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        if (data.saved) {
            btn.style.color = 'var(--warning)';
            toastr.success('{{ __("messages.job_saved") }}');
        } else {
            btn.style.color = 'var(--text-muted)';
            toastr.info('{{ __("messages.job_unsaved") }}');
        }
    } catch (e) {
        toastr.error('{{ __("messages.error_occurred") }}');
    }
    btn.disabled = false;
}
</script>
@endpush
@endsection