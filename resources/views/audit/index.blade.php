@extends('layouts.app')
@section('title', 'Audit Logs')
@section('page-icon', 'journal-text')
@section('page-title', 'Audit & Logging Service')
@section('breadcrumb', 'Security / Audit Logs')

@section('content')
<div class="alert alert-info mb-4">
    <i class="bi bi-shield-lock me-2"></i>
    <strong>NFR3.1 + FR2.3:</strong> All logs are immutable (WORM). Entries include user ID, timestamp, action, and are cryptographically chained.
    <strong>NFR1 Repudiation:</strong> Audit trail ensures non-repudiation for all configuration changes.
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    @foreach([
        ['Total Events', $stats['total'] ?? 0, 'accent', 'journal-text'],
        ['High Severity', $stats['high'] ?? 0, 'danger', 'exclamation-triangle'],
        ['Auth Events', $stats['auth'] ?? 0, 'warning', 'shield-lock'],
        ['Config Changes', $stats['config'] ?? 0, 'success', 'file-code'],
    ] as [$label,$count,$color,$icon])
    <div class="col-6 col-xl-3">
        <div class="stat-card {{ $color }}">
            <div class="stat-number">{{ $count }}</div>
            <div class="stat-label">{{ $label }}</div>
            <i class="bi bi-{{ $icon }} stat-icon"></i>
        </div>
    </div>
    @endforeach
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-sm-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All</option>
                    @foreach(['low','medium','high','critical'] as $s)
                    <option value="{{ $s }}" {{ request('severity') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-2">
                <label class="form-label">Event Type</label>
                <select name="event_type" class="form-select form-select-sm">
                    <option value="">All Types</option>
                    @foreach(['auth','config','approval','rbac','deployment'] as $t)
                    <option value="{{ $t }}" {{ request('event_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-sm-3">
                <label class="form-label">Date Range</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
            </div>
            <div class="col-sm-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<!-- Log Table -->
<div class="card">
    <div class="card-header">
        <i class="bi bi-list-ul"></i> Audit Log Entries
        <span class="ms-2 mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $logs->total() }} records</span>
        <div class="ms-auto d-flex gap-2 align-items-center">
            <span class="status-badge status-approved">
                <i class="bi bi-lock-fill me-1"></i>WORM Storage — Immutable
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Event Type</th>
                        <th>Action</th>
                        <th>Resource</th>
                        <th>IP Address</th>
                        <th>Severity</th>
                        <th>Hash</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="mono" style="font-size:0.7rem;color:var(--text-muted);white-space:nowrap;">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td>
                            <div style="font-size:0.82rem;font-weight:600;">{{ $log->user->name ?? 'System' }}</div>
                            <div class="mono" style="font-size:0.65rem;color:var(--text-muted);">ID:{{ $log->user_id ?? '—' }}</div>
                        </td>
                        <td>
                            <span class="role-badge badge-{{ strtolower($log->user->role ?? 'developer') }}" style="font-size:0.62rem;padding:2px 7px;border-radius:10px;font-family:'JetBrains Mono',monospace;">
                                {{ $log->user->role ?? '—' }}
                            </span>
                        </td>
                        <td>
                            <span class="mono" style="font-size:0.72rem;color:var(--accent);">{{ strtoupper($log->event_type ?? '—') }}</span>
                        </td>
                        <td style="font-size:0.8rem;max-width:200px;">{{ $log->action }}</td>
                        <td class="mono" style="font-size:0.72rem;color:var(--text-muted);">{{ $log->resource ?? '—' }}</td>
                        <td class="mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $log->ip_address ?? '—' }}</td>
                        <td>
                            @php
                                $sevColors = ['low'=>'status-approved','medium'=>'status-pending','high'=>'status-rejected','critical'=>'status-rejected'];
                                $sev = $log->severity ?? 'low';
                            @endphp
                            <span class="status-badge {{ $sevColors[$sev] ?? 'status-draft' }}">{{ ucfirst($sev) }}</span>
                        </td>
                        <td>
                            <span class="mono" title="{{ $log->log_hash }}" style="font-size:0.65rem;color:var(--text-dim);">
                                {{ substr($log->log_hash ?? 'n/a', 0, 8) }}…
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-journal" style="font-size:2rem;display:block;margin-bottom:12px;"></i>
                            No audit log entries found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($logs->hasPages())
    <div class="card-body border-top-cv py-3">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
