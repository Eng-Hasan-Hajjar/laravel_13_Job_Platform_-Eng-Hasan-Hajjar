/**
 * JOB PLATFORM - MAIN JAVASCRIPT
 * Dark/Light Mode | Language Switcher | Real-time Notifications
 */

'use strict';

/* ==========================================
   THEME MANAGER
   ========================================== */
const ThemeManager = {
    init() {
        const saved = localStorage.getItem('theme') || 'light';
        this.apply(saved);
        this.bindToggle();
    },
    apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        const btn = document.getElementById('themeToggle');
        if (btn) {
            btn.innerHTML = theme === 'dark'
                ? '<i class="fas fa-sun"></i>'
                : '<i class="fas fa-moon"></i>';
            btn.setAttribute('title', theme === 'dark' ? window.i18n?.lightMode || 'Light Mode' : window.i18n?.darkMode || 'Dark Mode');
        }
    },
    toggle() {
        const current = document.documentElement.getAttribute('data-theme') || 'light';
        this.apply(current === 'dark' ? 'light' : 'dark');
    },
    bindToggle() {
        const btn = document.getElementById('themeToggle');
        if (btn) btn.addEventListener('click', () => this.toggle());
    }
};

/* ==========================================
   SIDEBAR MANAGER
   ========================================== */
const SidebarManager = {
    init() {
        this.sidebar = document.querySelector('.sidebar');
        this.overlay = document.querySelector('.sidebar-overlay');
        this.bindToggle();
        this.highlightActive();
    },
    open() {
        this.sidebar?.classList.add('open');
        this.overlay?.classList.add('visible');
        document.body.style.overflow = 'hidden';
    },
    close() {
        this.sidebar?.classList.remove('open');
        this.overlay?.classList.remove('visible');
        document.body.style.overflow = '';
    },
    toggle() {
        this.sidebar?.classList.contains('open') ? this.close() : this.open();
    },
    bindToggle() {
        document.getElementById('sidebarToggle')?.addEventListener('click', () => this.toggle());
        this.overlay?.addEventListener('click', () => this.close());
    },
    highlightActive() {
        const path = window.location.pathname;
        document.querySelectorAll('.sidebar-item').forEach(item => {
            if (item.getAttribute('href') === path) {
                item.classList.add('active');
            }
        });
    }
};

/* ==========================================
   NOTIFICATION MANAGER
   ========================================== */
