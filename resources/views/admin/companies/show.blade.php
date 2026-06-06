@extends('layouts.app')
@section('title', $company->name)

@section('content')
<div class="page-container">

    <a href="{{ route('admin.companies.index') }}" style="display:inline-flex;align-items:center;gap:.5rem;color:var(--text-muted);font-size:.875rem;margin-bottom:1rem;text-decoration:none">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ __('messages.back_to_companies') }}
    </a>

    <!-- Header -->
    <div class="card" style="margin-bottom:1.5rem;background:linear-gradient(135deg,#1e3a5f,#2563eb);border:none;color:white">
        <div class="card-body" style="padding:2rem">
            <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
                @if($company->logo)
                    <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                         style="width:100px;height:100px;border-radius:var(--radius-lg);object-fit:cover;border:3px solid rgba(255,255,255,.4);background:white">
                @else
                    <div class="avatar" style="width:100px;height:100px;font-size:2rem;border-radius:var(--radius-lg);border:3px solid rgba(255,255,255,.4)">
                        {{ mb_strtoupper(mb_substr($company->name,0,2)) }}
                    </div>
                @endif

                <div style="flex:1;min-width:200px">
                    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ $company->name }}</h1>
                    @if($company->industry)<div style="opacity:.85;margin-bottom:.25rem"><i class="fas fa-briefcase"></i> {{ $company->industry }}</div>@endif
                    @if($company->location)<div style="opacity:.8;font-size:.875rem"><i class="fas fa-map-marker-alt"></i> {{ $company->location }}</div>@endif
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-top:.75rem">
                        @if($company->is_verified)
                        <span style="background:rgba(16,185,129,.3);padding:.25rem .75rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600">
                            <i class="fas fa-check-circle"></i> {{ __('messages.verified') }}
                        </span>
                        @endif
                        <span style="background:rgba(255,255,255,.2);padding:.25rem .75rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600">
                            {{ $company->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </div>
                </div>

                <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                    <form action="{{ route('admin.companies.verify', $company) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn" style="background:rgba(255,255,255,.2);color:white;border:1px solid rgba(255,255,255,.3)">
                            <i class="fas {{ $company->is_verified ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                            {{ $company->is_verified ? __('messages.unverify') : __('messages.verify') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.companies.destroy', $company) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.8);color:white;border:none"
                                data-confirm-delete="{{ __('messages.delete_company_confirm') }}">
                            <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['label'=>__('messages.total_jobs'),    'value'=>$stats['total_jobs'],   'icon'=>'fa-briefcase',    'color'=>'primary'],
            ['label'=>__('messages.active_jobs'),   'value'=>$stats['active_jobs'],  'icon'=>'fa-check-circle', 'color'=>'success'],
            ['label'=>__('messages.applications'),  'value'=>$stats['applications'], 'icon'=>'fa-file-alt',     'color'=>'info'],
            ['label'=>__('messages.reviews'),       'value'=>$stats['reviews'] . ' (' . $stats['avg_rating'] . '⭐)', 'icon'=>'fa-star', 'color'=>'warning'],
        ] as $s)
        <div class="stat-card {{ $s['color'] }}">
            <div class="stat-card-icon"><i class="fas {{ $s['icon'] }}"></i></div>
            <div>
                <div class="stat-value">{{ $s['value'] }}</div>
                <div class="stat-label">{{ $s['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        <!-- Company Info -->
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('messages.company_info') }}</span></div>
            <div class="card-body">
                @if($company->description)
                <div style="padding:1rem;background:var(--bg-hover);border-radius:var(--radius);margin-bottom:1rem;font-size:.875rem;line-height:1.6">
                    {{ $company->description }}
                </div>
                @endif

                <div style="display:flex;flex-direction:column;gap:.75rem">
                    @foreach([
                        ['label'=>__('messages.email'),     'value'=>$company->email, 'icon'=>'fa-envelope'],
                        ['label'=>__('messages.phone'),     'value'=>$company->phone, 'icon'=>'fa-phone'],
                        ['label'=>__('messages.website'),   'value'=>$company->website, 'icon'=>'fa-globe', 'is_url'=>true],
                        ['label'=>__('messages.employees'), 'value'=>$company->employees_count, 'icon'=>'fa-users'],
                        ['label'=>__('messages.founded'),   'value'=>$company->founded_year, 'icon'=>'fa-calendar'],
                        ['label'=>__('messages.owner'),     'value'=>$company->user->name ?? '—', 'icon'=>'fa-user'],
                    ] as $row)
                    @if($row['value'])
                    <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem 0;border-bottom:1px solid var(--border)">
                        <i class="fas {{ $row['icon'] }}" style="color:var(--primary);width:18px;text-align:center"></i>
                        <span style="font-size:.8rem;color:var(--text-muted);flex:1">{{ $row['label'] }}</span>
                        @if(isset($row['is_url']) && $row['is_url'])
                            <a href="{{ $row['value'] }}" target="_blank" style="font-weight:600;font-size:.875rem;color:var(--primary);text-decoration:none">{{ $row['value'] }}</a>
                        @else
                            <span style="font-weight:600;font-size:.875rem">{{ $row['value'] }}</span>
                        @endif
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Jobs -->
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('messages.recent_jobs') }}</span></div>
            <div class="card-body" style="padding:0">
                @forelse($company->jobs->take(8) as $job)
                <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border)">
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.875rem;font-weight:600">{{ $job->title }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">
                            {{ __('messages.' . str_replace('-','_',$job->type)) }} •
                            {{ $job->applications->count() }} {{ __('messages.applications') }} •
                            {{ $job->created_at->diffForHumans() }}
                        </div>
                    </div>
                    @if($job->is_active)
                    <span class="status-badge active">{{ __('messages.active') }}</span>
                    @else
                    <span class="status-badge inactive">{{ __('messages.inactive') }}</span>
                    @endif
                </div>
                @empty
                <div style="padding:2rem;text-align:center;color:var(--text-muted)">
                    <i class="fas fa-briefcase" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem"></i>
                    {{ __('messages.no_jobs_yet') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection