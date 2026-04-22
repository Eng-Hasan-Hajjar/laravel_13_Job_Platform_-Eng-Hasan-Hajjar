@extends('layouts.app')
@section('title', __('messages.browse_jobs'))

@section('content')
<div class="page-container">

    <!-- Page Header -->
    <div style="margin-bottom:1.5rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
            {{ __('messages.browse_jobs') }}
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem">
            {{ $jobs->total() }} {{ __('messages.jobs_found') }}
            @if(request('q')) {{ __('messages.for') }} "<strong>{{ request('q') }}</strong>" @endif
        </p>
    </div>

    <div style="display:grid;grid-template-columns:280px 1fr;gap:1.5rem;align-items:start">

        <!-- FILTER PANEL -->
        <aside>
            <form id="filterForm" method="GET" action="{{ route('jobs.index') }}">
                @if(request('q'))
                    <input type="hidden" name="q" value="{{ request('q') }}">
                @endif

                <div class="filter-panel">
                    <!-- Filter Header -->
                    <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between">
                        <span style="font-weight:700;font-size:.9rem">
                            <i class="fas fa-filter" style="color:var(--primary);margin-right:.5rem"></i>
                            {{ __('messages.filters') }}
                        </span>
                        <a href="{{ route('jobs.index') }}" class="btn btn-ghost btn-sm" style="font-size:.75rem">
                            {{ __('messages.reset') }}
                        </a>
                    </div>

                    <!-- Job Type -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.job_type') }}</div>
                        @foreach(['full-time', 'part-time', 'freelance', 'remote', 'internship'] as $type)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="type[]" value="{{ $type }}"
                                   {{ in_array($type, request('type', [])) ? 'checked' : '' }}
                                   onchange="document.getElementById('filterForm').submit()">
                            {{ __('messages.' . str_replace('-', '_', $type)) }}
                        </label>
                        @endforeach
                    </div>

                    <!-- Location -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.location') }}</div>
                        <input type="text" name="location" class="form-control"
                               placeholder="{{ __('messages.city_or_country') }}"
                               value="{{ request('location') }}"
                               style="font-size:.8rem">
                    </div>

                    <!-- Category -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.category') }}</div>
                        @foreach($categories as $category)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="category[]" value="{{ $category->id }}"
                                   {{ in_array($category->id, request('category', [])) ? 'checked' : '' }}
                                   onchange="document.getElementById('filterForm').submit()">
                            {{ $category->name }}
                            <span style="font-size:.7rem;color:var(--text-muted);margin-left:auto">({{ $category->jobs_count }})</span>
                        </label>
                        @endforeach
                    </div>

                    <!-- Salary Range -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.salary_range') }}</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:.5rem">
                            <input type="number" name="salary_min" class="form-control"
                                   placeholder="{{ __('messages.min') }}"
                                   value="{{ request('salary_min') }}"
                                   style="font-size:.8rem">
                            <input type="number" name="salary_max" class="form-control"
                                   placeholder="{{ __('messages.max') }}"
                                   value="{{ request('salary_max') }}"
                                   style="font-size:.8rem">
                        </div>
                    </div>

                    <!-- Experience -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.experience') }}</div>
                        @foreach(['entry', 'junior', 'mid', 'senior', 'lead'] as $exp)
                        <label class="filter-checkbox">
                            <input type="checkbox" name="experience[]" value="{{ $exp }}"
                                   {{ in_array($exp, request('experience', [])) ? 'checked' : '' }}
                                   onchange="document.getElementById('filterForm').submit()">
                            {{ __('messages.exp_' . $exp) }}
                        </label>
                        @endforeach
                    </div>

                    <!-- Posted Date -->
                    <div class="filter-section">
                        <div class="filter-section-title">{{ __('messages.posted_date') }}</div>
                        @foreach(['today', 'week', 'month'] as $period)
                        <label class="filter-checkbox">
                            <input type="radio" name="posted" value="{{ $period }}"
                                   {{ request('posted') === $period ? 'checked' : '' }}
                                   onchange="document.getElementById('filterForm').submit()">
                            {{ __('messages.posted_' . $period) }}
                        </label>
                        @endforeach
                    </div>

                    <div style="padding:1rem 1.25rem">
                        <button type="submit" class="btn btn-primary" style="width:100%">
                            <i class="fas fa-search"></i> {{ __('messages.apply_filters') }}
                        </button>
                    </div>
                </div>
            </form>
        </aside>

        <!-- JOB RESULTS -->
        <div>
            <!-- Sort Bar -->
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
                <div style="font-size:.875rem;color:var(--text-secondary)">
                    {{ __('messages.showing') }} {{ $jobs->firstItem() }}–{{ $jobs->lastItem() }}
                    {{ __('messages.of') }} {{ $jobs->total() }} {{ __('messages.results') }}
                </div>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <select class="form-control" style="width:auto;font-size:.8rem"
                            onchange="window.location='?{{ http_build_query(array_merge(request()->except('sort'), ['sort' => ''])) }}&sort='+this.value">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                        <option value="salary_high" {{ request('sort') === 'salary_high' ? 'selected' : '' }}>{{ __('messages.sort_salary_high') }}</option>
                        <option value="salary_low" {{ request('sort') === 'salary_low' ? 'selected' : '' }}>{{ __('messages.sort_salary_low') }}</option>
                        <option value="relevant" {{ request('sort') === 'relevant' ? 'selected' : '' }}>{{ __('messages.sort_relevant') }}</option>
                    </select>
                    <!-- View Toggle -->
                    <button class="nav-icon-btn" id="gridViewBtn" onclick="setView('grid')">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="nav-icon-btn" id="listViewBtn" onclick="setView('list')">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>

            <!-- Results -->
            <div id="jobResults" class="grid grid-auto stagger">
                @forelse($jobs as $job)
                    @include('components.job-card', ['job' => $job])
                @empty
                    <div class="empty-state" style="grid-column:1/-1">
                        <div class="empty-state-icon"><i class="fas fa-search"></i></div>
                        <h3>{{ __('messages.no_jobs_found') }}</h3>
                        <p>{{ __('messages.try_different_filters') }}</p>
                        <a href="{{ route('jobs.index') }}" class="btn btn-primary" style="margin-top:1rem">
                            {{ __('messages.clear_filters') }}
                        </a>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($jobs->hasPages())
            <div class="pagination" style="margin-top:2rem">
                @if($jobs->onFirstPage())
                    <span class="page-link disabled"><i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i></span>
                @else
                    <a href="{{ $jobs->previousPageUrl() }}" class="page-link">
                        <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i>
                    </a>
                @endif

                @foreach($jobs->getUrlRange(max(1, $jobs->currentPage()-2), min($jobs->lastPage(), $jobs->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page === $jobs->currentPage() ? 'active' : '' }}">
                        {{ $page }}
                    </a>
                @endforeach

                @if($jobs->hasMorePages())
                    <a href="{{ $jobs->nextPageUrl() }}" class="page-link">
                        <i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                    </a>
                @else
                    <span class="page-link disabled"><i class="fas fa-chevron-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i></span>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function setView(type) {
    const container = document.getElementById('jobResults');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    if (type === 'list') {
        container.style.gridTemplateColumns = '1fr';
        gridBtn.classList.remove('active');
        listBtn.classList.add('active');
        localStorage.setItem('jobView', 'list');
    } else {
        container.style.gridTemplateColumns = '';
        container.className = 'grid grid-auto stagger';
        gridBtn.classList.add('active');
        listBtn.classList.remove('active');
        localStorage.setItem('jobView', 'grid');
    }
}
// Restore view preference
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('jobView');
    if (saved) setView(saved);
});
</script>
@endpush

@endsection