@extends('layouts.app')
@section('title', __('messages.notifications'))

@section('content')
<div class="page-container" style="max-width:800px">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h1 style="font-size:1.5rem;font-weight:800;margin-bottom:.25rem">🔔 {{ __('messages.notifications') }}</h1>
            @if($unreadCount > 0)
            <p style="color:var(--primary);font-size:.875rem;font-weight:600">
                {{ $unreadCount }} {{ __('messages.unread_notifications') }}
            </p>
            @endif
        </div>
        <div style="display:flex;gap:.625rem">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fas fa-check-double"></i> {{ __('messages.mark_all_read') }}
                </button>
            </form>
            <form action="{{ route('notifications.destroy-all') }}" method="POST" style="display:inline">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)"
                        data-confirm-delete="{{ __('messages.clear_all_notifications_confirm') }}">
                    <i class="fas fa-trash"></i> {{ __('messages.clear_all') }}
                </button>
            </form>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div style="display:flex;gap:.25rem;border-bottom:1px solid var(--border);margin-bottom:1.25rem;overflow-x:auto">
        @foreach(['all','unread','job','application','system'] as $filter)
        <a href="{{ route('notifications.index', ['filter' => $filter === 'all' ? null : $filter]) }}"
           style="padding:.625rem 1rem;font-size:.85rem;font-weight:600;text-decoration:none;white-space:nowrap;border-bottom:2px solid {{ (request('filter') ?? 'all') === $filter ? 'var(--primary)' : 'transparent' }};color:{{ (request('filter') ?? 'all') === $filter ? 'var(--primary)' : 'var(--text-muted)' }};transition:var(--transition)">
            {{ __('messages.filter_' . $filter) }}
        </a>
        @endforeach
    </div>

    <!-- Notifications List -->
    @forelse($notifications as $notification)
    @php $data = $notification->data; @endphp
    <div class="card animate-slide-up {{ !$notification->read_at ? '' : '' }}"
         style="margin-bottom:.75rem;border-left:4px solid {{ !$notification->read_at ? 'var(--primary)' : 'var(--border)' }};overflow:visible">
        <div class="card-body" style="padding:1rem 1.25rem">
            <div style="display:flex;align-items:flex-start;gap:.875rem">
                <!-- Icon -->
                <div class="notification-icon {{ $data['type'] ?? 'job' }}"
                     style="width:44px;height:44px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:1.125rem">
                    <i class="fas {{ match($data['type'] ?? 'job') {
                        'job' => 'fa-briefcase',
                        'application' => 'fa-file-alt',
                        'message' => 'fa-envelope',
                        'alert' => 'fa-exclamation-circle',
                        'success' => 'fa-check-circle',
                        default => 'fa-bell'
                    } }}"></i>
                </div>

                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem;flex-wrap:wrap">
                        <div>
                            <div style="font-weight:700;font-size:.9rem;margin-bottom:.25rem;color:var(--text-primary)">
                                {{ $data['title'] ?? '' }}
                            </div>
                            <div style="font-size:.85rem;color:var(--text-secondary);line-height:1.5;margin-bottom:.5rem">
                                {{ $data['body'] ?? '' }}
                            </div>

                            <!-- Extra data based on type -->
                            @if(isset($data['job_title']))
                            <div style="display:inline-flex;align-items:center;gap:.375rem;padding:.25rem .75rem;background:var(--bg-hover);border-radius:var(--radius-full);font-size:.75rem;color:var(--text-secondary);margin-bottom:.5rem">
                                <i class="fas fa-briefcase" style="color:var(--primary)"></i>
                                {{ $data['job_title'] }}
                                @if(isset($data['company'])) · {{ $data['company'] }} @endif
                            </div>
                            @endif

                            <div style="font-size:.75rem;color:var(--text-muted);display:flex;align-items:center;gap:.375rem">
                                <i class="fas fa-clock"></i>
                                {{ $notification->created_at->diffForHumans() }}
                                @if(!$notification->read_at)
                                    <span style="width:6px;height:6px;border-radius:50%;background:var(--primary);display:inline-block;margin-left:.375rem"></span>
                                    <span style="color:var(--primary);font-weight:600">{{ __('messages.new') }}</span>
                                @endif
                            </div>
                        </div>

                        <div style="display:flex;gap:.375rem;flex-shrink:0">
                            @if(isset($data['action_url']))
                            <a href="{{ $data['action_url'] }}" class="btn btn-primary btn-sm"
                               onclick="markRead('{{ $notification->id }}')">
                                {{ __('messages.view') }}
                            </a>
                            @endif
                            @if(!$notification->read_at)
                            <button onclick="markRead('{{ $notification->id }}', true)" class="btn btn-ghost btn-sm"
                                    data-tooltip="{{ __('messages.mark_read') }}">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="empty-state">
        <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
        <h3>{{ __('messages.no_notifications') }}</h3>
        <p>{{ __('messages.no_notifications_desc') }}</p>
    </div>
    @endforelse

    {{ $notifications->links() }}
</div>

@push('scripts')
<script>
async function markRead(id, reload = false) {
    await fetch(`/notifications/${id}/read`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    });
    if (reload) location.reload();
}
</script>
@endpush
@endsection