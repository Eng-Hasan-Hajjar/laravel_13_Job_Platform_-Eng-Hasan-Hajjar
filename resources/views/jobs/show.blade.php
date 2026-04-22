@extends('layouts.app')
@section('title', $job->title . ' - ' . $job->company->name)

@section('content')
<div class="page-container">
    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

        <!-- LEFT: Job Details -->
        <div>
            <!-- Job Header Card -->
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-body">
                    <div style="display:flex;align-items:flex-start;gap:1.25rem;flex-wrap:wrap">
                        <!-- Logo -->
                        @if($job->company->logo)
                            <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}"
                                 style="width:80px;height:80px;object-fit:cover;border-radius:var(--radius-lg);border:1px solid var(--border)">
                        @else
                            <div class="avatar avatar-xl" style="border-radius:var(--radius-lg)">
                                {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
                            </div>
                        @endif

                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;flex-wrap:wrap">
                                <div>
                                    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.375rem">{{ $job->title }}</h1>
                                    <a href="{{ route('companies.show', $job->company) }}"
                                       style="font-size:1rem;color:var(--primary);font-weight:600;text-decoration:none">
                                        {{ $job->company->name }}
                                    </a>
                                </div>
                                @if($job->is_featured)
                                    <span class="status-badge active">⭐ {{ __('messages.featured') }}</span>
                                @endif
                            </div>

                            <div class="job-meta" style="margin-top:.75rem">
                                @if($job->location)
                                <span class="job-tag location"><i class="fas fa-map-marker-alt"></i> {{ $job->location }}</span>
                                @endif
                                @if($job->type)
                                <span class="job-tag type"><i class="fas fa-clock"></i> {{ __('messages.' . str_replace('-','_', $job->type)) }}</span>
                                @endif
                                @if($job->is_remote)
                                <span class="job-tag remote"><i class="fas fa-wifi"></i> {{ __('messages.remote') }}</span>
                                @endif
                                @if($job->experience_level)
                                <span class="job-tag" style="background:var(--bg-hover);color:var(--text-secondary);border-color:var(--border)">
                                    <i class="fas fa-layer-group"></i> {{ __('messages.exp_' . $job->experience_level) }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Description -->
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.job_description') }}</span>
                </div>
                <div class="card-body">
                    <div style="color:var(--text-secondary);line-height:1.8;font-size:.9rem">
                        {!! nl2br(e($job->description)) !!}
                    </div>
                </div>
            </div>

            <!-- Requirements -->
            @if($job->requirements)
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.requirements') }}</span>
                </div>
                <div class="card-body">
                    <div style="color:var(--text-secondary);line-height:1.8;font-size:.9rem">
                        {!! nl2br(e($job->requirements)) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Skills -->
            @if($job->skills && count($job->skills) > 0)
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.required_skills') }}</span>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                        @foreach($job->skills as $skill)
                            <span style="padding:.375rem .875rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem;font-weight:600;border:1px solid rgba(37,99,235,.2)">
                                {{ $skill }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Benefits -->
            @if($job->benefits)
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.benefits') }}</span>
                </div>
                <div class="card-body">
                    <div style="color:var(--text-secondary);line-height:1.8;font-size:.9rem">
                        {!! nl2br(e($job->benefits)) !!}
                    </div>
                </div>
            </div>
            @endif

            <!-- Similar Jobs -->
            @if($similarJobs->count() > 0)
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.similar_jobs') }}</span>
                </div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        @foreach($similarJobs as $similar)
                        <a href="{{ route('jobs.show', $similar) }}"
                           style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border:1px solid var(--border);border-radius:var(--radius);text-decoration:none;transition:var(--transition)"
                           onmouseover="this.style.borderColor='var(--primary)'"
                           onmouseout="this.style.borderColor='var(--border)'">
                            <div class="avatar avatar-sm" style="border-radius:var(--radius-sm)">
                                {{ mb_strtoupper(mb_substr($similar->company->name, 0, 2)) }}
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;font-size:.875rem;color:var(--text-primary)">{{ $similar->title }}</div>
                                <div style="font-size:.8rem;color:var(--text-muted)">{{ $similar->company->name }}</div>
                            </div>
                            <span class="job-tag location" style="font-size:.7rem">{{ $similar->location }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT: Apply Card + Company Info -->
        <div style="position:sticky;top:calc(var(--navbar-height) + 1rem)">

            <!-- Apply Card -->
            <div class="card" style="margin-bottom:1.25rem;border-top:3px solid var(--primary)">
                <div class="card-body">
                    @if($job->salary_min || $job->salary_max)
                    <div style="text-align:center;margin-bottom:1.25rem">
                        <div style="font-size:1.5rem;font-weight:800;color:var(--primary)">
                            @if($job->salary_min && $job->salary_max)
                                {{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}
                            @elseif($job->salary_min)
                                {{ number_format($job->salary_min) }}+
                            @else
                                {{ __('messages.up_to') }} {{ number_format($job->salary_max) }}
                            @endif
                            <span style="font-size:.875rem;font-weight:500;color:var(--text-muted)">{{ $job->salary_currency ?? 'USD' }}/{{ __('messages.month') }}</span>
                        </div>
                    </div>
                    @endif

                    <!-- Deadline -->
                    @if($job->deadline)
                    <div style="display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--text-secondary);margin-bottom:1rem;padding:.625rem;background:var(--bg-hover);border-radius:var(--radius)">
                        <i class="fas fa-calendar-alt" style="color:var(--warning)"></i>
                        {{ __('messages.deadline') }}: <strong>{{ $job->deadline->format('d M Y') }}</strong>
                        @if($job->deadline->isPast())
                            <span class="status-badge rejected" style="font-size:.7rem">{{ __('messages.expired') }}</span>
                        @elseif($job->deadline->diffInDays() <= 3)
                            <span class="status-badge pending" style="font-size:.7rem">{{ __('messages.ending_soon') }}</span>
                        @endif
                    </div>
                    @endif

                    <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.85rem;margin-bottom:1.25rem">
                        <div style="display:flex;justify-content:space-between;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
                            <span style="color:var(--text-muted)">{{ __('messages.applications') }}</span>
                            <span style="font-weight:600">{{ $job->applications_count }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
                            <span style="color:var(--text-muted)">{{ __('messages.posted') }}</span>
                            <span style="font-weight:600">{{ $job->created_at->format('d M Y') }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between">
                            <span style="color:var(--text-muted)">{{ __('messages.job_type') }}</span>
                            <span style="font-weight:600">{{ __('messages.' . str_replace('-','_', $job->type)) }}</span>
                        </div>
                    </div>

                    @auth
                    @if(auth()->user()->role === 'user')
                        @if($hasApplied)
                            <div class="alert alert-success" style="text-align:center;margin-bottom:0">
                                <i class="fas fa-check-circle"></i>
                                {{ __('messages.already_applied') }}
                            </div>
                        @elseif($job->deadline && $job->deadline->isPast())
                            <div class="alert alert-danger" style="text-align:center;margin-bottom:0">
                                <i class="fas fa-times-circle"></i>
                                {{ __('messages.application_closed') }}
                            </div>
                        @else
                            <button onclick="openApplyModal()" class="btn btn-primary" style="width:100%;padding:.875rem">
                                <i class="fas fa-paper-plane"></i> {{ __('messages.apply_now') }}
                            </button>
                        @endif
                    @endif
                    @else
                        <a href="{{ route('login', ['redirect' => url()->current()]) }}"
                           class="btn btn-primary" style="width:100%;padding:.875rem">
                            <i class="fas fa-sign-in-alt"></i> {{ __('messages.login_to_apply') }}
                        </a>
                    @endauth
                </div>
            </div>

            <!-- Company Mini Card -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">{{ __('messages.about_company') }}</span>
                </div>
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:1rem">
                        @if($job->company->logo)
                            <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}"
                                 style="width:48px;height:48px;object-fit:cover;border-radius:var(--radius)">
                        @else
                            <div class="avatar avatar-md" style="border-radius:var(--radius)">
                                {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div style="font-weight:700">{{ $job->company->name }}</div>
                            <div style="font-size:.8rem;color:var(--text-muted)">{{ $job->company->industry }}</div>
                        </div>
                    </div>

                    <div style="font-size:.85rem;color:var(--text-secondary);line-height:1.6;margin-bottom:1rem;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden">
                        {{ $job->company->description }}
                    </div>

                    <div style="display:flex;flex-direction:column;gap:.5rem;font-size:.8rem;margin-bottom:1rem">
                        @if($job->company->website)
                        <a href="{{ $job->company->website }}" target="_blank" style="color:var(--primary);text-decoration:none">
                            <i class="fas fa-globe" style="width:16px"></i> {{ $job->company->website }}
                        </a>
                        @endif
                        @if($job->company->location)
                        <span style="color:var(--text-muted)">
                            <i class="fas fa-map-marker-alt" style="width:16px;color:var(--primary)"></i> {{ $job->company->location }}
                        </span>
                        @endif
                        @if($job->company->employees_count)
                        <span style="color:var(--text-muted)">
                            <i class="fas fa-users" style="width:16px;color:var(--primary)"></i> {{ $job->company->employees_count }} {{ __('messages.employees') }}
                        </span>
                        @endif
                    </div>

                    <!-- Rating -->
                    @if($job->company->reviews_count > 0)
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                        <div style="display:flex;gap:.125rem;color:var(--warning)">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star{{ $i <= round($job->company->average_rating) ? '' : '-o' }}"></i>
                            @endfor
                        </div>
                        <span style="font-weight:700;font-size:.9rem">{{ number_format($job->company->average_rating, 1) }}</span>
                        <span style="font-size:.8rem;color:var(--text-muted)">({{ $job->company->reviews_count }} {{ __('messages.reviews') }})</span>
                    </div>
                    @endif

                    <a href="{{ route('companies.show', $job->company) }}" class="btn btn-outline" style="width:100%;font-size:.85rem">
                        {{ __('messages.view_company') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Apply Modal -->
@auth
@if(auth()->user()->role === 'user' && !$hasApplied)
<div id="applyModal" style="display:none;position:fixed;inset:0;z-index:9999;align-items:center;justify-content:center;background:rgba(0,0,0,.5);backdrop-filter:blur(4px)">
    <div class="card animate-scale-in" style="width:100%;max-width:560px;margin:1rem;max-height:90vh;overflow-y:auto">
        <div class="card-header">
            <span class="card-title">{{ __('messages.apply_for') }}: {{ $job->title }}</span>
            <button onclick="closeApplyModal()" class="nav-icon-btn">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="card-body">
            <form action="{{ route('jobs.apply', $job) }}" method="POST" enctype="multipart/form-data" data-validate>
                @csrf

                <div class="form-group">
                    <label class="form-label">{{ __('messages.cover_letter') }}</label>
                    <textarea name="cover_letter" class="form-control" rows="5"
                              placeholder="{{ __('messages.cover_letter_placeholder') }}"
                              required></textarea>
                </div>

                <!-- CV Upload or Use Existing -->
                @if(auth()->user()->cv_path)
                <div class="form-group">
                    <label class="form-label">{{ __('messages.cv_option') }}</label>
                    <div style="display:flex;flex-direction:column;gap:.75rem">
                        <label style="display:flex;align-items:center;gap:.75rem;padding:.875rem;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer">
                            <input type="radio" name="cv_option" value="existing" checked>
                            <div>
                                <div style="font-weight:600;font-size:.875rem">{{ __('messages.use_existing_cv') }}</div>
                                <div style="font-size:.75rem;color:var(--text-muted)">{{ basename(auth()->user()->cv_path) }}</div>
                            </div>
                        </label>
                        <label style="display:flex;align-items:center;gap:.75rem;padding:.875rem;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer">
                            <input type="radio" name="cv_option" value="new">
                            <div style="font-weight:600;font-size:.875rem">{{ __('messages.upload_new_cv') }}</div>
                        </label>
                    </div>
                </div>
                @endif

                <div class="form-group" id="cvUploadGroup">
                    <label class="form-label">
                        {{ __('messages.cv_resume') }}
                        {{ auth()->user()->cv_path ? '' : '<span class="required">*</span>' }}
                    </label>
                    <div class="upload-zone">
                        <input type="file" name="cv_file" accept=".pdf,.doc,.docx" style="display:none">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--text-muted);margin-bottom:.5rem"></i>
                        <div style="font-weight:600;margin-bottom:.25rem">{{ __('messages.drop_cv_here') }}</div>
                        <div style="font-size:.8rem;color:var(--text-muted)">PDF, DOC, DOCX • {{ __('messages.max_size') }}: 5MB</div>
                        <div class="upload-info" style="margin-top:.75rem"></div>
                    </div>
                    @error('cv_file')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.expected_salary') }}</label>
                    <div class="input-group">
                        <input type="number" name="expected_salary" class="form-control"
                               placeholder="0" min="0">
                        <span class="input-group-text">{{ $job->salary_currency ?? 'USD' }}</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.availability') }}</label>
                    <select name="availability" class="form-control">
                        <option value="immediately">{{ __('messages.immediately') }}</option>
                        <option value="two_weeks">{{ __('messages.two_weeks') }}</option>
                        <option value="one_month">{{ __('messages.one_month') }}</option>
                        <option value="negotiable">{{ __('messages.negotiable') }}</option>
                    </select>
                </div>

                <div style="display:flex;gap:.75rem">
                    <button type="button" onclick="closeApplyModal()" class="btn btn-ghost" style="flex:1">
                        {{ __('messages.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary" style="flex:2">
                        <i class="fas fa-paper-plane"></i> {{ __('messages.submit_application') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openApplyModal() {
    document.getElementById('applyModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeApplyModal() {
    document.getElementById('applyModal').style.display = 'none';
    document.body.style.overflow = '';
}

// Handle CV option toggle
document.querySelectorAll('input[name="cv_option"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
        const group = document.getElementById('cvUploadGroup');
        if (e.target.value === 'existing') {
            group.style.opacity = '.5';
            group.style.pointerEvents = 'none';
        } else {
            group.style.opacity = '1';
            group.style.pointerEvents = 'auto';
        }
    });
});
</script>
@endif
@endauth

@endsection