@extends('layouts.app')
@section('title', __('messages.companies'))

@section('content')
<div class="page-container">

    <!-- Header -->
    <div style="margin-bottom:2rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.5rem">🏢 {{ __('messages.explore_companies') }}</h1>
        <p style="color:var(--text-secondary)">{{ $companies->total() }} {{ __('messages.companies_found') }}</p>
    </div>

    <!-- Search & Filter -->
    <div class="card" style="margin-bottom:1.5rem">
        <div class="card-body" style="padding:1rem 1.25rem">
            <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
                <div style="flex:1;min-width:200px;position:relative">
                    <i class="fas fa-search" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:.875rem"></i>
                    <input type="text" name="search" class="form-control" style="padding-left:2.25rem"
                           placeholder="{{ __('messages.search_companies') }}" value="{{ request('search') }}">
                </div>
                <select name="industry" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_industries') }}</option>
                    @foreach($industries as $ind)
                    <option value="{{ $ind }}" {{ request('industry') === $ind ? 'selected' : '' }}>{{ $ind }}</option>
                    @endforeach
                </select>
                <select name="sort" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="latest" {{ request('sort','latest') === 'latest' ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                    <option value="rating" {{ request('sort') === 'rating' ? 'selected' : '' }}>{{ __('messages.highest_rated') }}</option>
                    <option value="jobs" {{ request('sort') === 'jobs' ? 'selected' : '' }}>{{ __('messages.most_jobs') }}</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                @if(request()->hasAny(['search','industry']))
                <a href="{{ route('companies.index') }}" class="btn btn-ghost">{{ __('messages.reset') }}</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Companies Grid -->
    <div class="grid grid-auto stagger">
        @forelse($companies as $company)
        <a href="{{ route('companies.show', $company) }}"
           class="card animate-slide-up" style="text-decoration:none;transition:var(--transition);display:block"
           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='var(--shadow-lg)'"
           onmouseout="this.style.transform='';this.style.boxShadow=''">

            <!-- Cover -->
            <div style="height:80px;background:linear-gradient(135deg,{{ ['#2563eb','#7c3aed','#06b6d4','#10b981','#f59e0b'][($company->id % 5)] }},{{ ['#7c3aed','#06b6d4','#10b981','#f59e0b','#2563eb'][($company->id % 5)] }});border-radius:var(--radius-lg) var(--radius-lg) 0 0;position:relative">
                @if($company->cover_image)
                <img src="{{ Storage::url($company->cover_image) }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:var(--radius-lg) var(--radius-lg) 0 0">
                @endif
                @if($company->is_verified)
                <div style="position:absolute;top:.5rem;right:.5rem;background:rgba(255,255,255,.9);color:var(--primary);padding:.2rem .5rem;border-radius:var(--radius-full);font-size:.65rem;font-weight:700">
                    <i class="fas fa-check-circle"></i> {{ __('messages.verified') }}
                </div>
                @endif
            </div>

            <!-- Logo -->
            <div style="padding:0 1.25rem">
                <div style="width:60px;height:60px;border-radius:var(--radius);border:3px solid var(--bg-card);overflow:hidden;margin-top:-30px;background:var(--bg-hover);box-shadow:var(--shadow-md);position:relative">
                    @if($company->logo)
                        <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}" style="width:100%;height:100%;object-fit:cover">
                    @else
                        <div class="avatar" style="width:100%;height:100%;border-radius:0;font-size:1rem">
                            {{ mb_strtoupper(mb_substr($company->name, 0, 2)) }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Info -->
            <div style="padding:.875rem 1.25rem 1.25rem">
                <div style="font-weight:800;font-size:.95rem;color:var(--text-primary);margin-bottom:.2rem">{{ $company->name }}</div>
                <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.625rem">{{ $company->industry }}</div>

                @if($company->reviews_count > 0)
                <div style="display:flex;align-items:center;gap:.25rem;margin-bottom:.625rem">
                    @for($i=1;$i<=5;$i++)
                    <i class="fas fa-star{{ $i <= round($company->average_rating) ? '' : '-o' }}" style="color:var(--warning);font-size:.75rem"></i>
                    @endfor
                    <span style="font-size:.75rem;color:var(--text-muted);margin-left:.25rem">{{ number_format($company->average_rating,1) }}</span>
                </div>
                @endif

                <div style="display:flex;justify-content:space-between;align-items:center;font-size:.8rem">
                    @if($company->location)
                    <span style="color:var(--text-muted)"><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> {{ Str::limit($company->location, 20) }}</span>
                    @endif
                    <span style="background:var(--primary-light);color:var(--primary);padding:.2rem .625rem;border-radius:var(--radius-full);font-weight:700">
                        {{ $company->active_jobs_count }} {{ __('messages.jobs') }}
                    </span>
                </div>
            </div>
        </a>
        @empty
        <div class="empty-state" style="grid-column:1/-1">
            <div class="empty-state-icon"><i class="fas fa-building"></i></div>
            <h3>{{ __('messages.no_companies_found') }}</h3>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($companies->hasPages())
    <div class="pagination" style="margin-top:2rem">
        @if(!$companies->onFirstPage())<a href="{{ $companies->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>@endif
        @foreach($companies->getUrlRange(max(1,$companies->currentPage()-2),min($companies->lastPage(),$companies->currentPage()+2)) as $p=>$url)
            <a href="{{ $url }}" class="page-link {{ $p===$companies->currentPage()?'active':'' }}">{{ $p }}</a>
        @endforeach
        @if($companies->hasMorePages())<a href="{{ $companies->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>@endif
    </div>
    @endif
</div>
@endsection