<article class="job-card animate-slide-up">
    <div class="job-card-header">
        <!-- Company Logo -->
        @if($job->company->logo)
            <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}" class="company-logo">
        @else
            <div class="avatar avatar-lg" style="border-radius:var(--radius)">
                {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
            </div>
        @endif

        <div class="job-info" style="flex:1;min-width:0">
            <h3>
                <a href="{{ route('jobs.show', $job) }}"
                   style="text-decoration:none;color:inherit;hover:color:var(--primary)">
                    {{ $job->title }}
                </a>
            </h3>
            <div class="company-name">
                <a href="{{ route('companies.show', $job->company) }}" style="text-decoration:none;color:inherit">
                    {{ $job->company->name }}
                </a>
            </div>
        </div>

        <!-- Save/Bookmark Button -->
        @auth
        @if(auth()->user()->role === 'user')
        <button class="nav-icon-btn save-job-btn {{ auth()->user()->savedJobs->contains($job->id) ? 'saved' : '' }}"
                data-job-id="{{ $job->id }}"
                data-tooltip="{{ __('messages.save_job') }}"
                onclick="toggleSaveJob(this, {{ $job->id }})"
                style="{{ auth()->user()->savedJobs->contains($job->id) ? 'color:var(--primary);border-color:var(--primary)' : '' }}">
            <i class="fas fa-bookmark"></i>
        </button>
        @endif
        @endauth
    </div>

    <!-- Job Tags -->
    <div class="job-meta">
        @if($job->location)
        <span class="job-tag location">
            <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
        </span>
        @endif

        @if($job->type)
        <span class="job-tag type">
            <i class="fas fa-clock"></i> {{ __('messages.' . str_replace('-','_', $job->type)) }}
        </span>
        @endif

        @if($job->is_remote)
        <span class="job-tag remote">
            <i class="fas fa-wifi"></i> {{ __('messages.remote') }}
        </span>
        @endif

        @if($job->salary_min || $job->salary_max)
        <span class="job-tag salary">
            <i class="fas fa-dollar-sign"></i>
            @if($job->salary_min && $job->salary_max)
                {{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}
            @elseif($job->salary_min)
                {{ __('messages.from') }} {{ number_format($job->salary_min) }}
            @else
                {{ __('messages.up_to') }} {{ number_format($job->salary_max) }}
            @endif
            {{ $job->salary_currency ?? 'USD' }}
        </span>
        @endif
    </div>

    <!-- Description -->
    <p class="job-description">{{ $job->description }}</p>

    <!-- Required Skills -->
    @if($job->skills && count($job->skills) > 0)
    <div style="display:flex;flex-wrap:wrap;gap:.375rem;margin-bottom:1rem">
        @foreach(array_slice($job->skills, 0, 5) as $skill)
            <span style="padding:.2rem .6rem;background:var(--bg-hover);color:var(--text-secondary);border-radius:var(--radius-full);font-size:.75rem;font-weight:500">
                {{ $skill }}
            </span>
        @endforeach
        @if(count($job->skills) > 5)
            <span style="padding:.2rem .6rem;background:var(--bg-hover);color:var(--text-muted);border-radius:var(--radius-full);font-size:.75rem">
                +{{ count($job->skills) - 5 }}
            </span>
        @endif
    </div>
    @endif

    <!-- Card Footer -->
    <div class="job-card-footer">
        <span class="job-posted-date">
            <i class="fas fa-clock"></i>
            {{ $job->created_at->diffForHumans() }}
        </span>

        <div style="display:flex;gap:.5rem">
            @if($job->is_featured)
                <span class="status-badge active" style="font-size:.7rem">
                    ⭐ {{ __('messages.featured') }}
                </span>
            @endif

            @auth
            @if(auth()->user()->role === 'user')
                @if(auth()->user()->jobApplications->where('job_id', $job->id)->count() > 0)
                    <span class="btn btn-ghost btn-sm" style="cursor:default;color:var(--success)">
                        <i class="fas fa-check"></i> {{ __('messages.applied') }}
                    </span>
                @else
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-primary btn-sm">
                        {{ __('messages.apply_now') }}
                    </a>
                @endif
            @else
                <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline btn-sm">
                    {{ __('messages.view_details') }}
                </a>
            @endif
            @else
                <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline btn-sm">
                    {{ __('messages.view_details') }}
                </a>
            @endauth
        </div>
    </div>
</article>

@push('scripts')
<script>
function toggleSaveJob(btn, jobId) {
    fetch(`/jobs/${jobId}/save`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.saved) {
            btn.style.color = 'var(--primary)';
            btn.style.borderColor = 'var(--primary)';
            toastr.success(data.message);
        } else {
            btn.style.color = '';
            btn.style.borderColor = '';
            toastr.info(data.message);
        }
    });
}
</script>
@endpush