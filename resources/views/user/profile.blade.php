@extends('layouts.app')
@section('title', __('messages.my_profile'))

@section('content')
<div class="page-container">

    <!-- Profile Header -->
    <div class="card" style="margin-bottom:1.5rem;background:linear-gradient(135deg,#1e3a5f,#2563eb);border:none;color:white">
        <div class="card-body" style="padding:2rem">
            <div style="display:flex;align-items:flex-end;gap:1.5rem;flex-wrap:wrap">
                <!-- Avatar Upload -->
                <div style="position:relative;flex-shrink:0">
                    <form id="avatarForm" action="{{ route('user.avatar.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf @method('PATCH')
                        @if($user->avatar)
                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                                 style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.4)">
                        @else
                            <div class="avatar" style="width:100px;height:100px;font-size:2rem;border:3px solid rgba(255,255,255,.4)">
                                {{ mb_strtoupper(mb_substr($user->name,0,2)) }}
                            </div>
                        @endif
                        <label style="position:absolute;bottom:0;right:0;width:30px;height:30px;background:white;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:var(--shadow-md)">
                            <i class="fas fa-camera" style="color:var(--primary);font-size:.75rem"></i>
                            <input type="file" name="avatar" accept="image/*" style="display:none"
                                   onchange="document.getElementById('avatarForm').submit()">
                        </label>
                    </form>
                </div>

                <div style="flex:1;min-width:0">
                    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ $user->name }}</h1>
                    <div style="opacity:.85;font-size:.9rem;margin-bottom:.5rem">{{ $user->email }}</div>
                    @if($user->location)
                        <div style="opacity:.8;font-size:.85rem"><i class="fas fa-map-marker-alt"></i> {{ $user->location }}</div>
                    @endif
                </div>

                <!-- Profile Completeness -->
                <div style="text-align:center;background:rgba(255,255,255,.15);border-radius:var(--radius-lg);padding:1rem 1.5rem">
                    <div style="font-size:2rem;font-weight:800">{{ $completeness }}%</div>
                    <div style="font-size:.8rem;opacity:.8;margin-bottom:.5rem">{{ __('messages.profile_complete') }}</div>
                    <div class="progress" style="width:100px">
                        <div class="progress-bar" style="width:{{ $completeness }}%;background:white"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 320px;gap:1.5rem;align-items:start">

        <!-- LEFT: Forms -->
        <div>
            <!-- Tabs -->
            <div style="display:flex;gap:.25rem;margin-bottom:1.5rem;border-bottom:1px solid var(--border);overflow-x:auto">
                @foreach([
                    ['tab'=>'info',  'icon'=>'fa-user',     'label'=>__('messages.personal_info')],
                    ['tab'=>'cv',    'icon'=>'fa-file-pdf',  'label'=>__('messages.cv_resume')],
                    ['tab'=>'prefs', 'icon'=>'fa-sliders-h','label'=>__('messages.preferences')],
                    ['tab'=>'security','icon'=>'fa-lock',   'label'=>__('messages.security')],
                ] as $t)
                <button onclick="switchTab('{{ $t['tab'] }}')" id="tab-{{ $t['tab'] }}"
                        style="padding:.75rem 1.25rem;border:none;background:none;cursor:pointer;font-size:.875rem;font-weight:600;color:var(--text-muted);border-bottom:2px solid transparent;white-space:nowrap;transition:var(--transition)">
                    <i class="fas {{ $t['icon'] }}"></i> {{ $t['label'] }}
                </button>
                @endforeach
            </div>

            <!-- Personal Info Tab -->
            <div id="panel-info" class="tab-panel">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.personal_info') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.profile.update') }}" method="POST" data-validate>
                            @csrf @method('PATCH')
                            <div class="grid grid-2">
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.full_name') }} <span class="required">*</span></label>
                                    <input type="text" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')<div class="form-error">{{ $message }}</div>@enderror
                                </div>
                                <div class="form-group">
                                    <label class="form-label">{{ __('messages.phone') }}</label>
                                    <input type="tel" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.location') }}</label>
                                <input type="text" name="location" class="form-control" value="{{ old('location', $user->location) }}"
                                       placeholder="{{ __('messages.city_country') }}">
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.bio') }}</label>
                                <textarea name="bio" class="form-control" rows="4"
                                          placeholder="{{ __('messages.bio_placeholder') }}">{{ old('bio', $user->bio) }}</textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.experience_level') }}</label>
                                <select name="experience_level" class="form-control">
                                    @foreach(['entry','junior','mid','senior','lead'] as $level)
                                        <option value="{{ $level }}" {{ $user->experience_level === $level ? 'selected' : '' }}>
                                            {{ __('messages.exp_' . $level) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.skills') }}</label>
                                <input type="text" name="skills" class="form-control" id="skillsInput"
                                       value="{{ old('skills', implode(', ', $user->skills ?? [])) }}"
                                       placeholder="{{ __('messages.skills_placeholder') }}">
                                <div style="font-size:.75rem;color:var(--text-muted);margin-top:.25rem">
                                    {{ __('messages.skills_hint') }}
                                </div>
                                <!-- Skill Tags Preview -->
                                <div id="skillTags" style="display:flex;flex-wrap:wrap;gap:.375rem;margin-top:.5rem">
                                    @foreach($user->skills ?? [] as $skill)
                                    <span style="padding:.25rem .75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem">
                                        {{ $skill }}
                                    </span>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.save_changes') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- CV Tab -->
            <div id="panel-cv" class="tab-panel" style="display:none">
                <div class="card" style="margin-bottom:1.25rem">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.cv_resume') }}</span>
                    </div>
                    <div class="card-body">
                        @if($user->cv_path)
                        <div style="display:flex;align-items:center;gap:1rem;padding:1rem;background:var(--bg-hover);border-radius:var(--radius);margin-bottom:1.25rem;border:1px solid var(--border)">
                            <div style="width:48px;height:48px;background:rgba(239,68,68,.1);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;color:var(--danger);font-size:1.375rem;flex-shrink:0">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div style="flex:1;min-width:0">
                                <div style="font-weight:600;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    {{ basename($user->cv_path) }}
                                </div>
                                <div style="font-size:.75rem;color:var(--text-muted)">
                                    {{ __('messages.uploaded') }}: {{ auth()->user()->updated_at->format('d M Y') }}
                                </div>
                            </div>
                            <div style="display:flex;gap:.5rem">
                                <a href="{{ route('user.cv.download') }}" class="btn btn-ghost btn-sm">
                                    <i class="fas fa-download"></i>
                                </a>
                                <form action="{{ route('user.cv.delete') }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                                            data-confirm-delete="{{ __('messages.delete_cv_confirm') }}" data-form-id="deleteCvForm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endif

                        <form action="{{ route('user.cv.upload') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="upload-zone">
                                <input type="file" name="cv_file" accept=".pdf,.doc,.docx">
                                <i class="fas fa-cloud-upload-alt" style="font-size:2.5rem;color:var(--text-muted);margin-bottom:.75rem;display:block"></i>
                                <div style="font-weight:700;margin-bottom:.375rem">{{ __('messages.drop_cv_or_click') }}</div>
                                <div style="font-size:.8rem;color:var(--text-muted)">PDF, DOC, DOCX • Max 5MB</div>
                                <div class="upload-info" style="margin-top:.75rem"></div>
                            </div>
                            @error('cv_file')<div class="form-error" style="margin-top:.5rem">{{ $message }}</div>@enderror
                            <button type="submit" class="btn btn-primary" style="margin-top:1rem;width:100%">
                                <i class="fas fa-upload"></i> {{ __('messages.upload_cv') }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- CV Analysis Results -->
                @if($user->cv_analyzed)
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">🤖 {{ __('messages.cv_analysis') }}</span>
                        <span class="status-badge active">{{ __('messages.analyzed') }}</span>
                    </div>
                    <div class="card-body">
                        @php $analysis = $user->cv_analyzed; @endphp

                        <!-- Score -->
                        <div style="text-align:center;margin-bottom:1.5rem">
                            <div style="position:relative;width:100px;height:100px;margin:0 auto .75rem">
                                <svg viewBox="0 0 36 36" style="width:100px;height:100px;transform:rotate(-90deg)">
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                          fill="none" stroke="var(--border)" stroke-width="3"/>
                                    <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"
                                          fill="none" stroke="var(--primary)" stroke-width="3"
                                          stroke-dasharray="{{ $analysis['score'] ?? 0 }}, 100"
                                          stroke-linecap="round"/>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;flex-direction:column">
                                    <span style="font-size:1.375rem;font-weight:800">{{ $analysis['score'] ?? 0 }}</span>
                                    <span style="font-size:.6rem;color:var(--text-muted)">/100</span>
                                </div>
                            </div>
                            <div style="font-weight:700">{{ __('messages.cv_score') }}</div>
                        </div>

                        <!-- Technical Skills -->
                        @if(!empty($analysis['technical_skills']))
                        <div style="margin-bottom:1.25rem">
                            <div style="font-weight:700;font-size:.875rem;margin-bottom:.625rem">
                                💻 {{ __('messages.technical_skills') }} ({{ count($analysis['technical_skills']) }})
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:.375rem">
                                @foreach($analysis['technical_skills'] as $skill)
                                <span style="padding:.25rem .75rem;background:rgba(37,99,235,.1);color:var(--primary);border-radius:var(--radius-full);font-size:.75rem;font-weight:600;border:1px solid rgba(37,99,235,.2)">
                                    {{ $skill }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Soft Skills -->
                        @if(!empty($analysis['soft_skills']))
                        <div style="margin-bottom:1.25rem">
                            <div style="font-weight:700;font-size:.875rem;margin-bottom:.625rem">
                                🤝 {{ __('messages.soft_skills') }} ({{ count($analysis['soft_skills']) }})
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:.375rem">
                                @foreach($analysis['soft_skills'] as $skill)
                                <span style="padding:.25rem .75rem;background:rgba(16,185,129,.1);color:var(--success);border-radius:var(--radius-full);font-size:.75rem;font-weight:600;border:1px solid rgba(16,185,129,.2)">
                                    {{ $skill }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <!-- Languages & Experience -->
                        <div class="grid grid-2">
                            @if(!empty($analysis['languages']))
                            <div style="padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                                <div style="font-weight:700;font-size:.8rem;margin-bottom:.5rem">🌐 {{ __('messages.languages') }}</div>
                                @foreach($analysis['languages'] as $lang)
                                <div style="font-size:.8rem;color:var(--text-secondary);padding:.2rem 0">{{ ucfirst($lang) }}</div>
                                @endforeach
                            </div>
                            @endif
                            <div style="padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                                <div style="font-weight:700;font-size:.8rem;margin-bottom:.5rem">📊 {{ __('messages.experience') }}</div>
                                <div style="font-size:1.25rem;font-weight:800;color:var(--primary)">
                                    {{ $analysis['experience_years'] ?? 0 }}
                                </div>
                                <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.years') }}</div>
                            </div>
                        </div>

                        <!-- Education -->
                        @if(!empty($analysis['education']))
                        <div style="margin-top:1.25rem">
                            <div style="font-weight:700;font-size:.875rem;margin-bottom:.625rem">🎓 {{ __('messages.education') }}</div>
                            @foreach($analysis['education'] as $edu)
                            <div style="font-size:.8rem;color:var(--text-secondary);padding:.375rem 0;border-bottom:1px solid var(--border)">
                                {{ $edu }}
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <div style="margin-top:1.25rem;padding:.75rem;background:var(--bg-hover);border-radius:var(--radius);font-size:.75rem;color:var(--text-muted)">
                            <i class="fas fa-clock"></i>
                            {{ __('messages.analyzed_at') }}: {{ \Carbon\Carbon::parse($analysis['analyzed_at'])->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Preferences Tab -->
            <div id="panel-prefs" class="tab-panel" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.job_preferences') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.preferences.update') }}" method="POST" data-validate>
                            @csrf @method('PATCH')
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.preferred_job_types') }}</label>
                                <div style="display:flex;flex-wrap:wrap;gap:.5rem">
                                    @foreach(['full-time','part-time','freelance','remote','internship'] as $type)
                                    <label style="display:flex;align-items:center;gap:.5rem;padding:.5rem .875rem;border:1px solid var(--border);border-radius:var(--radius-full);cursor:pointer;font-size:.8rem;transition:var(--transition)"
                                           id="type-label-{{ $type }}">
                                        <input type="checkbox" name="preferred_job_types[]" value="{{ $type }}"
                                               {{ in_array($type, $user->preferred_job_types ?? []) ? 'checked' : '' }}
                                               style="accent-color:var(--primary)"
                                               onchange="updateCheckboxStyle(this)">
                                        {{ __('messages.' . str_replace('-','_', $type)) }}
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.preferred_locations') }}</label>
                                <input type="text" name="preferred_locations" class="form-control"
                                       value="{{ old('preferred_locations', implode(', ', $user->preferred_locations ?? [])) }}"
                                       placeholder="{{ __('messages.locations_placeholder') }}">
                                <div style="font-size:.75rem;color:var(--text-muted);margin-top:.25rem">{{ __('messages.comma_separated') }}</div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.expected_salary') }}</label>
                                <div class="input-group">
                                    <input type="number" name="expected_salary" class="form-control"
                                           value="{{ old('expected_salary', $user->expected_salary) }}" min="0">
                                    <span class="input-group-text">USD</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.language_preference') }}</label>
                                <select name="locale" class="form-control">
                                    <option value="en" {{ $user->locale === 'en' ? 'selected' : '' }}>English</option>
                                    <option value="ar" {{ $user->locale === 'ar' ? 'selected' : '' }}>العربية</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('messages.save_preferences') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="panel-security" class="tab-panel" style="display:none">
                <div class="card">
                    <div class="card-header">
                        <span class="card-title">{{ __('messages.change_password') }}</span>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.password.update') }}" method="POST" data-validate>
                            @csrf @method('PATCH')
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.current_password') }} <span class="required">*</span></label>
                                <input type="password" name="current_password" class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}" required>
                                @error('current_password')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.new_password') }} <span class="required">*</span></label>
                                <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" required>
                                @error('password')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.confirm_password') }} <span class="required">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-lock"></i> {{ __('messages.update_password') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT: Stats -->
        <div>
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header"><span class="card-title">{{ __('messages.activity') }}</span></div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:.875rem">
                        @foreach([
                            ['icon'=>'fa-file-alt','color'=>'primary','label'=>__('messages.total_applications'),'value'=>$stats['applications']],
                            ['icon'=>'fa-check-circle','color'=>'success','label'=>__('messages.accepted'),'value'=>$stats['accepted']],
                            ['icon'=>'fa-bookmark','color'=>'warning','label'=>__('messages.saved_jobs'),'value'=>$stats['saved']],
                            ['icon'=>'fa-eye','color'=>'info','label'=>__('messages.profile_views'),'value'=>$stats['views'] ?? 0],
                        ] as $stat)
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div style="width:36px;height:36px;background:rgba(37,99,235,.1);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:.875rem;color:var(--{{ $stat['color'] }})">
                                <i class="fas {{ $stat['icon'] }}"></i>
                            </div>
                            <div style="flex:1">
                                <div style="font-size:.8rem;color:var(--text-muted)">{{ $stat['label'] }}</div>
                            </div>
                            <span style="font-weight:800;font-size:1.125rem">{{ $stat['value'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recommended Jobs Quick -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">🤖 {{ __('messages.for_you') }}</span>
                    <a href="{{ route('jobs.recommended') }}" style="font-size:.75rem;color:var(--primary)">{{ __('messages.view_all') }}</a>
                </div>
                <div class="card-body" style="padding:0">
                    @foreach($recommendedJobs->take(4) as $job)
                    <a href="{{ route('jobs.show', $job) }}"
                       style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1rem;border-bottom:1px solid var(--border);text-decoration:none;transition:var(--transition)"
                       onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''">
                        <div class="avatar avatar-sm" style="border-radius:var(--radius-sm);flex-shrink:0">
                            {{ mb_strtoupper(mb_substr($job->company->name,0,2)) }}
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="font-size:.8rem;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $job->title }}</div>
                            <div style="font-size:.7rem;color:var(--text-muted)">{{ $job->company->name }}</div>
                        </div>
                        @if($job->recommendation_score > 0)
                        <span style="font-size:.65rem;background:var(--primary-light);color:var(--primary);padding:.15rem .4rem;border-radius:var(--radius-full);font-weight:700;white-space:nowrap">
                            {{ $job->recommendation_score }}%
                        </span>
                        @endif
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function switchTab(name) {
    document.querySelectorAll('.tab-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('[id^="tab-"]').forEach(t => {
        t.style.color = 'var(--text-muted)';
        t.style.borderBottomColor = 'transparent';
    });
    document.getElementById('panel-' + name).style.display = 'block';
    const btn = document.getElementById('tab-' + name);
    btn.style.color = 'var(--primary)';
    btn.style.borderBottomColor = 'var(--primary)';
}
// Activate first tab
switchTab('info');

// Skills live preview
document.getElementById('skillsInput')?.addEventListener('input', function() {
    const skills = this.value.split(',').map(s => s.trim()).filter(Boolean);
    const container = document.getElementById('skillTags');
    container.innerHTML = skills.map(s => `<span style="padding:.25rem .75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem">${s}</span>`).join('');
});
</script>
@endpush
@endsection