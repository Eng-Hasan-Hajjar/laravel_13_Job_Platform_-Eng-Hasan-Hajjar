@extends('layouts.app')
@section('title', __('messages.home'))

@section('content')

{{-- HERO SECTION --}}
<section class="hero">
    <div class="hero-content">
        <h1>{{ __('messages.hero_title') }}</h1>
        <p>{{ __('messages.hero_subtitle') }}</p>

        <div class="hero-search">
            <i class="fas fa-search" style="color:var(--text-muted)"></i>
            <input type="text" id="heroSearch"
                   placeholder="{{ __('messages.search_placeholder') }}"
                   onkeypress="if(event.key==='Enter'){window.location='/jobs?q='+this.value}">
            <select id="heroLocation" style="border:none;background:none;color:var(--text-secondary);font-size:.875rem;outline:none;cursor:pointer;min-width:100px">
                <option value="">{{ __('messages.all_locations') }}</option>
                @foreach($locations as $location)
                    <option value="{{ $location }}">{{ $location }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" onclick="
                const q = document.getElementById('heroSearch').value;
                const l = document.getElementById('heroLocation').value;
                window.location='/jobs?q='+q+'&location='+l;
            ">{{ __('messages.search') }}</button>
        </div>

        <div class="hero-stats">
            <div class="hero-stat">
                <div class="hero-stat-value">{{ number_format($stats['total_jobs']) }}+</div>
                <div class="hero-stat-label">{{ __('messages.active_jobs') }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-value">{{ number_format($stats['total_companies']) }}+</div>
                <div class="hero-stat-label">{{ __('messages.companies') }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-value">{{ number_format($stats['total_users']) }}+</div>
                <div class="hero-stat-label">{{ __('messages.job_seekers') }}</div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat-value">{{ number_format($stats['placements']) }}+</div>
                <div class="hero-stat-label">{{ __('messages.successful_hires') }}</div>
            </div>
        </div>
    </div>
</section>

{{-- POPULAR CATEGORIES --}}
<div class="page-container">
    <div style="margin-bottom:2rem">
        <h2 style="font-size:1.375rem;font-weight:800;margin-bottom:.5rem">{{ __('messages.browse_by_category') }}</h2>
        <p style="color:var(--text-secondary);font-size:.9rem">{{ __('messages.category_subtitle') }}</p>
    </div>

    <div class="grid grid-4 stagger" style="margin-bottom:3rem">
        @foreach($categories as $category)
        <a href="{{ route('jobs.index', ['category' => $category->slug]) }}"
           class="card animate-slide-up" style="text-decoration:none;text-align:center;padding:1.5rem;cursor:pointer">
            <div style="font-size:2rem;margin-bottom:.75rem">{{ $category->icon }}</div>
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem">{{ $category->name }}</div>
            <div style="font-size:.8rem;color:var(--text-muted)">{{ $category->jobs_count }} {{ __('messages.jobs') }}</div>
        </a>
        @endforeach
    </div>

    {{-- FEATURED JOBS --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:.75rem">
        <div>
            <h2 style="font-size:1.375rem;font-weight:800;margin-bottom:.25rem">{{ __('messages.featured_jobs') }}</h2>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.latest_opportunities') }}</p>
        </div>
        <a href="{{ route('jobs.index') }}" class="btn btn-outline">
            {{ __('messages.view_all_jobs') }} <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
        </a>
    </div>

    <div class="grid grid-auto stagger" style="margin-bottom:3rem">
        @forelse($featuredJobs as $job)
            @include('components.job-card', ['job' => $job])
        @empty
            <div class="empty-state" style="grid-column:1/-1">
                <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                <h3>{{ __('messages.no_jobs_yet') }}</h3>
                <p>{{ __('messages.check_back_soon') }}</p>
            </div>
        @endforelse
    </div>

    {{-- AI RECOMMENDATION BANNER (for logged in users) --}}
    @auth
    @if(auth()->user()->role === 'user')
    <div class="card" style="background:linear-gradient(135deg, var(--primary), var(--secondary));border:none;margin-bottom:3rem">
        <div class="card-body" style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap;color:white">
            <div style="font-size:3rem">🤖</div>
            <div style="flex:1">
                <h3 style="font-size:1.125rem;font-weight:800;margin-bottom:.375rem">{{ __('messages.ai_recommendation_title') }}</h3>
                <p style="opacity:.9;font-size:.875rem">{{ __('messages.ai_recommendation_desc') }}</p>
            </div>
            <a href="{{ route('jobs.recommended') }}" class="btn" style="background:white;color:var(--primary);font-weight:700">
                <i class="fas fa-magic"></i> {{ __('messages.get_recommendations') }}
            </a>
        </div>
    </div>
    @endif
    @endauth

    {{-- TOP COMPANIES --}}
    <div style="margin-bottom:1.5rem">
        <h2 style="font-size:1.375rem;font-weight:800;margin-bottom:.5rem">{{ __('messages.top_companies') }}</h2>
    </div>

    <div class="grid grid-4 stagger">
        @foreach($topCompanies as $company)
        <a href="{{ route('companies.show', $company) }}"
           class="card animate-slide-up" style="text-decoration:none;padding:1.5rem;text-align:center">
            @if($company->logo)
                <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                     style="width:60px;height:60px;object-fit:cover;border-radius:var(--radius);margin:0 auto .75rem;border:1px solid var(--border)">
            @else
                <div class="avatar avatar-lg" style="margin:0 auto .75rem">
                    {{ mb_strtoupper(mb_substr($company->name, 0, 2)) }}
                </div>
            @endif
            <div style="font-weight:700;color:var(--text-primary);margin-bottom:.25rem">{{ $company->name }}</div>
            <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.5rem">{{ $company->industry }}</div>
            <div style="display:flex;align-items:center;justify-content:center;gap:.25rem;font-size:.8rem;color:var(--warning)">
                @for($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star{{ $i <= round($company->average_rating) ? '' : '-o' }}"></i>
                @endfor
                <span style="color:var(--text-muted);margin-left:.25rem">({{ $company->reviews_count }})</span>
            </div>
            <div style="font-size:.75rem;color:var(--primary);margin-top:.375rem">
                {{ $company->active_jobs_count }} {{ __('messages.open_positions') }}
            </div>
        </a>
        @endforeach
    </div>
</div>

@endsection