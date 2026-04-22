@extends('layouts.app')
@section('title', __('messages.users_management'))

@section('content')
<div class="page-container">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">👥 {{ __('messages.users_management') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ $users->total() }} {{ __('messages.total_users') }}</p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:.875rem 1.25rem">
            <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:center">
                <input type="text" name="search" class="form-control" style="width:250px"
                       placeholder="{{ __('messages.search_by_name_email') }}"
                       value="{{ request('search') }}">
                <select name="role" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_roles') }}</option>
                    <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>{{ __('messages.job_seeker') }}</option>
                    <option value="company" {{ request('role') === 'company' ? 'selected' : '' }}>{{ __('messages.employer') }}</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>{{ __('messages.admin') }}</option>
                </select>
                <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if(request()->hasAny(['search','role','status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm">{{ __('messages.reset') }}</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-4" style="margin-bottom:1.5rem">
        @foreach([
            ['label'=>__('messages.total_users'),'value'=>\App\Models\User::count(),'color'=>'primary'],
            ['label'=>__('messages.job_seekers'),'value'=>\App\Models\User::where('role','user')->count(),'color'=>'success'],
            ['label'=>__('messages.companies'),'value'=>\App\Models\User::where('role','company')->count(),'color'=>'warning'],
            ['label'=>__('messages.active_today'),'value'=>\App\Models\User::whereDate('last_seen_at',today())->count(),'color'=>'purple'],
        ] as $s)
        <div class="stat-card {{ $s['color'] }}">
            <div class="stat-value" style="font-size:1.5rem">{{ $s['value'] }}</div>
            <div class="stat-label">{{ $s['label'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Table -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.user') }}</th>
                        <th>{{ __('messages.role') }}</th>
                        <th>{{ __('messages.joined') }}</th>
                        <th>{{ __('messages.last_seen') }}</th>
                        <th>{{ __('messages.cv_analyzed') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:.625rem">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0">
                                @else
                                    <div class="avatar avatar-sm">{{ mb_strtoupper(mb_substr($user->name,0,2)) }}</div>
                                @endif
                                <div>
                                    <div style="font-weight:600;font-size:.875rem">{{ $user->name }}</div>
                                    <div style="font-size:.75rem;color:var(--text-muted)">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge {{ match($user->role) { 'admin'=>'rejected', 'company'=>'reviewed', default=>'active' } }}">
                                {{ __('messages.' . ($user->role === 'user' ? 'job_seeker' : $user->role)) }}
                            </span>
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">
                            {{ $user->last_seen_at ? $user->last_seen_at->diffForHumans() : '—' }}
                        </td>
                        <td>
                            @if($user->cv_analyzed)
                                <span style="color:var(--success);font-size:.8rem;font-weight:600">
                                    <i class="fas fa-check-circle"></i> {{ $user->cv_analyzed['score'] ?? 0 }}/100
                                </span>
                            @elseif($user->cv_path)
                                <span style="color:var(--warning);font-size:.8rem">
                                    <i class="fas fa-clock"></i> {{ __('messages.pending') }}
                                </span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem">—</span>
                            @endif
                        </td>
                        <td>
                            <span class="status-badge {{ $user->is_active ? 'active' : 'inactive' }}">
                                {{ $user->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem;align-items:center">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-ghost btn-icon btn-sm"
                                   data-tooltip="{{ __('messages.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button onclick="toggleUser({{ $user->id }}, this)"
                                        class="btn btn-ghost btn-icon btn-sm"
                                        style="color:{{ $user->is_active ? 'var(--warning)' : 'var(--success)' }}"
                                        data-tooltip="{{ $user->is_active ? __('messages.deactivate') : __('messages.activate') }}">
                                    <i class="fas {{ $user->is_active ? 'fa-ban' : 'fa-check-circle' }}"></i>
                                </button>
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm"
                                            style="color:var(--danger)"
                                            data-confirm-delete="{{ __('messages.delete_user_confirm') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7">
                        <div class="empty-state" style="padding:2rem">
                            <p>{{ __('messages.no_users_found') }}</p>
                        </div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="card-footer">
            <div class="pagination">
                @if(!$users->onFirstPage())
                    <a href="{{ $users->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                @endif
                @foreach($users->getUrlRange(max(1,$users->currentPage()-2),min($users->lastPage(),$users->currentPage()+2)) as $p=>$url)
                    <a href="{{ $url }}" class="page-link {{ $p===$users->currentPage()?'active':'' }}">{{ $p }}</a>
                @endforeach
                @if($users->hasMorePages())
                    <a href="{{ $users->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
async function toggleUser(id, btn) {
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    try {
        const res = await fetch(`/admin/users/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        toastr.success(data.message);
        setTimeout(() => location.reload(), 800);
    } catch(e) {
        toastr.error('Error');
    }
}
</script>
@endpush
@endsection