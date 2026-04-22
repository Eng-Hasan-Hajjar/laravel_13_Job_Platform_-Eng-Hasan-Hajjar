@extends('layouts.app')
@section('title', __('messages.recommended_jobs'))

@section('content')
<div class="page-container">

    <!-- Header Banner -->
    <div class="card" style="margin-bottom:2rem;background:linear-gradient(135deg,#1e3a5f,#2563eb,#7c3aed);border:none;overflow:visible">
        <div class="card-body" style="padding:2rem;color:white;position:relative">
            <div style="position:absolute;right:1.5rem;top:50%;transform:translateY(-50%);font-size:5rem;opacity:.2">🤖</div>
            <div style="position:relative;z-index:1;max-width:600px">
                <div style="display:inline-flex;align-items:center;gap:.5rem;background:rgba(255,255,255,.15);border-radius:var(--radius-full);padding:.375rem .875rem;font-size:.8rem;font-weight:600;margin-bottom:1rem">
                    <i class="fas fa-magic"></i> {{ __('messages.powered_by_ai') }}
                </div>
                <h1 style="font-size:1.75rem;font-weight:800;margin-bottom:.5rem">{{ __('messages.recommended_jobs') }}</h1>
                <p style="opacity:.85;font-size:.9rem;line-height:1.6;margin-bottom:1.25rem">
                    {{ __('messages.recommendations_desc') }}
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                    @foreach(auth()->user()->skills ?? [] as $skill)
                    @if($loop->index < 6)
                    <span style="background:rgba(255,255,255,.2);padding:.25rem .75rem;border-radius:var(--radius-full);font-size:.8rem;font-weight:600">
                        {{ $skill }}
                    </span>
                    @endif
                    @endforeach
                    @if(empty(auth()->user()->skills))
                    <a href="{{ route('user.profile') }}" style="background:rgba(255,255,255,.2);padding:.375rem .875rem;border-radius:var(--radius-full);font-size:.8rem;font-weight:600;text-decoration:none;color:white">
                        <i class="fas fa-plus"></i> {{ __('messages.add_skills_to_improve') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Match Score Legend -->
    <div style="display:flex;align-items:center;gap:1.25rem;margin-bottom:1.5rem;flex-wrap:wrap">
        <span style="font-size:.875rem;font-weight:700;color:var(--text-secondary)">{{ __('messages.match_score') }}:</span>
        @foreach([['min'=>70,'color'=>'var(--success)','label'=>__('messages.excellent_match')],['min'=>40,'color'=>'var(--warning)','label'=>__('messages.good_match')],['min'=>0,'color'=>'var(--text-muted)','label'=>__('messages.new_opportunities')]] as $level)
        <div style="display:flex;align-items:center;gap:.375rem;font-size:.8rem">
            <div style="width:10px;height:10px;border-radius:50%;background:{{ $level['color'] }}"></div>
            <span style="color:var(--text-secondary)">{{ $level['label'] }}</span>
        </div>
        @endforeach
    </div>

    <!-- Jobs Grid -->
    @if($jobs->isEmpty())
    <div class="empty-state">
        <div class="empty-state-icon">🤖</div>
        <h3>{{ __('messages.no_recommendations_yet') }}</h3>
        <p>{{ __('messages.complete_profile_for_recommendations') }}</p>
        <a href="{{ route('user.profile') }}" class="btn btn-primary" style="margin-top:1rem">
            <i class="fas fa-user-edit"></i> {{ __('messages.complete_profile') }}
        </a>
    </div>
    @else
    <div class="grid grid-auto stagger">
        @foreach($jobs as $job)
        <div class="job-card animate-slide-up" style="position:relative">
            <!-- Match Score Badge -->
            @if($job->recommendation_score > 0)
            <div style="position:absolute;top:.875rem;{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}:.875rem;z-index:2;
                background:{{ $job->recommendation_score >= 70 ? 'var(--success)' : ($job->recommendation_score >= 40 ? 'var(--warning)' : 'var(--primary)') }};
                color:white;padding:.25rem .625rem;border-radius:var(--radius-full);font-size:.7rem;font-weight:800;
                display:flex;align-items:center;gap:.25rem;box-shadow:var(--shadow-md)">
                <i class="fas fa-bolt"></i> {{ $job->recommendation_score }}%
            </div>
            @endif

            <div class="job-card-header">
                @if($job->company->logo)
                    <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}" class="company-logo">
                @else
                    <div class="avatar avatar-lg" style="border-radius:var(--radius)">
                        {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
                    </div>
                @endif
                <div class="job-info">
                    <h3>
                        <a href="{{ route('jobs.show', $job) }}" style="text-decoration:none;color:inherit">
                            {{ $job->title }}
                        </a>
                    </h3>
                    <div class="company-name">{{ $job->company->name }}</div>
                </div>
            </div>

            <div class="job-meta">
                @if($job->location)
                <span class="job-tag location"><i class="fas fa-map-marker-alt"></i> {{ $job->location }}</span>
                @endif
                <span class="job-tag type"><i class="fas fa-clock"></i> {{ __('messages.' . str_replace('-','_',$job->type)) }}</span>
                @if($job->is_remote)
                <span class="job-tag remote"><i class="fas fa-wifi"></i> {{ __('messages.remote') }}</span>
                @endif
            </div>

            <!-- Why Recommended -->
            @php
                $reasons = [];
                $userSkills = array_map('mb_strtolower', auth()->user()->skills ?? []);
                $jobSkills  = array_map('mb_strtolower', $job->skills ?? []);
                $matched    = array_intersect($userSkills, $jobSkills);
                if (count($matched) > 0) $reasons[] = count($matched) . ' ' . __('messages.matching_skills');
                if (in_array($job->type, auth()->user()->preferred_job_types ?? [])) $reasons[] = __('messages.preferred_type');
                if ($job->experience_level === auth()->user()->experience_level) $reasons[] = __('messages.matches_experience');
            @endphp
            @if(!empty($reasons))
            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.875rem;display:flex;align-items:center;gap:.375rem;flex-wrap:wrap">
                <i class="fas fa-robot" style="color:var(--primary)"></i>
                @foreach($reasons as $r)<span style="background:var(--bg-hover);padding:.15rem .5rem;border-radius:var(--radius-full)">{{ $r }}</span>@endforeach
            </div>
            @endif

            <div class="job-card-footer">
                <span class="job-posted-date"><i class="fas fa-clock"></i> {{ $job->created_at->diffForHumans() }}</span>
                <div style="display:flex;gap:.5rem">
                    @if(auth()->user()->hasAppliedTo($job))
                    <span class="btn btn-ghost btn-sm" style="cursor:default;color:var(--success)">
                        <i class="fas fa-check"></i> {{ __('messages.applied') }}
                    </span>
                    @else
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-primary btn-sm">
                        {{ __('messages.apply_now') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Improve Recommendations CTA -->
    @if($jobs->isNotEmpty())
    <div class="card" style="margin-top:2rem;text-align:center;padding:1.5rem">
        <i class="fas fa-sliders-h" style="font-size:1.5rem;color:var(--primary);margin-bottom:.625rem;display:block"></i>
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.375rem">{{ __('messages.improve_recommendations') }}</h3>
        <p style="font-size:.875rem;color:var(--text-secondary);margin-bottom:1rem">{{ __('messages.improve_recommendations_desc') }}</p>
        <div style="display:flex;gap:.75rem;justify-content:center">
            <a href="{{ route('user.profile') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-user-edit"></i> {{ __('messages.update_profile') }}
            </a>
            <a href="{{ route('user.cv') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-file-pdf"></i> {{ __('messages.upload_cv') }}
            </a>
        </div>
    </div>
    @endif
</div>
@endsection