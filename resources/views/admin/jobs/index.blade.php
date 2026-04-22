@extends('layouts.app')
@section('title', __('messages.jobs_management'))

@section('content')
<div class="page-container">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">💼 {{ __('messages.jobs_management') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ $jobs->total() }} {{ __('messages.total_jobs') }}</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:.875rem 1.25rem">
            <form method="GET" style="display:flex;gap:.625rem;flex-wrap:wrap;align-items:center">
                <input type="text" name="search" class="form-control" style="width:220px"
                       placeholder="{{ __('messages.search_jobs') }}" value="{{ request('search') }}">
                <select name="type" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_types') }}</option>
                    @foreach(['full-time','part-time','freelance','remote','internship'] as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                        {{ __('messages.' . str_replace('-','_',$t)) }}
                    </option>
                    @endforeach
                </select>
                <select name="status" class="form-control" style="width:auto" onchange="this.form.submit()">
                    <option value="">{{ __('messages.all_statuses') }}</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('messages.active') }}</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('messages.inactive') }}</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
                @if(request()->hasAny(['search','type','status']))
                <a href="{{ route('admin.jobs.index') }}" class="btn btn-ghost btn-sm">{{ __('messages.reset') }}</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.job') }}</th>
                        <th>{{ __('messages.company') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th>{{ __('messages.applicants') }}</th>
                        <th>{{ __('messages.views') }}</th>
                        <th>{{ __('messages.posted') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.featured') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                    <tr>
                        <td style="max-width:200px">
                            <div style="font-weight:600;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $job->title }}</div>
                            <div style="font-size:.72rem;color:var(--text-muted)">{{ $job->location }}</div>
                        </td>
                        <td style="font-size:.875rem;white-space:nowrap">{{ $job->company->name }}</td>
                        <td>
                            <span class="job-tag type" style="font-size:.7rem">
                                {{ __('messages.' . str_replace('-','_',$job->type)) }}
                            </span>
                        </td>
                        <td style="font-size:.9rem;font-weight:700;text-align:center">{{ $job->applications_count }}</td>
                        <td style="font-size:.875rem;color:var(--text-muted);text-align:center">{{ number_format($job->views_count) }}</td>
                        <td style="font-size:.8rem;color:var(--text-muted);white-space:nowrap">{{ $job->created_at->format('d M Y') }}</td>
                        <td>
                            <span class="status-badge {{ $job->is_active ? 'active' : 'inactive' }}">
                                {{ $job->is_active ? __('messages.active') : __('messages.inactive') }}
                            </span>
                        </td>
                        <td>
                            <button onclick="toggleFeatured({{ $job->id }}, this)"
                                    class="btn btn-sm {{ $job->is_featured ? 'btn-warning' : 'btn-ghost' }}"
                                    style="padding:.25rem .625rem;font-size:.75rem">
                                <i class="fas fa-star"></i> {{ $job->is_featured ? __('messages.featured') : __('messages.feature') }}
                            </button>
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('jobs.show', $job) }}" target="_blank"
                                   class="btn btn-ghost btn-icon btn-sm"><i class="fas fa-eye"></i></a>
                                <form action="{{ route('admin.jobs.destroy', $job) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm"
                                            style="color:var(--danger)" data-confirm-delete>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="9">
                        <div class="empty-state" style="padding:2rem"><p>{{ __('messages.no_jobs_found') }}</p></div>
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($jobs->hasPages())
        <div class="card-footer">
            <div class="pagination">
                @if(!$jobs->onFirstPage())<a href="{{ $jobs->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>@endif
                @foreach($jobs->getUrlRange(max(1,$jobs->currentPage()-2),min($jobs->lastPage(),$jobs->currentPage()+2)) as $p=>$url)
                    <a href="{{ $url }}" class="page-link {{ $p===$jobs->currentPage()?'active':'' }}">{{ $p }}</a>
                @endforeach
                @if($jobs->hasMorePages())<a href="{{ $jobs->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>@endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
async function toggleFeatured(id, btn) {
    const res = await fetch(`/admin/jobs/${id}/featured`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    const data = await res.json();
    if (data.is_featured) {
        btn.classList.replace('btn-ghost', 'btn-warning');
        btn.innerHTML = '<i class="fas fa-star"></i> {{ __("messages.featured") }}';
    } else {
        btn.classList.replace('btn-warning', 'btn-ghost');
        btn.innerHTML = '<i class="fas fa-star"></i> {{ __("messages.feature") }}';
    }
    toastr.success(data.message);
}
</script>
@endpush
@endsection