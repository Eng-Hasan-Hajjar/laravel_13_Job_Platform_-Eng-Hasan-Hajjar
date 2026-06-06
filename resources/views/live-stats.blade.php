@extends('layouts.app')
@section('title', 'Live Statistics')

@section('content')
<div class="page-container">
    <div class="card" style="background:linear-gradient(135deg,#10b981,#059669);border:none;color:white;margin-bottom:2rem">
        <div class="card-body" style="padding:2rem;text-align:center">
            <div style="font-size:3rem;margin-bottom:.5rem">⚡</div>
            <h1 style="font-size:1.75rem;font-weight:800;margin-bottom:.5rem">{{ __('messages.live_statistics') }}</h1>
            <p style="opacity:.9">{{ __('messages.powered_by_nodejs') }} <strong>Node.js + Socket.io</strong></p>
            <div id="connection-status" style="margin-top:1rem;display:inline-block;padding:.375rem 1rem;background:rgba(255,255,255,.2);border-radius:var(--radius-full);font-size:.875rem">
                🔴 Connecting...
            </div>
        </div>
    </div>

    <div class="grid grid-4" style="margin-bottom:2rem">
        <div class="stat-card primary"><div class="stat-card-icon"><i class="fas fa-briefcase"></i></div>
            <div><div class="stat-value" id="stat-jobs">0</div><div class="stat-label">Active Jobs</div></div>
        </div>
        <div class="stat-card success"><div class="stat-card-icon"><i class="fas fa-users"></i></div>
            <div><div class="stat-value" id="stat-users">0</div><div class="stat-label">Job Seekers</div></div>
        </div>
        <div class="stat-card warning"><div class="stat-card-icon"><i class="fas fa-building"></i></div>
            <div><div class="stat-value" id="stat-companies">0</div><div class="stat-label">Companies</div></div>
        </div>
        <div class="stat-card info"><div class="stat-card-icon"><i class="fas fa-file-alt"></i></div>
            <div><div class="stat-value" id="stat-applications">0</div><div class="stat-label">Applications</div></div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">🚀 Recent Jobs (Live)</span>
            <span style="font-size:.75rem;color:var(--text-muted)" id="last-update">—</span>
        </div>
        <div class="card-body" id="recent-jobs">
            <div style="text-align:center;padding:2rem;color:var(--text-muted)">Loading...</div>
        </div>
    </div>
</div>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    const socket = io('http://localhost:3001');
    const status = document.getElementById('connection-status');

    socket.on('connect', () => {
        status.innerHTML = '🟢 Connected to Node.js Server';
        status.style.background = 'rgba(255,255,255,.3)';
    });

    socket.on('disconnect', () => {
        status.innerHTML = '🔴 Disconnected';
    });

    socket.on('stats', (data) => {
        animateNumber('stat-jobs',         data.jobs);
        animateNumber('stat-users',        data.users);
        animateNumber('stat-companies',    data.companies);
        animateNumber('stat-applications', data.applications);

        document.getElementById('last-update').textContent =
            'Last update: ' + new Date(data.timestamp).toLocaleTimeString();

        document.getElementById('recent-jobs').innerHTML = data.recentJobs
            .map(j => `<div style="padding:.75rem 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between">
                <strong>${j.title}</strong>
                <span style="color:var(--text-muted);font-size:.8rem">${new Date(j.created_at).toLocaleString()}</span>
            </div>`).join('');
    });

    function animateNumber(id, target) {
        const el = document.getElementById(id);
        const current = parseInt(el.textContent) || 0;
        const step = Math.max(1, Math.ceil((target - current) / 20));
        let value = current;
        const t = setInterval(() => {
            value += step;
            if ((step > 0 && value >= target) || (step < 0 && value <= target)) {
                value = target;
                clearInterval(t);
            }
            el.textContent = value;
        }, 30);
    }
</script>
@endsection