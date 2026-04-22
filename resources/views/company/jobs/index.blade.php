@extends('layouts.app')
@section('title', __('messages.my_jobs'))

@section('content')
<div class="page-container">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">{{ __('messages.my_jobs') }}</h1>
            <p style="color:var(--text-secondary);font-size:.875rem">{{ $jobs->total() }} {{ __('messages.total_jobs') }}</p>
        </div>
        <a href="{{ route('company.jobs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.post_new_job') }}
        </a>
    </div>

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:1.25rem">
        <div class="card-body" style="padding:.875rem 1.25rem">
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
                @foreach(['all'=>__('messages.all'),'active'=>__('messages.active'),'inactive'=>__('messages.inactive')] as $val=>$label)
                <a href="{{ route('company.jobs.index', ['status'=>$val==='all'?null:$val]) }}"
                   class="btn btn-sm {{ (request('status') ?? 'all') === $val ? 'btn-primary' : 'btn-ghost' }}">
                    {{ $label }}
                </a>
                @endforeach
                <div style="margin-left:auto;display:flex;gap:.5rem">
                    <input type="text" class="form-control" style="width:200px;font-size:.8rem"
                           placeholder="{{ __('messages.search_jobs') }}"
                           onkeypress="if(event.key==='Enter'){window.location='?q='+this.value+'&status={{ request('status') }}'}"
                           value="{{ request('q') }}">
                </div>
            </div>
        </div>
    </div>

    <!-- Jobs Table -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('messages.job_title') }}</th>
                        <th>{{ __('messages.type') }}</th>
                        <th>{{ __('messages.applicants') }}</th>
                        <th>{{ __('messages.views') }}</th>
                        <th>{{ __('messages.deadline') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th>{{ __('messages.featured') }}</th>
                        <th>{{ __('messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $job)
                    <tr>
                        <td style="max-width:220px">
                            <div style="font-weight:700;font-size:.875rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                {{ $job->title }}
                            </div>
                            <div style="font-size:.75rem;color:var(--text-muted);margin-top:.125rem">
                                <i class="fas fa-map-marker-alt"></i> {{ $job->location }}
                            </div>
                        </td>
                        <td>
                            <span class="job-tag type" style="font-size:.7rem">
                                {{ __('messages.' . str_replace('-','_', $job->type)) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('company.applications.index', ['job_id' => $job->id]) }}"
                               style="font-weight:700;color:var(--primary);text-decoration:none;font-size:.9rem">
                                {{ $job->applications_count }}
                            </a>
                        </td>
                        <td style="font-size:.875rem;color:var(--text-muted)">
                            {{ number_format($job->views_count) }}
                        </td>
                        <td>
                            @if($job->deadline)
                                @php $diff = now()->diffInDays($job->deadline, false); @endphp
                                <span style="font-size:.8rem;color:{{ $diff < 0 ? 'var(--danger)' : ($diff <= 3 ? 'var(--warning)' : 'var(--text-secondary)') }}">
                                    {{ $job->deadline->format('d M Y') }}
                                    @if($diff < 0) <i class="fas fa-exclamation-circle"></i>
                                    @elseif($diff <= 3) ({{ $diff }}d)
                                    @endif
                                </span>
                            @else
                                <span style="color:var(--text-muted);font-size:.8rem">—</span>
                            @endif
                        </td>
                        <td>
                            <label style="display:flex;align-items:center;gap:.375rem;cursor:pointer">
                                <div class="toggle-switch" onclick="toggleJobStatus({{ $job->id }}, this)">
                                    <input type="checkbox" {{ $job->is_active ? 'checked' : '' }} style="display:none">
                                    <div style="width:38px;height:20px;border-radius:10px;background:{{ $job->is_active ? 'var(--success)' : 'var(--border)' }};position:relative;transition:var(--transition)" id="toggle-{{ $job->id }}">
                                        <div style="width:16px;height:16px;border-radius:50%;background:white;position:absolute;top:2px;transition:var(--transition);left:{{ $job->is_active ? '20px' : '2px' }}" id="thumb-{{ $job->id }}"></div>
                                    </div>
                                </div>
                            </label>
                        </td>
                        <td>
                            @if($job->is_featured)
                                <span style="color:var(--warning);font-size:1rem"><i class="fas fa-star"></i></span>
                            @else
                                <span style="color:var(--border);font-size:1rem"><i class="far fa-star"></i></span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:.375rem">
                                <a href="{{ route('jobs.show', $job) }}" target="_blank"
                                   class="btn btn-ghost btn-icon btn-sm" data-tooltip="{{ __('messages.preview') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('company.jobs.edit', $job) }}"
                                   class="btn btn-ghost btn-icon btn-sm" data-tooltip="{{ __('messages.edit') }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('company.applications.index', ['job_id' => $job->id]) }}"
                                   class="btn btn-ghost btn-icon btn-sm" data-tooltip="{{ __('messages.applications') }}">
                                    <i class="fas fa-users"></i>
                                </a>
                                <form action="{{ route('company.jobs.destroy', $job) }}" method="POST" style="display:inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-icon btn-sm"
                                            style="color:var(--danger)"
                                            data-confirm-delete="{{ __('messages.delete_job_confirm') }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8">
                            <div class="empty-state" style="padding:3rem">
                                <div class="empty-state-icon"><i class="fas fa-briefcase"></i></div>
                                <h3>{{ __('messages.no_jobs_yet') }}</h3>
                                <p>{{ __('messages.post_first_job') }}</p>
                                <a href="{{ route('company.jobs.create') }}" class="btn btn-primary" style="margin-top:1rem">
                                    <i class="fas fa-plus"></i> {{ __('messages.post_job') }}
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($jobs->hasPages())
        <div class="card-footer">
            <div class="pagination">
                @if($jobs->onFirstPage())
                    <span class="page-link disabled"><i class="fas fa-chevron-left"></i></span>
                @else
                    <a href="{{ $jobs->previousPageUrl() }}" class="page-link"><i class="fas fa-chevron-left"></i></a>
                @endif
                @foreach($jobs->getUrlRange(max(1,$jobs->currentPage()-2), min($jobs->lastPage(),$jobs->currentPage()+2)) as $page => $url)
                    <a href="{{ $url }}" class="page-link {{ $page === $jobs->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                @endforeach
                @if($jobs->hasMorePages())
                    <a href="{{ $jobs->nextPageUrl() }}" class="page-link"><i class="fas fa-chevron-right"></i></a>
                @else
                    <span class="page-link disabled"><i class="fas fa-chevron-right"></i></span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
async function toggleJobStatus(id, el) {
    const bar   = document.getElementById('toggle-' + id);
    const thumb = document.getElementById('thumb-' + id);
    const isActive = thumb.style.left === '20px';

    // Optimistic UI update
    if (isActive) {
        bar.style.background = 'var(--border)';
        thumb.style.left = '2px';
    } else {
        bar.style.background = 'var(--success)';
        thumb.style.left = '20px';
    }

    try {
        const res = await fetch(`/company/jobs/${id}/toggle`, {
            method: 'PATCH',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        const data = await res.json();
        toastr.success(data.is_active ? '{{ __("messages.job_activated") }}' : '{{ __("messages.job_deactivated") }}');
    } catch (e) {
        // Revert on error
        if (isActive) {
            bar.style.background = 'var(--success)'; thumb.style.left = '20px';
        } else {
            bar.style.background = 'var(--border)'; thumb.style.left = '2px';
        }
        toastr.error('{{ __("messages.error_occurred") }}');
    }
}
</script>
@endpush
@endsection