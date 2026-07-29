@extends('layouts.app')
@section('title', isset($job) ? __('messages.edit_job') : __('messages.post_new_job'))

@section('content')
<div class="page-container" style="max-width:900px">

    <!-- Breadcrumb -->
    <div style="display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:var(--text-muted);margin-bottom:1.5rem">
        <a href="{{ route('company.dashboard') }}" style="color:var(--primary);text-decoration:none">{{ __('messages.dashboard') }}</a>
        <i class="fas fa-chevron-right" style="font-size:.65rem"></i>
        <a href="{{ route('company.jobs.index') }}" style="color:var(--primary);text-decoration:none">{{ __('messages.my_jobs') }}</a>
        <i class="fas fa-chevron-right" style="font-size:.65rem"></i>
        <span>{{ isset($job) ? __('messages.edit_job') : __('messages.post_new_job') }}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 280px;gap:1.5rem;align-items:start">

        <!-- Main Form -->
        <div>
            <form action="{{ isset($job) ? route('company.jobs.update', $job) : route('company.jobs.store') }}"
                  method="POST" data-validate id="jobForm">
                @csrf
                @if(isset($job)) @method('PATCH') @endif

                <!-- Basic Info -->
                <div class="card" style="margin-bottom:1.25rem">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-briefcase" style="color:var(--primary);margin-right:.5rem"></i>
                            {{ __('messages.basic_info') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.job_title') }} <span class="required">*</span></label>
                            <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                                   value="{{ old('title', $job->title ?? '') }}"
                                   placeholder="{{ __('messages.job_title_placeholder') }}" required minlength="5" maxlength="200">
                            @error('title')<div class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>@enderror
                        </div>

                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.category') }} <span class="required">*</span></label>
                                <select name="category_id" class="form-control {{ $errors->has('category_id') ? 'is-invalid' : '' }}" required>
                                    <option value="">{{ __('messages.select_category') }}</option>
                                    @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id', $job->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->icon }} {{ $cat->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.job_type') }} <span class="required">*</span></label>
                                <select name="type" class="form-control" required>
                                    @foreach(['full-time','part-time','freelance','remote','internship'] as $type)
                                    <option value="{{ $type }}" {{ old('type', $job->type ?? 'full-time') === $type ? 'selected' : '' }}>
                                        {{ __('messages.' . str_replace('-','_', $type)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.location') }} <span class="required">*</span></label>
                                <input type="text" name="location" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}"
                                       value="{{ old('location', $job->location ?? '') }}"
                                       placeholder="{{ __('messages.location_placeholder') }}" required>
                                @error('location')<div class="form-error">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.experience_level') }} <span class="required">*</span></label>
                                <select name="experience_level" class="form-control" required>
                                    @foreach(['entry','junior','mid','senior','lead'] as $level)
                                    <option value="{{ $level }}" {{ old('experience_level', $job->experience_level ?? 'mid') === $level ? 'selected' : '' }}>
                                        {{ __('messages.exp_' . $level) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Remote Toggle -->
                        <div class="form-group">
                            <label style="display:flex;align-items:center;gap:.75rem;cursor:pointer;user-select:none">
                                <div style="position:relative;width:44px;height:24px">
                                    <input type="checkbox" name="is_remote" id="remoteToggle" value="1"
                                           {{ old('is_remote', $job->is_remote ?? false) ? 'checked' : '' }}
                                           style="opacity:0;position:absolute;inset:0;cursor:pointer;z-index:1"
                                           onchange="updateToggleUI(this, 'remoteBar', 'remoteThumb')">
                                    <div id="remoteBar" style="position:absolute;inset:0;border-radius:12px;transition:var(--transition);background:{{ old('is_remote', $job->is_remote ?? false) ? 'var(--success)' : 'var(--border)' }}"></div>
                                    <div id="remoteThumb" style="position:absolute;top:2px;width:20px;height:20px;border-radius:50%;background:white;transition:var(--transition);left:{{ old('is_remote', $job->is_remote ?? false) ? '22px' : '2px' }}"></div>
                                </div>
                                <div>
                                    <div style="font-weight:600;font-size:.875rem">{{ __('messages.remote_work') }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ __('messages.remote_work_desc') }}</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card" style="margin-bottom:1.25rem">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-align-left" style="color:var(--primary);margin-right:.5rem"></i>
                            {{ __('messages.job_details') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="form-label">{{ __('messages.job_description') }} <span class="required">*</span></label>
                            <textarea name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                      rows="8" required minlength="100"
                                      placeholder="{{ __('messages.description_placeholder') }}">{{ old('description', $job->description ?? '') }}</textarea>
                            <div style="display:flex;justify-content:flex-end;margin-top:.25rem">
                                <span id="descCount" style="font-size:.75rem;color:var(--text-muted)">0 {{ __('messages.characters') }}</span>
                            </div>
                            @error('description')<div class="form-error">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('messages.requirements') }}</label>
                            <textarea name="requirements" class="form-control" rows="5"
                                      placeholder="{{ __('messages.requirements_placeholder') }}">{{ old('requirements', $job->requirements ?? '') }}</textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">{{ __('messages.benefits') }}</label>
                            <textarea name="benefits" class="form-control" rows="4"
                                      placeholder="{{ __('messages.benefits_placeholder') }}">{{ old('benefits', $job->benefits ?? '') }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Skills -->
                <div class="card" style="margin-bottom:1.25rem">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-tags" style="color:var(--primary);margin-right:.5rem"></i>
                            {{ __('messages.required_skills') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="form-group" style="margin-bottom:0">
                            <input type="text" name="skills" id="skillsInput" class="form-control"
                                   value="{{ old('skills', isset($job) ? implode(', ', $job->skills ?? []) : '') }}"
                                   placeholder="{{ __('messages.skills_input_placeholder') }}">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.375rem">
                                {{ __('messages.skills_hint') }}
                            </div>
                            <!-- Skills Preview -->
                            <div id="skillsPreview" style="display:flex;flex-wrap:wrap;gap:.375rem;margin-top:.75rem">
                                @foreach(isset($job) ? ($job->skills ?? []) : [] as $skill)
                                <span class="skill-tag" style="padding:.25rem .75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem;font-weight:600">
                                    {{ $skill }} <span onclick="removeSkill(this)" style="cursor:pointer;margin-left:.25rem;opacity:.7">×</span>
                                </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary & Deadline -->
                <div class="card" style="margin-bottom:1.5rem">
                    <div class="card-header">
                        <span class="card-title">
                            <i class="fas fa-dollar-sign" style="color:var(--primary);margin-right:.5rem"></i>
                            {{ __('messages.salary_deadline') }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.min_salary') }}</label>
                                <div class="input-group">
                                    <input type="number" name="salary_min" class="form-control"
                                           value="{{ old('salary_min', $job->salary_min ?? '') }}" min="0" step="100"
                                           placeholder="0">
                                    <select name="salary_currency" class="input-group-text form-control" style="border-left:none;width:80px">
                                        @foreach(['USD','EUR','SAR','AED','EGP','SYP'] as $cur)
                                        <option value="{{ $cur }}" {{ old('salary_currency', $job->salary_currency ?? 'USD') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.max_salary') }}</label>
                                <input type="number" name="salary_max" class="form-control"
                                       value="{{ old('salary_max', $job->salary_max ?? '') }}" min="0" step="100"
                                       placeholder="0">
                            </div>
                        </div>

                        <div class="grid grid-2">
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.salary_period') }}</label>
                                <select name="salary_period" class="form-control">
                                    @foreach(['monthly','hourly','yearly'] as $p)
                                    <option value="{{ $p }}" {{ old('salary_period', $job->salary_period ?? 'monthly') === $p ? 'selected' : '' }}>
                                        {{ __('messages.period_' . $p) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">{{ __('messages.application_deadline') }}</label>
                                <input type="date" name="deadline" class="form-control"
                                       value="{{ old('deadline', isset($job) && $job->deadline ? $job->deadline->format('Y-m-d') : '') }}"
                                       min="{{ now()->addDay()->format('Y-m-d') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div style="display:flex;gap:.75rem">
                    <a href="{{ route('company.jobs.index') }}" class="btn btn-ghost">
                        <i class="fas fa-arrow-left"></i> {{ __('messages.cancel') }}
                    </a>
                    @if(isset($job))
                    <button type="submit" name="action" value="save" class="btn btn-ghost" style="flex:1">
                        <i class="fas fa-save"></i> {{ __('messages.save_draft') }}
                    </button>
                    @endif
                    <button type="submit" name="action" value="publish" class="btn btn-primary" style="flex:2">
                        <i class="fas fa-{{ isset($job) ? 'sync' : 'paper-plane' }}"></i>
                        {{ isset($job) ? __('messages.update_job') : __('messages.publish_job') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Sidebar Tips -->
        <div style="position:sticky;top:calc(var(--navbar-height) + 1rem)">
            <div class="card" style="margin-bottom:1.25rem">
                <div class="card-header"><span class="card-title">💡 {{ __('messages.tips') }}</span></div>
                <div class="card-body" style="padding:1rem">
                    <div style="display:flex;flex-direction:column;gap:.875rem">
                        @foreach([
                            ['icon'=>'fa-heading','tip'=>__('messages.tip_title')],
                            ['icon'=>'fa-align-left','tip'=>__('messages.tip_description')],
                            ['icon'=>'fa-tags','tip'=>__('messages.tip_skills')],
                            ['icon'=>'fa-dollar-sign','tip'=>__('messages.tip_salary')],
                            ['icon'=>'fa-calendar','tip'=>__('messages.tip_deadline')],
                        ] as $tip)
                        <div style="display:flex;gap:.625rem;align-items:flex-start">
                            <div style="width:28px;height:28px;border-radius:var(--radius-sm);background:var(--primary-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:var(--primary);font-size:.75rem">
                                <i class="fas {{ $tip['icon'] }}"></i>
                            </div>
                            <p style="font-size:.8rem;color:var(--text-secondary);line-height:1.5;margin:0">{{ $tip['tip'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Job Preview Card -->
            <div class="card">
                <div class="card-header"><span class="card-title">👁 {{ __('messages.preview') }}</span></div>
                <div class="card-body" style="padding:1rem">
                    <div style="font-weight:700;font-size:.875rem;margin-bottom:.25rem" id="previewTitle">{{ isset($job) ? $job->title : __('messages.job_title') }}</div>
                    <div style="font-size:.75rem;color:var(--primary);margin-bottom:.5rem">{{ auth()->user()->company->name }}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.375rem" id="previewTags">
                        <span class="job-tag type" style="font-size:.7rem" id="previewType">Full-time</span>
                        <span class="job-tag location" style="font-size:.7rem" id="previewLocation">{{ auth()->user()->company->location }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Live preview
document.querySelector('[name="title"]')?.addEventListener('input', e => {
    document.getElementById('previewTitle').textContent = e.target.value || '{{ __("messages.job_title") }}';
});
document.querySelector('[name="type"]')?.addEventListener('change', e => {
    document.getElementById('previewType').textContent = e.target.options[e.target.selectedIndex].text;
});
document.querySelector('[name="location"]')?.addEventListener('input', e => {
    document.getElementById('previewLocation').textContent = e.target.value;
});

// Character counter
const desc = document.querySelector('[name="description"]');
const count = document.getElementById('descCount');
if (desc && count) {
    const update = () => count.textContent = desc.value.length + ' {{ __("messages.characters") }}';
    desc.addEventListener('input', update);
    update();
}

// Skills live preview
document.getElementById('skillsInput')?.addEventListener('input', function() {
    const skills = this.value.split(',').map(s => s.trim()).filter(Boolean);
    const container = document.getElementById('skillsPreview');
    container.innerHTML = skills.map(s =>
        `<span style="padding:.25rem .75rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.8rem;font-weight:600">${s}</span>`
    ).join('');
});

// Toggle UI helper
function updateToggleUI(input, barId, thumbId) {
    const bar   = document.getElementById(barId);
    const thumb = document.getElementById(thumbId);
    if (input.checked) {
        bar.style.background = 'var(--success)';
        thumb.style.left = '22px';
    } else {
        bar.style.background = 'var(--border)';
        thumb.style.left = '2px';
    }
}
</script>
@endpush
@endsection