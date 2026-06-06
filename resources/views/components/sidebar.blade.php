@auth
    <aside class="sidebar" id="mainSidebar">
        <nav class="sidebar-nav">

            @php
                $user = auth()->user();
                $route = request()->route()?->getName() ?? '';
            @endphp

            {{-- ===== USER SIDEBAR ===== --}}
            @if($user->role === 'user')

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.main') }}</div>
                    <a href="{{ route('home') }}" class="sidebar-item {{ $route === 'home' ? 'active' : '' }}">
                        <i class="fas fa-home"></i>
                        <span>{{ __('messages.home') }}</span>
                    </a>
                    <a href="{{ route('jobs.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'jobs.') && $route !== 'jobs.recommended' ? 'active' : '' }}">
                        <i class="fas fa-briefcase"></i>
                        <span>{{ __('messages.browse_jobs') }}</span>
                    </a>
                    <a href="{{ route('jobs.recommended') }}"
                        class="sidebar-item {{ $route === 'jobs.recommended' ? 'active' : '' }}">
                        <i class="fas fa-magic"></i>
                        <span>{{ __('messages.recommended') }}</span>
                        <span class="item-badge">AI</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.my_account') }}</div>
                    <a href="{{ route('user.profile') }}" class="sidebar-item {{ $route === 'user.profile' ? 'active' : '' }}">
                        <i class="fas fa-user-circle"></i>
                        <span>{{ __('messages.my_profile') }}</span>
                    </a>
                    <a href="{{ route('user.applications') }}"
                        class="sidebar-item {{ str_starts_with($route, 'user.application') ? 'active' : '' }}">
                        <i class="fas fa-file-alt"></i>
                        <span>{{ __('messages.my_applications') }}</span>
                        @php $pendingApps = auth()->user()->jobApplications()->where('status', 'pending')->count(); @endphp
                        @if($pendingApps > 0)
                            <span class="item-badge">{{ $pendingApps }}</span>
                        @endif
                    </a>
                    <a href="{{ route('user.saved-jobs') }}"
                        class="sidebar-item {{ $route === 'user.saved-jobs' ? 'active' : '' }}">
                        <i class="fas fa-bookmark"></i>
                        <span>{{ __('messages.saved_jobs') }}</span>
                    </a>
                    <a href="{{ route('user.cv') }}" class="sidebar-item {{ $route === 'user.cv' ? 'active' : '' }}">
                        <i class="fas fa-file-pdf"></i>
                        <span>{{ __('messages.my_cv') }}</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.companies') }}</div>
                    <a href="{{ route('companies.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'companies.') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>{{ __('messages.companies') }}</span>
                    </a>
                </div>

                {{-- ===== COMPANY SIDEBAR ===== --}}
            @elseif($user->role === 'company')

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.dashboard') }}</div>
                    <a href="{{ route('company.dashboard') }}"
                        class="sidebar-item {{ $route === 'company.dashboard' ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>{{ __('messages.overview') }}</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.jobs') }}</div>
                    <a href="{{ route('company.jobs.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'company.jobs.') && $route !== 'company.jobs.create' ? 'active' : '' }}">
                        <i class="fas fa-briefcase"></i>
                        <span>{{ __('messages.my_jobs') }}</span>
                    </a>
                    <a href="{{ route('company.jobs.create') }}"
                        class="sidebar-item {{ $route === 'company.jobs.create' ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>{{ __('messages.post_job') }}</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.applications') }}</div>
                    <a href="{{ route('company.applications.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'company.applications.') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>{{ __('messages.all_applications') }}</span>
                        @php $newApps = \App\Models\JobApplication::whereHas('job', fn($q) => $q->where('company_id', $user->company?->id ?? 0))->where('status', 'pending')->count(); @endphp
                        @if($newApps > 0)
                            <span class="item-badge">{{ $newApps }}</span>
                        @endif
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.profile') }}</div>
                    <a href="{{ route('company.profile') }}"
                        class="sidebar-item {{ $route === 'company.profile' ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>{{ __('messages.company_profile') }}</span>
                    </a>
                    <a href="{{ route('company.reviews') }}"
                        class="sidebar-item {{ str_starts_with($route, 'company.reviews') ? 'active' : '' }}">
                        <i class="fas fa-star"></i>
                        <span>{{ __('messages.reviews') }}</span>
                    </a>
                </div>

                {{-- ===== ADMIN SIDEBAR ===== --}}
            @elseif($user->role === 'admin')

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.admin') }}</div>
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-item {{ $route === 'admin.dashboard' ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>{{ __('messages.dashboard') }}</span>
                    </a>
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.management') }}</div>
                    <a href="{{ route('admin.users.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'admin.users.') ? 'active' : '' }}">
                        <i class="fas fa-users"></i>
                        <span>{{ __('messages.users') }}</span>
                    </a>
                    <a href="{{ route('admin.companies.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'admin.companies.') ? 'active' : '' }}">
                        <i class="fas fa-building"></i>
                        <span>{{ __('messages.companies') }}</span>
                    </a>
                    <a href="{{ route('admin.jobs.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'admin.jobs.') ? 'active' : '' }}">
                        <i class="fas fa-briefcase"></i>
                        <span>{{ __('messages.jobs') }}</span>
                    </a>
                    {{-- admin.applications.index غير موجود في routes — تم حذفه --}}
                </div>

                <div class="sidebar-section">
                    <div class="sidebar-section-title">{{ __('messages.system') }}</div>

                    <a href="{{ route('live-stats') }}" class="sidebar-item">
                        <i class="fas fa-bolt"></i>
                        <span>{{ __('messages.live_statistics') }}</span>
                        <span class="item-badge" style="background:#10b981">LIVE</span>
                    </a>


                    <a href="{{ route('admin.notifications.index') }}"
                        class="sidebar-item {{ str_starts_with($route, 'admin.notifications.') ? 'active' : '' }}">
                        <i class="fas fa-bell"></i>
                        <span>{{ __('messages.notifications') }}</span>
                    </a>
                    <a href="{{ route('admin.settings') }}"
                        class="sidebar-item {{ $route === 'admin.settings' ? 'active' : '' }}">
                        <i class="fas fa-cog"></i>
                        <span>{{ __('messages.settings') }}</span>
                    </a>
                </div>

            @endif

            {{-- ===== Common Bottom Links ===== --}}
            <div class="sidebar-section">
                <div class="sidebar-section-title">{{ __('messages.account') }}</div>
                <a href="{{ route('notifications.index') }}"
                    class="sidebar-item {{ $route === 'notifications.index' ? 'active' : '' }}">
                    <i class="fas fa-bell"></i>
                    <span>{{ __('messages.notifications') }}</span>
                    @php $unread = auth()->user()->unreadNotifications()->count(); @endphp
                    @if($unread > 0)
                        <span class="item-badge">{{ $unread }}</span>
                    @endif
                </a>
                <a href="{{ route('settings') }}" class="sidebar-item {{ $route === 'settings' ? 'active' : '' }}">
                    <i class="fas fa-cog"></i>
                    <span>{{ __('messages.settings') }}</span>
                </a>
            </div>

        </nav>
    </aside>
@endauth