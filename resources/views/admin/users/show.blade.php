@extends('layouts.app')
@section('title', $user->name)

@section('content')
<div class="page-container">

    <!-- Back Link -->
    <a href="{{ route('admin.users.index') }}" style="display:inline-flex;align-items:center;gap:.5rem;color:var(--text-muted);font-size:.875rem;margin-bottom:1rem;text-decoration:none">
        <i class="fas fa-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}"></i> {{ __('messages.back_to_users') }}
    </a>

    <!-- Profile Header Card -->
    <div class="card" style="margin-bottom:1.5rem;background:linear-gradient(135deg,#1e3a5f,#2563eb);border:none;color:white">
        <div class="card-body" style="padding:2rem">
            <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}"
                         style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid rgba(255,255,255,.4)">
                @else
                    <div class="avatar" style="width:100px;height:100px;font-size:2rem;border:3px solid rgba(255,255,255,.4)">
                        {{ mb_strtoupper(mb_substr($user->name,0,2)) }}
                    </div>
                @endif

                <div style="flex:1;min-width:200px">
                    <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ $user->name }}</h1>
                    <div style="opacity:.85;margin-bottom:.5rem">{{ $user->email }}</div>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                        <span style="background:rgba(255,255,255,.2);padding:.25rem .75rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600">
                            {{ __('messages.' . $user->role) }}
                        </span>
                        <span style="background:rgba(255,255,255,.2);padding:.25rem .75rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:600">
                            {{ $user->is_active ? __('messages.active') : __('messages.inactive') }}
                        </span>
                    </div>
                </div>

                @if($user->id !== auth()->id())
                <div style="display:flex;gap:.5rem">
                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn" style="background:rgba(255,255,255,.2);color:white;border:1px solid rgba(255,255,255,.3)">
                            <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check' }}"></i>
                            {{ $user->is_active ? __('messages.deactivate') : __('messages.activate') }}
                        </button>
                    </form>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn" style="background:rgba(239,68,68,.8);color:white;border:none"
                                data-confirm-delete="{{ __('messages.delete_user_confirm') }}">
                            <i class="fas fa-trash"></i> {{ __('messages.delete') }}
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['label'=>__('messages.applications'), 'value'=>$stats['applications'], 'icon'=>'fa-file-alt',     'color'=>'primary'],
            ['label'=>__('messages.accepted'),     'value'=>$stats['accepted'],     'icon'=>'fa-check-circle', 'color'=>'success'],
            ['label'=>__('messages.pending'),      'value'=>$stats['pending'],      'icon'=>'fa-clock',        'color'=>'warning'],
            ['label'=>__('messages.saved_jobs'),   'value'=>$stats['saved_jobs'],   'icon'=>'fa-bookmark',     'color'=>'info'],
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

        <!-- Personal Info -->
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('messages.personal_info') }}</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:.875rem">
                    @foreach([
                        ['label'=>__('messages.full_name'),      'value'=>$user->name],
                        ['label'=>__('messages.email'),          'value'=>$user->email],
                        ['label'=>__('messages.phone'),          'value'=>$user->phone],
                        ['label'=>__('messages.location'),       'value'=>$user->location],
                        ['label'=>__('messages.experience_level'),'value'=>$user->experience_level ? __('messages.exp_' . $user->experience_level) : null],
                        ['label'=>__('messages.expected_salary'),'value'=>$user->expected_salary ? '$' . number_format($user->expected_salary) : null],
                        ['label'=>__('messages.joined'),         'value'=>$user->created_at->format('d M Y, H:i')],
                        ['label'=>__('messages.last_seen'),      'value'=>$user->last_seen_at?->diffForHumans()],
                    ] as $row)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:.5rem;border-bottom:1px solid var(--border)">
                        <span style="font-size:.8rem;color:var(--text-muted)">{{ $row['label'] }}</span>
                        <span style="font-weight:600;font-size:.875rem;text-align:end">{{ $row['value'] ?? '—' }}</span>
                    </div>
                    @endforeach
                </div>

                @if($user->bio)
                <div style="margin-top:1rem;padding:1rem;background:var(--bg-hover);border-radius:var(--radius)">
                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.25rem">{{ __('messages.bio') }}</div>
                    <div style="font-size:.875rem">{{ $user->bio }}</div>
                </div>
                @endif

                @if(!empty($user->skills))
                <div style="margin-top:1rem">
                    <div style="font-size:.75rem;color:var(--text-muted);margin-bottom:.5rem">{{ __('messages.skills') }}</div>
                    <div style="display:flex;flex-wrap:wrap;gap:.375rem">
                        @foreach($user->skills as $skill)
                        <span style="padding:.2rem .65rem;background:var(--primary-light);color:var(--primary);border-radius:var(--radius-full);font-size:.75rem;font-weight:600">{{ $skill }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Applications -->
        <div class="card">
            <div class="card-header"><span class="card-title">{{ __('messages.recent_applications') }}</span></div>
            <div class="card-body" style="padding:0">
                @forelse($user->jobApplications->take(8) as $app)
                <div style="display:flex;align-items:center;gap:.75rem;padding:.875rem 1.25rem;border-bottom:1px solid var(--border)">
                    <div class="avatar avatar-sm" style="border-radius:var(--radius-sm)">
                        {{ mb_strtoupper(mb_substr($app->job->company->name,0,2)) }}
                    </div>
                    <div style="flex:1;min-width:0">
                        <div style="font-size:.875rem;font-weight:600">{{ $app->job->title }}</div>
                        <div style="font-size:.75rem;color:var(--text-muted)">{{ $app->job->company->name }} • {{ $app->created_at->diffForHumans() }}</div>
                    </div>
                    <span class="status-badge {{ $app->status }}">{{ __('messages.' . $app->status) }}</span>
                </div>
                @empty
                <div style="padding:2rem;text-align:center;color:var(--text-muted)">
                    <i class="fas fa-inbox" style="font-size:2rem;opacity:.4;display:block;margin-bottom:.5rem"></i>
                    {{ __('messages.no_applications_yet') }}
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection