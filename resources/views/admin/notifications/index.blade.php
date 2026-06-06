@extends('layouts.app')
@section('title', __('messages.broadcast_notifications'))

@section('content')
<div class="page-container" style="max-width:900px">

    <div style="margin-bottom:1.5rem">
        <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
            <i class="fas fa-bullhorn" style="color:var(--primary)"></i> {{ __('messages.broadcast_notifications') }}
        </h1>
        <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.broadcast_notifications_desc') }}</p>
    </div>

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.25rem">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">

        <!-- Send Form -->
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('messages.send_notification') }}</span></div>
            <div class="card-body">
                <form action="{{ route('admin.notifications.send') }}" method="POST" data-validate>
                    @csrf

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.target_audience') }} <span class="required">*</span></label>
                        <div style="display:flex;flex-direction:column;gap:.5rem">
                            @foreach([
                                ['value'=>'all',       'icon'=>'fa-globe',   'label'=>__('messages.all_users')],
                                ['value'=>'users',     'icon'=>'fa-user',    'label'=>__('messages.job_seekers_only')],
                                ['value'=>'companies', 'icon'=>'fa-building','label'=>__('messages.companies_only')],
                            ] as $t)
                            <label style="display:flex;align-items:center;gap:.75rem;padding:.75rem;border:1px solid var(--border);border-radius:var(--radius);cursor:pointer;transition:var(--transition)">
                                <input type="radio" name="target" value="{{ $t['value'] }}"
                                       {{ old('target','all') === $t['value'] ? 'checked' : '' }}
                                       style="accent-color:var(--primary)">
                                <i class="fas {{ $t['icon'] }}" style="color:var(--primary);width:18px"></i>
                                <span style="font-size:.875rem;font-weight:500">{{ $t['label'] }}</span>
                            </label>
                            @endforeach
                        </div>
                        @error('target')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.title') }} <span class="required">*</span></label>
                        <input type="text" name="title" class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               value="{{ old('title') }}" required maxlength="100"
                               placeholder="{{ __('messages.notification_title_placeholder') }}">
                        @error('title')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.message') }} <span class="required">*</span></label>
                        <textarea name="body" class="form-control {{ $errors->has('body') ? 'is-invalid' : '' }}"
                                  rows="4" required maxlength="500"
                                  placeholder="{{ __('messages.notification_body_placeholder') }}">{{ old('body') }}</textarea>
                        @error('body')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">{{ __('messages.action_url') }} <span style="color:var(--text-muted);font-size:.75rem">({{ __('messages.optional') }})</span></label>
                        <input type="url" name="action_url" class="form-control"
                               value="{{ old('action_url') }}"
                               placeholder="https://example.com/page">
                        @error('action_url')<div class="form-error">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%">
                        <i class="fas fa-paper-plane"></i> {{ __('messages.send_to_all') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Info -->
        <div>
            <div class="card" style="background:linear-gradient(135deg,#10b981,#059669);border:none;color:white;margin-bottom:1rem">
                <div class="card-body" style="padding:1.5rem">
                    <div style="font-size:2.5rem;margin-bottom:.5rem">📢</div>
                    <h3 style="font-size:1.125rem;font-weight:800;margin-bottom:.5rem">{{ __('messages.reach_everyone') }}</h3>
                    <p style="opacity:.9;font-size:.875rem;line-height:1.6">{{ __('messages.broadcast_info') }}</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><span class="card-title">{{ __('messages.guidelines') }}</span></div>
                <div class="card-body">
                    <div style="display:flex;flex-direction:column;gap:.875rem">
                        @foreach([
                            ['icon'=>'fa-check-circle', 'color'=>'success', 'text'=>__('messages.guideline_1')],
                            ['icon'=>'fa-check-circle', 'color'=>'success', 'text'=>__('messages.guideline_2')],
                            ['icon'=>'fa-check-circle', 'color'=>'success', 'text'=>__('messages.guideline_3')],
                            ['icon'=>'fa-exclamation-triangle', 'color'=>'warning', 'text'=>__('messages.guideline_4')],
                        ] as $g)
                        <div style="display:flex;gap:.75rem;align-items:flex-start">
                            <i class="fas {{ $g['icon'] }}" style="color:var(--{{ $g['color'] }});margin-top:.2rem"></i>
                            <span style="font-size:.8rem;line-height:1.5">{{ $g['text'] }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection