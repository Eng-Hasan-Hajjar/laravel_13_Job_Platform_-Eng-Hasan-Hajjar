@extends('layouts.app')
@section('title', __('messages.company_profile'))

@section('content')
<div class="page-container" style="max-width:800px">
    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:1.5rem">🏢 {{ __('messages.edit_company_profile') }}</h1>

    <form action="{{ route('company.profile.update') }}" method="POST" enctype="multipart/form-data" data-validate>
        @csrf @method('PATCH')

        <!-- Logo & Cover -->
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><span class="card-title">{{ __('messages.images') }}</span></div>
            <div class="card-body">
                <!-- Cover Upload -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.cover_image') }}</label>
                    <div class="upload-zone" style="height:120px">
                        <input type="file" name="cover_image" accept="image/*">
                        @if($company->cover_image)
                        <img src="{{ Storage::url($company->cover_image) }}" alt=""
                             style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:var(--radius-lg);opacity:.5">
                        @endif
                        <div style="position:relative;z-index:1;text-align:center">
                            <i class="fas fa-image" style="font-size:1.5rem;color:var(--text-muted);display:block;margin-bottom:.375rem"></i>
                            <span style="font-size:.8rem;color:var(--text-muted)">{{ __('messages.click_to_upload_cover') }}</span>
                        </div>
                    </div>
                </div>

                <!-- Logo -->
                <div class="form-group">
                    <label class="form-label">{{ __('messages.company_logo') }}</label>
                    <div style="display:flex;align-items:center;gap:1rem">
                        @if($company->logo)
                        <img src="{{ Storage::url($company->logo) }}" alt="{{ $company->name }}"
                             style="width:72px;height:72px;border-radius:var(--radius);object-fit:cover;border:2px solid var(--border)">
                        @else
                        <div class="avatar avatar-xl" style="border-radius:var(--radius)">{{ mb_strtoupper(mb_substr($company->name,0,2)) }}</div>
                        @endif
                        <div>
                            <input type="file" name="logo" accept="image/*" class="form-control" style="width:auto">
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.25rem">{{ __('messages.logo_hint') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Info -->
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><span class="card-title">{{ __('messages.basic_info') }}</span></div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.company_name') }} <span class="required">*</span></label>
                        <input type="text" name="name" class="form-control" required
                               value="{{ old('name', $company->name) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.industry') }} <span class="required">*</span></label>
                        <input type="text" name="industry" class="form-control" required
                               value="{{ old('industry', $company->industry) }}"
                               placeholder="{{ __('messages.industry_placeholder') }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">{{ __('messages.description') }}</label>
                    <textarea name="description" class="form-control" rows="5"
                              placeholder="{{ __('messages.company_desc_placeholder') }}">{{ old('description', $company->description) }}</textarea>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.location') }}</label>
                        <input type="text" name="location" class="form-control"
                               value="{{ old('location', $company->location) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.website') }}</label>
                        <input type="url" name="website" class="form-control"
                               value="{{ old('website', $company->website) }}" placeholder="https://...">
                    </div>
                </div>

                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.employees_count') }}</label>
                        <select name="employees_count" class="form-control">
                            @foreach(['1-10','11-50','51-200','201-500','501-1000','1000+'] as $range)
                            <option value="{{ $range }}" {{ old('employees_count', $company->employees_count) === $range ? 'selected' : '' }}>{{ $range }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.founded_year') }}</label>
                        <input type="number" name="founded_year" class="form-control"
                               value="{{ old('founded_year', $company->founded_year) }}"
                               min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="card" style="margin-bottom:1.25rem">
            <div class="card-header"><span class="card-title">{{ __('messages.contact_info') }}</span></div>
            <div class="card-body">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.email') }}</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $company->email) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{ __('messages.phone') }}</label>
                        <input type="tel" name="phone" class="form-control"
                               value="{{ old('phone', $company->phone) }}">
                    </div>
                </div>

                <div class="grid grid-3">
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-linkedin" style="color:#0077b5"></i> LinkedIn</label>
                        <input type="url" name="linkedin" class="form-control"
                               value="{{ old('linkedin', $company->linkedin) }}" placeholder="https://linkedin.com/company/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-twitter" style="color:#1da1f2"></i> Twitter</label>
                        <input type="url" name="twitter" class="form-control"
                               value="{{ old('twitter', $company->twitter) }}" placeholder="https://twitter.com/...">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><i class="fab fa-facebook" style="color:#1877f2"></i> Facebook</label>
                        <input type="url" name="facebook" class="form-control"
                               value="{{ old('facebook', $company->facebook) }}" placeholder="https://facebook.com/...">
                    </div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:.75rem">
            <a href="{{ route('company.dashboard') }}" class="btn btn-ghost">
                <i class="fas fa-arrow-left"></i> {{ __('messages.cancel') }}
            </a>
            <button type="submit" class="btn btn-primary" style="flex:1">
                <i class="fas fa-save"></i> {{ __('messages.save_changes') }}
            </button>
        </div>
    </form>
</div>
@endsection