<!-- Notification Dropdown Panel -->
<div id="notificationPanel" class="notification-panel">
    <div class="notification-panel-header">
        <h4>🔔 {{ __('messages.notifications') }}</h4>
        <div style="display:flex;gap:.5rem;align-items:center">
            <button id="markAllReadBtn" class="btn btn-ghost btn-sm" style="font-size:.75rem">
                {{ __('messages.mark_all_read') }}
            </button>
            <a href="{{ route('notifications.index') }}" class="btn btn-ghost btn-sm" style="font-size:.75rem">
                {{ __('messages.view_all') }}
            </a>
        </div>
    </div>

    <div class="notification-list" id="notificationList">
        @if(auth()->user()->notifications()->count() === 0)
            <div class="empty-state" style="padding:2rem">
                <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                <p>{{ __('messages.no_notifications') }}</p>
            </div>
        @else
            @foreach(auth()->user()->notifications()->latest()->take(15)->get() as $notification)
                @php $data = $notification->data; @endphp
                <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}"
                     data-id="{{ $notification->id }}"
                     onclick="NotificationManager.markRead('{{ $notification->id }}', '{{ $data['action_url'] ?? '#' }}')">

                    <div class="notification-icon {{ $data['type'] ?? 'job' }}">
                        <i class="fas {{ match($data['type'] ?? 'job') {
                            'job' => 'fa-briefcase',
                            'application' => 'fa-file-alt',
                            'message' => 'fa-envelope',
                            'alert' => 'fa-exclamation-circle',
                            default => 'fa-bell'
                        } }}"></i>
                    </div>

                    <div class="notification-content">
                        <div class="notification-title">{{ $data['title'] ?? '' }}</div>
                        <div class="notification-body">{{ $data['body'] ?? '' }}</div>
                        <div class="notification-time">
                            <i class="fas fa-clock"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </div>
                    </div>

                    @if(!$notification->read_at)
                        <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px"></div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    <div class="notification-panel-footer">
        <a href="{{ route('notifications.index') }}" class="btn btn-outline btn-sm" style="width:100%">
            {{ __('messages.see_all_notifications') }}
        </a>
    </div>
</div>