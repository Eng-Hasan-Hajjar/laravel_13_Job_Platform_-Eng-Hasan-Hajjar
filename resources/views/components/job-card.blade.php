{{-- resources/views/components/job-card.blade.php --}}
@php
    $jobSkills = is_array($job->skills) ? $job->skills : [];
@endphp

<div class="card" style="transition:var(--transition)">
    <div class="card-body">
        <div style="display:flex;align-items:flex-start;gap:1rem;flex-wrap:wrap">
            {{-- Logo --}}
            @if($job->company->logo)
                <img src="{{ Storage::url($job->company->logo) }}" alt="{{ $job->company->name }}"
                     style="width:48px;height:48px;border-radius:var(--radius);object-fit:cover;border:1px solid var(--border);flex-shrink:0">
            @else
                <div class="avatar" style="width:48px;height:48px;border-radius:var(--radius);flex-shrink:0;font-size:.875rem">
                    {{ mb_strtoupper(mb_substr($job->company->name, 0, 2)) }}
                </div>
            @endif

            {{-- Info --}}
            <div style="flex:1;min-width:0;overflow:hidden">
                <h3 style="font-size:.95rem;font-weight:700;margin-bottom:.2rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                    <a href="{{ route('jobs.show', $job) }}" style="text-decoration:none;color:var(--text-primary)">
                        {{ $job->title }}
                    </a>
                </h3>
                <div style="font-size:.8rem;color:var(--primary);font-weight:600;margin-bottom:.5rem">
                    {{ $job->company->name }}
                </div>

                {{-- Tags --}}
                <div style="display:flex;flex-wrap:wrap;gap:.3rem;margin-bottom:.5rem">
                    @if($job->location)
                    <span class="job-tag location" style="font-size:.7rem"><i class="fas fa-map-marker-alt"></i> {{ $job->location }}</span>
                    @endif
                    <span class="job-tag type" style="font-size:.7rem">
                        <i class="fas fa-clock"></i> {{ __('messages.' . str_replace('-','_', $job->type)) }}
                    </span>
                    @if($job->is_remote)
                    <span class="job-tag remote" style="font-size:.7rem"><i class="fas fa-wifi"></i> {{ __('messages.remote') }}</span>
                    @endif
                    @if($job->salary_min || $job->salary_max)
                    <span class="job-tag salary" style="font-size:.7rem">
                        <i class="fas fa-dollar-sign"></i>
                        @if($job->salary_min && $job->salary_max)
                            {{ number_format($job->salary_min) }} - {{ number_format($job->salary_max) }}
                        @elseif($job->salary_min)
                            {{ number_format($job->salary_min) }}+
                        @else
                            {{ __('messages.up_to') }} {{ number_format($job->salary_max) }}
                        @endif
                        {{ $job->salary_currency ?? 'USD' }}
                    </span>
                    @endif
                </div>

                {{-- Description snippet --}}
                @if($job->description)
                <p style="font-size:.8rem;color:var(--text-secondary);line-height:1.5;margin-bottom:.5rem;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;word-break:break-word">
                    {{ Str::limit(strip_tags($job->description), 120) }}
                </p>
                @endif

                {{-- Skills --}}
                @if(!empty($jobSkills))
                <div style="display:flex;flex-wrap:wrap;gap:.25rem;margin-bottom:.5rem">
                    @foreach(array_slice($jobSkills, 0, 5) as $skill)
                    <span style="font-size:.65rem;padding:.15rem .5rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-weight:600">
                        {{ $skill }}
                    </span>
                    @endforeach
                    @if(count($jobSkills) > 5)
                    <span style="font-size:.65rem;padding:.15rem .5rem;background:var(--bg-hover);color:var(--text-muted);border-radius:var(--radius-full)">
                        +{{ count($jobSkills) - 5 }}
                    </span>
                    @endif
                </div>
                @endif

                {{-- Footer --}}
                <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem">
                    <span style="font-size:.75rem;color:var(--text-muted)">
                        <i class="fas fa-clock"></i> {{ $job->created_at->diffForHumans() }}
                    </span>
                    <a href="{{ route('jobs.show', $job) }}" class="btn btn-outline btn-sm" style="font-size:.75rem">
                        {{ __('messages.view_details') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>