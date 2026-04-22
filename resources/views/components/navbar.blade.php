<nav class="navbar">
    <!-- Mobile Sidebar Toggle -->
    <button id="sidebarToggle" class="nav-icon-btn d-md-none" aria-label="Menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Brand -->
    <a href="{{ url('/') }}" class="navbar-brand">
        <div class="brand-icon">
            <i class="fas fa-briefcase"></i>
        </div>
        <span>{{ config('app.name', 'JobPortal') }}</span>
    </a>

    <!-- Search Bar -->
    <div class="navbar-search d-none d-md-block">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="navSearch" placeholder="{{ __('messages.search_jobs') }}"
               value="{{ request('q') }}"
               onkeypress="if(event.key==='Enter'){window.location='/jobs?q='+this.value}">
    </div>

    <!-- Actions -->
    <div class="navbar-actions">

        <!-- Language Toggle -->
        <button id="langSwitcher" class="nav-icon-btn"
                data-tooltip="{{ app()->getLocale() === 'ar' ? 'English' : 'العربية' }}">
            <span style="font-size:.8rem;font-weight:700">
                {{ app()->getLocale() === 'ar' ? 'EN' : 'ع' }}
            </span>
        </button>

        <!-- Dark/Light Toggle -->
        <button id="themeToggle" class="nav-icon-btn" data-tooltip="{{ __('messages.toggle_theme') }}">
            <i class="fas fa-moon"></i>
        </button>

        @auth
            <!-- Notifications -->
            <button id="notificationBtn" class="nav-icon-btn" aria-label="{{ __('messages.notifications') }}">
                <i class="fas fa-bell"></i>
                @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                @if($unreadCount > 0)
                    <span class="badge" data-notification-badge>{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                @else
                    <span class="badge" data-notification-badge style="display:none">0</span>
                @endif
            </button>

            <!-- User Menu -->
            <div class="user-menu">
                @php $user = auth()->user(); @endphp
                @if($user->avatar)
                    <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="user-avatar">
                @else
                    <div class="avatar avatar-md" style="cursor:pointer">
                        {{ mb_strtoupper(mb_substr($user->name, 0, 2)) }}
                    </div>
                @endif

                <div class="user-dropdown">
                    <div class="user-dropdown-header">
                        <div class="name">{{ $user->name }}</div>
                        <div class="email">{{ $user->email }}</div>
                    </div>

                    @if($user->role === 'user')
                        <a href="{{ route('user.profile') }}">
                            <i class="fas fa-user"></i> {{ __('messages.my_profile') }}
                        </a>
                        <a href="{{ route('user.applications') }}">
                            <i class="fas fa-file-alt"></i> {{ __('messages.my_applications') }}
                        </a>
                    @elseif($user->role === 'company')
                        <a href="{{ route('company.dashboard') }}">
                            <i class="fas fa-building"></i> {{ __('messages.company_dashboard') }}
                        </a>
                        <a href="{{ route('company.jobs.create') }}">
                            <i class="fas fa-plus"></i> {{ __('messages.post_job') }}
                        </a>
                    @elseif($user->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-shield-alt"></i> {{ __('messages.admin_panel') }}
                        </a>
                    @endif

                    <a href="{{ route('notifications.index') }}">
                        <i class="fas fa-bell"></i> {{ __('messages.notifications') }}
                        @if($unreadCount > 0)
                            <span class="item-badge">{{ $unreadCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('settings') }}">
                        <i class="fas fa-cog"></i> {{ __('messages.settings') }}
                    </a>

                    <a href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                       style="color:var(--danger)">
                        <i class="fas fa-sign-out-alt"></i> {{ __('messages.logout') }}
                    </a>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
                        @csrf
                    </form>
                </div>
            </div>
        @else
            <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                {{ __('messages.login') }}
            </a>
            <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                {{ __('messages.register') }}
            </a>
        @endauth
    </div>
</nav>

<script>
    // Pass auth state and translations to JS
    window.isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};
    window.i18n = {
        darkMode: "{{ __('messages.dark_mode') }}",
        lightMode: "{{ __('messages.light_mode') }}",
        noNotifications: "{{ __('messages.no_notifications') }}",
        confirmAction: "{{ __('messages.confirm_action') }}",
        cancel: "{{ __('messages.cancel') }}",
        confirm: "{{ __('messages.confirm') }}",
        deleteConfirm: "{{ __('messages.delete_confirm') }}"
    };
</script>