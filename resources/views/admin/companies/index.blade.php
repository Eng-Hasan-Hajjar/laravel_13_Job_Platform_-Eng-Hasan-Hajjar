@extends('layouts.app')
@section('title', __('messages.companies_management'))

@section('content')
<div class="page-container">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">
                <i class="fas fa-building" style="color:var(--primary)"></i> {{ __('messages.companies_management') }}
            </h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ __('messages.manage_all_companies') }}</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['label'=>__('messages.total'),      'value'=>$stats['total'],      'icon'=>'fa-building',     'color'=>'primary'],
            ['label'=>__('messages.verified'),   'value'=>$stats['verified'],   'icon'=>'fa-check-circle', 'color'=>'success'],
            ['label'=>__('messages.unverified'), 'value'=>$stats['unverified'], 'icon'=>'fa-clock',        'color'=>'warning'],
            ['label'=>__('messages.active'),     'value'=>$stats['active'],     'icon'=>'fa-circle',       'color'=>'info'],
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

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:1rem 1.25rem">
            <form method="GET" style="display:flex;gap:.75rem;align-items:center;flex-wrap:wrap">
                <input type="text" name="q" class="form-control" style="flex:1;min-width:200px"
                       placeholder="{{ __('messages.search_companies') }}" value="{{ request('q') }}">

                <select name="status" class="form-control" style="width:auto;min-width:160px" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="verified"   {{ request('status') === 'verified'   ? 'selected' : '' }}>{{ __('messages.verified') }}</option>
                    <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>{{ __('messages.unverified') }}</option>
                    <option value="active"     {{ request('status') === 'active'     ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="inactive"   {{ request('status') === 'inactive'   ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>

                <select name="sort" class="form-control" style="width:auto;min-width:140px" onchange="this.form.submit()">
                    <option value="latest"    {{ request('sort','latest') === 'latest' ? 'selected' : '' }}>{{ __('messages.sort_latest') }}</option>
                    <option value="oldest"    {{ request('sort') === 'oldest' ? 'selected' : '' }}>{{ __('messages.sort_oldest') }}</option>
                    <option value="name_asc"  {{ request('sort') === 'name_asc' ? 'selected' : '' }}>{{ __('messages.sort_name') }}</option>
                    <option value="most_jobs" {{ request('sort') === 'most_jobs' ? 'selected' : '' }}>{{ __('messages.most_jobs') }}</option>
                </select>

                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                @if(request()->hasAny(['q','status','sort']))
                <a href="{{ route('admin.companies.index') }}" class="btn btn-ghost"><i class="fas fa-times"></i></a>
                @endif
            </form>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.industry') }}</th>
                        <th>{{ __('messages.location') }}</th>
                        <th>{{ __('messages.jobs') }}</th>
                        <th>{{ __('messages.verified') }}</th>
                        <th>{{ __('messages.joined') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $c)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.75rem">
                                @if($c->logo)
                                    <img src="{{ Storage::url($c->logo) }}" alt="{{ $c->name }}"
                                         style="width:40px;height:40px;border-radius:var(--radius);object-fit:cover;border:1px solid var(--border)">
                                @else
                                    <div class="avatar avatar-sm" style="border-radius:var(--radius)">
                                        {{ mb_strtoupper(mb_substr($c->name,0,2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div style="font-weight:600;font-size:.875rem">{{ $c->name }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $c->user->email ?? '—' }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="font-size:.8rem">{{ $c->industry ?? '—' }}</td>
                        <td style="font-size:.8rem;color:var(--text-muted)">
                            @if($c->location)<i class="fas fa-map-marker-alt" style="font-size:.7rem"></i> {{ $c->location }}@else — @endif
                        </td>
                        <td>
                            <span style="background:var(--primary-light);color:var(--primary);padding:.15rem .5rem;border-radius:var(--radius-full);font-size:.75rem;font-weight:700">
                                {{ $c->jobs_count }}
                            </span>
                        </td>
                        <td>
                            @if($c->is_verified)
                                <span class="status-badge active"><i class="fas fa-check-circle"></i> {{ __('messages.verified') }}</span>
                            @else
                                <span class="status-badge inactive"><i class="fas fa-clock"></i> {{ __('messages.unverified') }}</span>
                            @endif
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted)">{{ $c->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('admin.companies.show', $c) }}" class="btn btn-ghost btn-sm" data-tooltip="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.companies.verify', $c) }}" method="POST" style="display:inline">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--{{ $c->is_verified ? 'warning' : 'success' }})"
                                            data-tooltip="{{ $c->is_verified ? __('messages.unverify') : __('messages.verify') }}">
                                        <i class="fas {{ $c->is_verified ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.companies.destroy', $c) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                                            data-confirm-delete="{{ __('messages.delete_company_confirm') }}"
                                            data-tooltip="{{ __('messages.delete') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:3rem;color:var(--text-muted)">
                            <i class="fas fa-building" style="font-size:2rem;display:block;margin-bottom:.5rem;opacity:.4"></i>
                            {{ __('messages.no_companies_found') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($companies->hasPages())
        <div style="padding:1rem">{{ $companies->links() }}</div>
        @endif
    </div>
</div>
@endsection