const NotificationManager = {
    panel: null,
    badge: null,
    pollingInterval: null,
    lastId: 0,

    init() {
        this.panel = document.getElementById('notificationPanel');
        this.badge = document.querySelector('[data-notification-badge]');
        this.bindEvents();
        if (window.isAuthenticated) {
            this.startPolling();
        }
    },

    toggle() {
        this.panel?.classList.toggle('open');
        if (this.panel?.classList.contains('open')) {
            this.markAllAsSeen();
        }
    },

    close() {
        this.panel?.classList.remove('open');
    },

    async fetch() {
        try {
            const res = await $.ajax({
                url: '/notifications/latest',
                method: 'GET',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            if (res.notifications) {
                this.updatePanel(res.notifications);
                this.updateBadge(res.unread_count);
            }
        } catch (e) {
            console.warn('Notification fetch failed', e);
        }
    },

    updatePanel(notifications) {
        const list = document.getElementById('notificationList');
        if (!list) return;

        if (notifications.length === 0) {
            list.innerHTML = `
                <div class="empty-state" style="padding:2rem">
                    <div class="empty-state-icon"><i class="fas fa-bell-slash"></i></div>
                    <p>${window.i18n?.noNotifications || 'No notifications yet'}</p>
                </div>`;
            return;
        }

        list.innerHTML = notifications.map(n => `
            <div class="notification-item ${n.read_at ? '' : 'unread'}" data-id="${n.id}" onclick="NotificationManager.markRead(${n.id}, '${n.action_url || '#'}')">
                <div class="notification-icon ${n.type}">
                    <i class="fas ${this.getIcon(n.type)}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${n.title}</div>
                    <div class="notification-body">${n.body}</div>
                    <div class="notification-time">${n.time_ago}</div>
                </div>
                ${!n.read_at ? '<div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px"></div>' : ''}
            </div>
        `).join('');
    },

    updateBadge(count) {
        document.querySelectorAll('[data-notification-badge]').forEach(el => {
            if (count > 0) {
                el.textContent = count > 99 ? '99+' : count;
                el.style.display = 'flex';
            } else {
                el.style.display = 'none';
            }
        });
    },

    async markRead(id, url) {
        try {
            await $.ajax({
                url: `/notifications/${id}/read`,
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            document.querySelector(`.notification-item[data-id="${id}"]`)?.classList.remove('unread');
            if (url && url !== '#') window.location.href = url;
        } catch (e) {}
    },

    async markAllAsRead() {
        try {
            await $.ajax({
                url: '/notifications/mark-all-read',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            document.querySelectorAll('.notification-item.unread').forEach(el => el.classList.remove('unread'));
            this.updateBadge(0);
        } catch (e) {}
    },

    async markAllAsSeen() {
        try {
            await $.ajax({
                url: '/notifications/mark-seen',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
        } catch (e) {}
    },

    showToast(notification) {
        const toast = document.createElement('div');
        toast.className = 'rt-notification animate-slide-up';
        toast.innerHTML = `
            <div class="notification-icon ${notification.type}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center">
                <i class="fas ${this.getIcon(notification.type)}"></i>
            </div>
            <div style="flex:1;min-width:0">
                <div style="font-weight:700;font-size:.85rem;margin-bottom:.2rem">${notification.title}</div>
                <div style="font-size:.8rem;color:var(--text-secondary);line-height:1.4">${notification.body}</div>
            </div>
            <button onclick="this.parentElement.remove()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:.25rem;margin-left:.5rem">
                <i class="fas fa-times"></i>
            </button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 6000);
    },

    getIcon(type) {
        const icons = {
            job: 'fa-briefcase',
            application: 'fa-file-alt',
            message: 'fa-envelope',
            alert: 'fa-exclamation-circle',
            success: 'fa-check-circle',
            system: 'fa-cog'
        };
        return icons[type] || 'fa-bell';
    },

    startPolling() {
        this.fetch();
        this.pollingInterval = setInterval(() => this.fetch(), 30000); // every 30s
    },

    stopPolling() {
        if (this.pollingInterval) clearInterval(this.pollingInterval);
    },

    bindEvents() {
        document.getElementById('notificationBtn')?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggle();
        });

        document.getElementById('markAllReadBtn')?.addEventListener('click', () => this.markAllAsRead());

        document.addEventListener('click', (e) => {
            if (this.panel && !this.panel.contains(e.target) && !document.getElementById('notificationBtn')?.contains(e.target)) {
                this.close();
            }
        });
    }
};

/* ==========================================
   LANGUAGE SWITCHER
   ========================================== */
const LanguageManager = {
    init() {
        document.getElementById('langSwitcher')?.addEventListener('click', () => {
            const current = document.documentElement.lang;
            window.location.href = `/lang/${current === 'ar' ? 'en' : 'ar'}`;
        });
    }
};

/* ==========================================
   FORM HELPERS
   ========================================== */
const FormManager = {
    init() {
        this.initDragDropUploads();
        this.initFormValidation();
        this.initAjaxForms();
    },

    initDragDropUploads() {
        document.querySelectorAll('.upload-zone').forEach(zone => {
            const input = zone.querySelector('input[type="file"]');

            zone.addEventListener('click', () => input?.click());

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('dragover');
            });

            zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('dragover');
                if (input && e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    this.handleFileSelect(zone, e.dataTransfer.files[0]);
                }
            });

            input?.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    this.handleFileSelect(zone, e.target.files[0]);
                }
            });
        });
    },

    handleFileSelect(zone, file) {
        const info = zone.querySelector('.upload-info');
        if (info) {
            info.innerHTML = `
                <div style="font-weight:600;color:var(--success)">${file.name}</div>
                <div style="font-size:.8rem;color:var(--text-muted)">${this.formatFileSize(file.size)}</div>
            `;
        }
        zone.style.borderColor = 'var(--success)';
    },

    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    },

    initFormValidation() {
        document.querySelectorAll('form[data-validate]').forEach(form => {
            form.addEventListener('submit', (e) => {
                let valid = true;
                form.querySelectorAll('[required]').forEach(field => {
                    if (!field.value.trim()) {
                        field.classList.add('is-invalid');
                        valid = false;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                if (!valid) e.preventDefault();
            });
        });
    },

    initAjaxForms() {
        document.querySelectorAll('form[data-ajax]').forEach(form => {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = form.querySelector('[type="submit"]');
                const originalText = btn?.innerHTML;
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                }

                try {
                    const formData = new FormData(form);
                    const res = await fetch(form.action, {
                        method: form.method || 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    if (data.success) {
                        toastr.success(data.message);
                        if (data.redirect) setTimeout(() => window.location.href = data.redirect, 1000);
                    } else {
                        toastr.error(data.message || 'Error occurred');
                        if (data.errors) {
                            Object.entries(data.errors).forEach(([field, msgs]) => {
                                const el = form.querySelector(`[name="${field}"]`);
                                if (el) el.classList.add('is-invalid');
                            });
                        }
                    }
                } catch (err) {
                    toastr.error('Connection error. Please try again.');
                } finally {
                    if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
                }
            });
        });
    }
};

/* ==========================================
   JOB SEARCH
   ========================================== */
const JobSearch = {
    debounceTimer: null,
    init() {
        const input = document.getElementById('liveSearch');
        if (input) {
            input.addEventListener('input', (e) => {
                clearTimeout(this.debounceTimer);
                this.debounceTimer = setTimeout(() => this.search(e.target.value), 400);
            });
        }

        document.querySelectorAll('.filter-checkbox input').forEach(cb => {
            cb.addEventListener('change', () => this.applyFilters());
        });
    },

    async search(query) {
        const container = document.getElementById('jobResults');
        if (!container) return;

        container.innerHTML = `
            <div style="text-align:center;padding:2rem">
                <i class="fas fa-spinner fa-spin" style="font-size:1.5rem;color:var(--primary)"></i>
            </div>`;

        try {
            const params = new URLSearchParams(window.location.search);
            if (query) params.set('q', query);
            const res = await fetch(`/jobs/search?${params}&format=json`);
            const data = await res.json();
            // Re-render handled by server partial or Blade template
            if (data.html) {
                container.innerHTML = data.html;
            }
        } catch (e) {
            container.innerHTML = '<div class="alert alert-danger">Search failed. Please try again.</div>';
        }
    },

    applyFilters() {
        const form = document.getElementById('filterForm');
        if (form) form.submit();
    }
};

/* ==========================================
   CONFIRM DIALOGS
   ========================================== */
const Confirm = {
    show(message, onConfirm) {
        const modal = document.createElement('div');
        modal.style.cssText = `
            position:fixed;inset:0;z-index:9999;display:flex;
            align-items:center;justify-content:center;
            background:rgba(0,0,0,.5);backdrop-filter:blur(4px);
        `;
        modal.innerHTML = `
            <div class="card animate-scale-in" style="width:100%;max-width:400px;margin:1rem">
                <div class="card-body" style="text-align:center">
                    <div style="font-size:2.5rem;margin-bottom:1rem;color:var(--warning)">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h3 style="font-size:1rem;margin-bottom:.5rem;font-weight:700">${window.i18n?.confirmAction || 'Confirm Action'}</h3>
                    <p style="color:var(--text-secondary);font-size:.875rem;margin-bottom:1.5rem">${message}</p>
                    <div style="display:flex;gap:.75rem;justify-content:center">
                        <button class="btn btn-ghost" id="cancelBtn">${window.i18n?.cancel || 'Cancel'}</button>
                        <button class="btn btn-danger" id="confirmBtn">${window.i18n?.confirm || 'Confirm'}</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        document.getElementById('cancelBtn').onclick = () => modal.remove();
        document.getElementById('confirmBtn').onclick = () => { modal.remove(); onConfirm(); };
    }
};

/* ==========================================
   TOOLTIPS
   ========================================== */
const Tooltip = {
    init() {
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.style.position = 'relative';
            el.addEventListener('mouseenter', () => {
                const tip = document.createElement('div');
                tip.className = 'tooltip-bubble';
                tip.textContent = el.dataset.tooltip;
                tip.style.cssText = `
                    position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);
                    background:var(--text-primary);color:var(--bg-card);
                    padding:.375rem .625rem;border-radius:var(--radius-sm);
                    font-size:.75rem;white-space:nowrap;pointer-events:none;z-index:9999;
                    box-shadow:var(--shadow-md);
                `;
                el.appendChild(tip);
            });
            el.addEventListener('mouseleave', () => {
                el.querySelector('.tooltip-bubble')?.remove();
            });
        });
    }
};

/* ==========================================
   TABS
   ========================================== */
const Tabs = {
    init() {
        document.querySelectorAll('[data-tab-container]').forEach(container => {
            container.querySelectorAll('[data-tab]').forEach(tab => {
                tab.addEventListener('click', () => {
                    const target = tab.dataset.tab;
                    container.querySelectorAll('[data-tab]').forEach(t => t.classList.remove('active'));
                    container.querySelectorAll('[data-tab-content]').forEach(c => c.style.display = 'none');
                    tab.classList.add('active');
                    container.querySelector(`[data-tab-content="${target}"]`).style.display = 'block';
                });
            });
        });
    }
};

/* ==========================================
   MOBILE MENU
   ========================================== */
const MobileMenu = {
    init() {
        // Create overlay if not exists
        if (!document.querySelector('.sidebar-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'sidebar-overlay';
            overlay.style.cssText = `
                position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:850;
                opacity:0;visibility:hidden;transition:all 0.3s ease;
            `;
            overlay.style.setProperty('--visible', '0');
            document.body.appendChild(overlay);
        }
    }
};

/* ==========================================
   INITIALIZE ALL
   ========================================== */
document.addEventListener('DOMContentLoaded', () => {
    ThemeManager.init();
    SidebarManager.init();
    NotificationManager.init();
    LanguageManager.init();
    FormManager.init();
    JobSearch.init();
    Tooltip.init();
    Tabs.init();
    MobileMenu.init();

    // Delete buttons with confirmation
    document.querySelectorAll('[data-confirm-delete]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const msg = btn.dataset.confirmDelete || (window.i18n?.deleteConfirm || 'Are you sure you want to delete this?');
            Confirm.show(msg, () => {
                const form = document.getElementById(btn.dataset.formId) || btn.closest('form');
                form?.submit();
            });
        });
    });

    // Close notification panel on outside click handled in NotificationManager
    console.log('✅ Job Platform initialized');
});

// Expose for global use
window.ThemeManager = ThemeManager;
window.NotificationManager = NotificationManager;
window.Confirm = Confirm;