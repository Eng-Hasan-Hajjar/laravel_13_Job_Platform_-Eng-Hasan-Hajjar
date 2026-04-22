@extends('layouts.app')
@section('title', __('messages.application_details'))

@section('content')
<div class="page-container" style="max-width:1000px">

    <!-- Back -->
    <a href="{{ route('company.applications.index') }}" class="btn btn-ghost btn-sm" style="margin-bottom:1.25rem">
        <i class="fas fa-arrow-left"></i> {{ __('messages.back_to_applications') }}
    </a>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

        <!-- LEFT: Application Details -->
        <div>

            <!-- Applicant Header Card -->
            <div class="card" style="margin-bottom:1.25rem;border-top:3px solid var(--primary)">
                <div class="card-body">
                    <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap">
                        <div class="avatar avatar-xl">{{ mb_strtoupper(mb_substr($application->user->name, 0, 2)) }}</div>
                        <div style="flex:1">
                            <h2 style="font-size:1.25rem;font-weight:800;margin-bottom:.25rem">{{ $application->user->name }}</h2>
                            <div style="display:flex;flex-wrap:wrap;gap:.625rem;font-size:.85rem;color:var(--text-secondary)">
                                <span><i class="fas fa-envelope" style="color:var(--primary)"></i> {{ $application->user->email }}</span>
                                @if($application->user->phone)
                                <span><i class="fas fa-phone" style="color:var(--primary)"></i> {{ $application->user->phone }}</span>
                                @endif
                                @if($application->user->location)
                                <span><i class="fas fa-map-marker-alt" style="color:var(--primary)"></i> {{ $application->user->location }}</span>
                                @endif
                            </div>
                            @if($application->user->bio)
                            <p style="font-size:.875rem;color:var(--text-secondary);margin-top:.625rem;line-height:1.6">
                                {{ $application->user->bio }}
                            </p>
                            @endif
                        </div>
                        <div style="text-align:center">
                            <span class="status-badge {{ $application->status }}" style="font-size:.85rem;padding:.5rem 1rem">
                                {{ __('messages.' . $application->status) }}
                            </span>
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.375rem">
                                {{ __('messages.applied') }}: {{ $application->created_at->format('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Application Info -->
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header"><span class="card-title">📋 {{ __('messages.application_info') }}</span></div>
                <div class="card-body">
                    <div class="grid grid-2" style="margin-bottom:1.25rem">
                        <div style="padding:.875rem;background:var(--bg-hover);border-radius:var(--radius)">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.25rem">{{ __('messages.applied_for') }}</div>
                            <div style="font-weight:700;font-size:.9rem">{{ $application->job->title }}</div>
                        </div>
                        <div style="padding:.875rem;background:var(--bg-hover);border-radius:var(--radius)">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.25rem">{{ __('messages.expected_salary') }}</div>
                            <div style="font-weight:700;font-size:.9rem">
                                {{ $application->expected_salary ? number_format($application->expected_salary) . ' USD' : '—' }}
                            </div>
                        </div>
                        <div style="padding:.875rem;background:var(--bg-hover);border-radius:var(--radius)">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.25rem">{{ __('messages.availability') }}</div>
                            <div style="font-weight:700;font-size:.9rem">{{ __('messages.' . $application->availability) }}</div>
                        </div>
                        <div style="padding:.875rem;background:var(--bg-hover);border-radius:var(--radius)">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.25rem">{{ __('messages.experience_level') }}</div>
                            <div style="font-weight:700;font-size:.9rem">
                                {{ $application->user->experience_level ? __('messages.exp_' . $application->user->experience_level) : '—' }}
                            </div>
                        </div>
                    </div>

                    <!-- Cover Letter -->
                    <div>
                        <div style="font-weight:700;font-size:.875rem;margin-bottom:.625rem;display:flex;align-items:center;gap:.5rem">
                            <i class="fas fa-file-alt" style="color:var(--primary)"></i>
                            {{ __('messages.cover_letter') }}
                        </div>
                        <div style="font-size:.875rem;color:var(--text-secondary);line-height:1.8;padding:1rem;background:var(--bg-hover);border-radius:var(--radius);border-left:3px solid var(--primary)">
                            {!! nl2br(e($application->cover_letter)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- CV Analysis -->
            @if($cvAnalysis && empty($cvAnalysis['error']))
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header">
                    <span class="card-title">🤖 {{ __('messages.cv_analysis') }}</span>
                    <div style="display:flex;align-items:center;gap:.75rem">
                        <!-- Score Badge -->
                        @php $score = $cvAnalysis['score'] ?? 0; @endphp
                        <div style="display:flex;align-items:center;gap:.375rem;padding:.375rem .875rem;border-radius:var(--radius-full);font-weight:800;font-size:.875rem;
                            background:{{ $score >= 70 ? 'rgba(16,185,129,.1)' : ($score >= 40 ? 'rgba(245,158,11,.1)' : 'rgba(239,68,68,.1)') }};
                            color:{{ $score >= 70 ? 'var(--success)' : ($score >= 40 ? 'var(--warning)' : 'var(--danger)') }}">
                            <i class="fas fa-chart-pie"></i> {{ $score }}/100
                        </div>
                        <a href="{{ route('company.applications.cv', $application) }}" class="btn btn-ghost btn-sm">
                            <i class="fas fa-download"></i> {{ __('messages.download_cv') }}
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="grid grid-2" style="margin-bottom:1.25rem">
                        <!-- Technical Skills -->
                        @if(!empty($cvAnalysis['technical_skills']))
                        <div>
                            <div style="font-weight:700;font-size:.8rem;margin-bottom:.5rem;color:var(--text-secondary)">
                                💻 {{ __('messages.technical_skills') }} ({{ count($cvAnalysis['technical_skills']) }})
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:.25rem">
                                @foreach($cvAnalysis['technical_skills'] as $sk)
                                <span style="padding:.2rem .6rem;background:rgba(37,99,235,.1);color:var(--primary);border-radius:var(--radius-full);font-size:.72rem;font-weight:600">{{ $sk }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Soft Skills -->
                        @if(!empty($cvAnalysis['soft_skills']))
                        <div>
                            <div style="font-weight:700;font-size:.8rem;margin-bottom:.5rem;color:var(--text-secondary)">
                                🤝 {{ __('messages.soft_skills') }} ({{ count($cvAnalysis['soft_skills']) }})
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:.25rem">
                                @foreach($cvAnalysis['soft_skills'] as $sk)
                                <span style="padding:.2rem .6rem;background:rgba(16,185,129,.1);color:var(--success);border-radius:var(--radius-full);font-size:.72rem;font-weight:600">{{ $sk }}</span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Misc Stats -->
                    <div style="display:flex;gap:1rem;flex-wrap:wrap">
                        @if(!empty($cvAnalysis['languages']))
                        <div style="padding:.75rem 1rem;background:var(--bg-hover);border-radius:var(--radius);flex:1">
                            <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.25rem">🌐 {{ __('messages.languages') }}</div>
                            <div style="font-size:.8rem;font-weight:600">{{ implode(', ', array_map('ucfirst', $cvAnalysis['languages'])) }}</div>
                        </div>
                        @endif
                        <div style="padding:.75rem 1rem;background:var(--bg-hover);border-radius:var(--radius);flex:1">
                            <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.25rem">📊 {{ __('messages.experience') }}</div>
                            <div style="font-size:1.125rem;font-weight:800;color:var(--primary)">{{ $cvAnalysis['experience_years'] ?? 0 }} {{ __('messages.years') }}</div>
                        </div>
                        @if(!empty($cvAnalysis['education']))
                        <div style="padding:.75rem 1rem;background:var(--bg-hover);border-radius:var(--radius);flex:2">
                            <div style="font-size:.7rem;color:var(--text-muted);margin-bottom:.25rem">🎓 {{ __('messages.education') }}</div>
                            <div style="font-size:.8rem;font-weight:600">{{ $cvAnalysis['education'][0] ?? '' }}</div>
                        </div>
                        @endif
                    </div>

                    <!-- Skill Match with Job -->
                    @php
                        $jobSkills = array_map('mb_strtolower', $application->job->skills ?? []);
                        $cvSkills  = array_map('mb_strtolower', $cvAnalysis['technical_skills'] ?? []);
                        $matched   = array_intersect($jobSkills, $cvSkills);
                        $matchPct  = count($jobSkills) > 0 ? round((count($matched) / count($jobSkills)) * 100) : 0;
                    @endphp
                    @if(count($jobSkills) > 0)
                    <div style="margin-top:1.25rem;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem">
                            <div style="font-weight:700;font-size:.85rem">🎯 {{ __('messages.skill_match_with_job') }}</div>
                            <span style="font-weight:800;font-size:1.125rem;color:{{ $matchPct >= 70 ? 'var(--success)' : ($matchPct >= 40 ? 'var(--warning)' : 'var(--danger)') }}">
                                {{ $matchPct }}%
                            </span>
                        </div>
                        <div class="progress" style="height:8px;margin-bottom:.75rem">
                            <div class="progress-bar" style="width:{{ $matchPct }}%;background:{{ $matchPct >= 70 ? 'var(--success)' : ($matchPct >= 40 ? 'var(--warning)' : 'var(--danger)') }}"></div>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:.25rem">
                            @foreach($jobSkills as $sk)
                            <span style="padding:.2rem .6rem;border-radius:var(--radius-full);font-size:.72rem;font-weight:600;
                                background:{{ in_array($sk, $cvSkills) ? 'rgba(16,185,129,.1)' : 'rgba(239,68,68,.08)' }};
                                color:{{ in_array($sk, $cvSkills) ? 'var(--success)' : 'var(--danger)' }};
                                border:1px solid {{ in_array($sk, $cvSkills) ? 'rgba(16,185,129,.3)' : 'rgba(239,68,68,.2)' }}">
                                {{ in_array($sk, $cvSkills) ? '✓' : '✗' }} {{ $sk }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- RIGHT: Status Panel -->
        <div style="position:sticky;top:calc(var(--navbar-height) + 1rem)">

            <!-- Update Status -->
            <div class="card" style="margin-bottom:1.25rem;border-top:3px solid var(--primary)">
                <div class="card-header"><span class="card-title">{{ __('messages.update_status') }}</span></div>
                <div class="card-body">
                    <form id="statusForm">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.new_status') }}</label>
                            <div style="display:flex;flex-direction:column;gap:.5rem">
                                @foreach(['pending','reviewed','shortlisted','accepted','rejected'] as $st)
                                <label style="display:flex;align-items:center;gap:.625rem;padding:.625rem .875rem;border:2px solid {{ $application->status === $st ? 'var(--primary)' : 'var(--border)' }};border-radius:var(--radius);cursor:pointer;transition:var(--transition)"
                                       onmouseover="this.style.borderColor='var(--primary)'"
                                       onmouseout="this.style.borderColor='{{ $application->status === $st ? 'var(--primary)' : 'var(--border)' }}'">
                                    <input type="radio" name="status" value="{{ $st }}"
                                           {{ $application->status === $st ? 'checked' : '' }}
                                           style="accent-color:var(--primary)">
                                    <span class="status-badge {{ $st }}" style="margin:0">{{ __('messages.' . $st) }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.note_to_applicant') }}</label>
                            <textarea name="admin_notes" class="form-control" rows="3"
                                      placeholder="{{ __('messages.note_placeholder') }}">{{ $application->admin_notes }}</textarea>
                        </div>
                        <button type="button" onclick="updateStatus()" class="btn btn-primary" style="width:100%">
                            <i class="fas fa-sync"></i> {{ __('messages.update_status') }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card">
                <div class="card-header"><span class="card-title">{{ __('messages.quick_actions') }}</span></div>
                <div class="card-body" style="display:flex;flex-direction:column;gap:.625rem">
                    <a href="{{ route('company.applications.cv', $application) }}" class="btn btn-outline">
                        <i class="fas fa-file-pdf" style="color:var(--danger)"></i> {{ __('messages.download_cv') }}
                    </a>
                    <a href="mailto:{{ $application->user->email }}" class="btn btn-ghost">
                        <i class="fas fa-envelope"></i> {{ __('messages.email_applicant') }}
                    </a>
                    <a href="{{ route('company.applications.index') }}" class="btn btn-ghost">
                        <i class="fas fa-list"></i> {{ __('messages.back_to_applications') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function updateStatus() {
    const form   = document.getElementById('statusForm');
    const status = form.querySelector('[name="status"]:checked')?.value;
    const notes  = form.querySelector('[name="admin_notes"]').value;

    if (!status) { toastr.warning('Please select a status'); return; }

    const btn = form.querySelector('button');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    try {
        const res = await fetch('{{ route("company.applications.status", $application) }}', {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status, admin_notes: notes })
        });
        const data = await res.json();
        if (data.success) {
            toastr.success('{{ __("messages.status_updated") }}');
            // Update displayed badge
            document.querySelector('.status-badge.{{ $application->status }}')?.remove();
        } else {
            toastr.error('{{ __("messages.error_occurred") }}');
        }
    } catch (e) {
        toastr.error('Network error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-sync"></i> {{ __("messages.update_status") }}';
    }
}
</script>
@endpush
@endsection