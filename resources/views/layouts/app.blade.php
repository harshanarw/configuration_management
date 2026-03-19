<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ConfigVault') — Configuration Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=JetBrains+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:      #0f4c75;
            --primary-light:#1b6ca8;
            --accent:       #00b4d8;
            --accent-glow:  rgba(0,180,216,0.18);
            --danger:       #e63946;
            --warning:      #f4a261;
            --success:      #2a9d8f;
            --bg:           #070d1a;
            --bg-2:         #0d1626;
            --bg-3:         #111e33;
            --surface:      #152035;
            --surface-2:    #1c2a42;
            --border:       #1e3050;
            --border-light: #243858;
            --text:         #cdd9ec;
            --text-muted:   #6b82a4;
            --text-dim:     #2d4060;
            --sidebar-w:    260px;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Syne', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Sidebar ─────────────────────────────── */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--bg-2);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar-brand {
            padding: 24px 20px 20px;
            border-bottom: 1px solid var(--border);
        }
        .sidebar-brand .brand-icon {
            width: 40px; height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; color: #fff;
            margin-bottom: 10px;
            box-shadow: 0 0 20px var(--accent-glow);
        }
        .sidebar-brand h1 {
            font-size: 1.1rem; font-weight: 800;
            color: #fff; margin: 0; letter-spacing: -0.02em;
        }
        .sidebar-brand p {
            font-size: 0.62rem; color: var(--text-muted);
            margin: 2px 0 0; letter-spacing: 0.1em; text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
        }

        .sidebar-user {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 10px;
        }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-light), var(--accent));
            display: flex; align-items: center; justify-content: center;
            font-size: 0.8rem; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .user-info .name { font-size: 0.82rem; font-weight: 600; color: var(--text); }
        .user-info .role-badge {
            font-size: 0.58rem; padding: 1px 7px; border-radius: 10px;
            font-family: 'JetBrains Mono', monospace; letter-spacing: 0.05em;
            font-weight: 500; text-transform: uppercase;
        }
        .badge-admin    { background: rgba(230,57,70,0.2);  color: #e63946; }
        .badge-developer{ background: rgba(0,180,216,0.15); color: var(--accent); }
        .badge-reviewer { background: rgba(42,157,143,0.2); color: var(--success); }
        .badge-approver { background: rgba(244,162,97,0.2); color: var(--warning); }
        .badge-auditor  { background: rgba(107,130,164,0.2);color: var(--text-muted); }

        .sidebar-nav { padding: 16px 12px; flex: 1; }
        .nav-section-title {
            font-size: 0.58rem; letter-spacing: 0.15em; text-transform: uppercase;
            color: var(--text-dim); padding: 10px 8px 6px;
            font-family: 'JetBrains Mono', monospace;
        }
        .nav-link {
            display: flex; align-items: center; gap: 10px;
            padding: 9px 12px; border-radius: 8px; margin-bottom: 2px;
            color: var(--text-muted); font-size: 0.85rem; font-weight: 500;
            text-decoration: none; transition: all 0.2s;
        }
        .nav-link i { font-size: 1rem; width: 20px; text-align: center; flex-shrink: 0; }
        .nav-link:hover { background: var(--surface); color: var(--text); }
        .nav-link.active {
            background: linear-gradient(90deg, var(--accent-glow), transparent);
            color: var(--accent);
            border-left: 2px solid var(--accent);
        }
        .nav-link .badge-count {
            margin-left: auto; font-size: 0.62rem;
            background: var(--danger); color: #fff;
            border-radius: 10px; padding: 1px 6px;
            font-family: 'JetBrains Mono', monospace;
        }

        .sidebar-footer {
            padding: 14px 12px;
            border-top: 1px solid var(--border);
        }

        /* ── Main Content ────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex; flex-direction: column;
        }

        .topbar {
            background: var(--bg-2);
            border-bottom: 1px solid var(--border);
            padding: 14px 32px;
            display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .page-title {
            font-size: 1.1rem; font-weight: 700; color: #fff;
            display: flex; align-items: center; gap: 10px;
        }
        .topbar .page-title i { color: var(--accent); }
        .breadcrumb-bar {
            font-size: 0.7rem; color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
            margin-top: 2px;
        }

        .content-area { padding: 28px 32px; flex: 1; }

        /* ── Cards ───────────────────────────────── */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
        }
        .card-header {
            background: var(--surface-2);
            border-bottom: 1px solid var(--border);
            padding: 14px 20px;
            font-weight: 600; font-size: 0.88rem;
            display: flex; align-items: center; gap: 8px;
        }
        .card-header i { color: var(--accent); }
        .card-body { padding: 20px; }

        /* ── Stat Cards ──────────────────────────── */
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            position: relative; overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,0.3); }
        .stat-card::before {
            content: ''; position: absolute;
            top: 0; left: 0; right: 0; height: 2px;
        }
        .stat-card.accent::before  { background: linear-gradient(90deg, var(--accent), transparent); }
        .stat-card.success::before { background: linear-gradient(90deg, var(--success), transparent); }
        .stat-card.warning::before { background: linear-gradient(90deg, var(--warning), transparent); }
        .stat-card.danger::before  { background: linear-gradient(90deg, var(--danger), transparent); }
        .stat-number { font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; }
        .stat-label  { font-size: 0.72rem; color: var(--text-muted); letter-spacing: 0.08em; text-transform: uppercase; margin-top: 4px; }
        .stat-icon   { font-size: 2rem; opacity: 0.15; position: absolute; right: 16px; top: 16px; }

        /* ── Tables ──────────────────────────────── */
        .table { color: var(--text); }
        .table th {
            font-size: 0.68rem; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--text-muted); border-color: var(--border);
            background: var(--surface-2); font-weight: 600; padding: 12px 16px;
            font-family: 'JetBrains Mono', monospace;
        }
        .table td { border-color: var(--border); padding: 12px 16px; font-size: 0.84rem; vertical-align: middle; }
        .table tbody tr { transition: background 0.15s; }
        .table tbody tr:hover { background: rgba(255,255,255,0.03); }
        .table-striped > tbody > tr:nth-of-type(odd) > * { background: rgba(255,255,255,0.02); }

        /* ── Buttons ─────────────────────────────── */
        .btn { font-family: 'Syne', sans-serif; font-weight: 600; border-radius: 8px; font-size: 0.82rem; }
        .btn-primary { background: var(--primary-light); border-color: var(--primary-light); }
        .btn-primary:hover { background: var(--accent); border-color: var(--accent); }
        .btn-accent { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn-accent:hover { background: #00c8f0; border-color: #00c8f0; color: #fff; }
        .btn-outline-accent { border-color: var(--accent); color: var(--accent); background: transparent; }
        .btn-outline-accent:hover { background: var(--accent); color: #fff; }
        .btn-sm { font-size: 0.75rem; padding: 4px 10px; }

        /* ── Badges / Status ─────────────────────── */
        .status-badge {
            font-size: 0.65rem; padding: 3px 10px; border-radius: 20px;
            font-family: 'JetBrains Mono', monospace; font-weight: 500; letter-spacing: 0.05em;
        }
        .status-pending   { background: rgba(244,162,97,0.15); color: var(--warning); border: 1px solid rgba(244,162,97,0.3); }
        .status-approved  { background: rgba(42,157,143,0.15); color: var(--success); border: 1px solid rgba(42,157,143,0.3); }
        .status-rejected  { background: rgba(230,57,70,0.15);  color: var(--danger);  border: 1px solid rgba(230,57,70,0.3); }
        .status-deployed  { background: rgba(0,180,216,0.12);  color: var(--accent);  border: 1px solid rgba(0,180,216,0.3); }
        .status-draft     { background: rgba(107,130,164,0.15);color: var(--text-muted); border: 1px solid var(--border-light); }

        /* ── Forms ───────────────────────────────── */
        .form-control, .form-select {
            background: var(--bg-3); border-color: var(--border);
            color: var(--text); border-radius: 8px; font-size: 0.84rem;
        }
        .form-control:focus, .form-select:focus {
            background: var(--bg-3); border-color: var(--accent);
            color: var(--text); box-shadow: 0 0 0 3px var(--accent-glow);
        }
        .form-label { font-size: 0.78rem; font-weight: 600; color: var(--text-muted); letter-spacing: 0.05em; margin-bottom: 6px; }
        textarea.form-control { font-family: 'JetBrains Mono', monospace; font-size: 0.8rem; }

        /* ── Alerts ──────────────────────────────── */
        .alert { border-radius: 10px; border: none; font-size: 0.84rem; }
        .alert-success { background: rgba(42,157,143,0.15); color: #4ecdc4; border: 1px solid rgba(42,157,143,0.3); }
        .alert-danger  { background: rgba(230,57,70,0.15);  color: #ff6b7a; border: 1px solid rgba(230,57,70,0.3); }
        .alert-warning { background: rgba(244,162,97,0.15); color: var(--warning); border: 1px solid rgba(244,162,97,0.3); }
        .alert-info    { background: rgba(0,180,216,0.12);  color: var(--accent);  border: 1px solid rgba(0,180,216,0.3); }

        /* ── Misc ────────────────────────────────── */
        .mono { font-family: 'JetBrains Mono', monospace; }
        .text-accent { color: var(--accent) !important; }
        .text-success-cv { color: var(--success) !important; }
        .border-top-cv { border-top: 1px solid var(--border) !important; }
        .section-divider { border-color: var(--border); margin: 20px 0; }

        /* Code block */
        .code-block {
            background: var(--bg); border: 1px solid var(--border);
            border-radius: 8px; padding: 14px 16px;
            font-family: 'JetBrains Mono', monospace; font-size: 0.78rem;
            color: var(--accent); overflow-x: auto; white-space: pre;
        }

        /* Timeline */
        .timeline { position: relative; padding-left: 28px; }
        .timeline::before { content: ''; position: absolute; left: 8px; top: 0; bottom: 0; width: 1px; background: var(--border); }
        .timeline-item { position: relative; margin-bottom: 20px; }
        .timeline-item::before {
            content: ''; position: absolute; left: -24px; top: 5px;
            width: 10px; height: 10px; border-radius: 50%;
            background: var(--accent); border: 2px solid var(--bg-2);
        }
        .timeline-item.success::before { background: var(--success); }
        .timeline-item.warning::before { background: var(--warning); }
        .timeline-item.danger::before  { background: var(--danger); }
        .timeline-time { font-size: 0.65rem; color: var(--text-muted); font-family: 'JetBrains Mono', monospace; }
        .timeline-text { font-size: 0.82rem; color: var(--text); margin-top: 2px; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        /* Modal */
        .modal-content { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; color: var(--text); }
        .modal-header { border-bottom: 1px solid var(--border); padding: 16px 20px; }
        .modal-footer { border-top: 1px solid var(--border); }
        .btn-close { filter: invert(1) opacity(0.5); }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #main-content { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<!-- SIDEBAR -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-shield-lock-fill"></i></div>
        <h1>ConfigVault</h1>
        <p>Configuration Management System</p>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
        <div class="user-info">
            <div class="name">{{ auth()->user()->name ?? 'Guest' }}</div>
            <span class="role-badge badge-{{ strtolower(auth()->user()->role ?? 'developer') }}">
                {{ auth()->user()->role ?? 'Developer' }}
            </span>
        </div>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-title">Overview</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a>

        <div class="nav-section-title">Configuration</div>
        <a href="{{ route('configurations.index') }}" class="nav-link {{ request()->routeIs('configurations.*') ? 'active' : '' }}">
            <i class="bi bi-file-code"></i> Config Repository
        </a>
        <a href="{{ route('configurations.create') }}" class="nav-link {{ request()->routeIs('configurations.create') ? 'active' : '' }}">
            <i class="bi bi-plus-square"></i> New Config
        </a>

        <div class="nav-section-title">Workflow</div>
        <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
            <i class="bi bi-check2-circle"></i> Approval Queue
            @php $pending = \App\Models\ChangeRequest::where('status','pending')->count(); @endphp
            @if($pending > 0)<span class="badge-count">{{ $pending }}</span>@endif
        </a>

        <div class="nav-section-title">Security</div>
        <a href="{{ route('audit.index') }}" class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
            <i class="bi bi-journal-text"></i> Audit Logs
        </a>
        <a href="{{ route('threats.index') }}" class="nav-link {{ request()->routeIs('threats.*') ? 'active' : '' }}">
            <i class="bi bi-shield-exclamation"></i> Threat Model
        </a>

        @if(auth()->user()->role === 'Admin')
        <div class="nav-section-title">Administration</div>
        <a href="{{ route('rbac.index') }}" class="nav-link {{ request()->routeIs('rbac.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> RBAC Management
        </a>
        @endif
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-link w-100 border-0" style="background:none;cursor:pointer;">
                <i class="bi bi-box-arrow-left"></i> Sign Out
            </button>
        </form>
    </div>
</nav>

<!-- MAIN CONTENT -->
<div id="main-content">
    <div class="topbar">
        <div>
            <div class="page-title">
                <i class="bi bi-@yield('page-icon', 'grid-1x2')"></i>
                @yield('page-title', 'Dashboard')
            </div>
            <div class="breadcrumb-bar">ConfigVault / @yield('breadcrumb', 'Dashboard')</div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="mono" style="font-size:0.68rem;color:var(--text-muted);">
                <i class="bi bi-clock me-1"></i>{{ now()->format('D, d M Y H:i') }}
            </span>
            @if(auth()->user()->role === 'Admin')
            <span class="status-badge status-approved"><i class="bi bi-shield-check me-1"></i>Admin</span>
            @endif
        </div>
    </div>

    <div class="content-area">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
