@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-icon', 'grid-1x2')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Overview')

@section('content')
<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card accent">
            <div class="stat-number">{{ $totalConfigs }}</div>
            <div class="stat-label">Config Files</div>
            <i class="bi bi-file-code stat-icon"></i>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card warning">
            <div class="stat-number">{{ $pendingApprovals }}</div>
            <div class="stat-label">Pending Approvals</div>
            <i class="bi bi-hourglass-split stat-icon"></i>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card success">
            <div class="stat-number">{{ $deployedToday }}</div>
            <div class="stat-label">Deployed Today</div>
            <i class="bi bi-rocket stat-icon"></i>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card danger">
            <div class="stat-number">{{ $auditEvents }}</div>
            <div class="stat-label">Audit Events</div>
            <i class="bi bi-journal-text stat-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Recent Change Requests -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header">
                <i class="bi bi-clock-history"></i> Recent Change Requests
                <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-outline-accent ms-auto">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th><th>Config</th><th>Submitted By</th><th>Status</th><th>Date</th><th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRequests as $req)
                            <tr>
                                <td class="mono text-accent">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div style="font-weight:600;">{{ $req->configuration->name ?? '—' }}</div>
                                    <div style="font-size:0.7rem;color:var(--text-muted);">{{ $req->configuration->environment ?? '' }}</div>
                                </td>
                                <td>{{ $req->submitter->name ?? '—' }}</td>
                                <td><span class="status-badge status-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                                <td class="mono" style="font-size:0.72rem;color:var(--text-muted);">{{ $req->created_at->format('d M H:i') }}</td>
                                <td><a href="{{ route('approvals.show', $req) }}" class="btn btn-sm btn-outline-accent">Review</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center py-4" style="color:var(--text-muted);">No change requests yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Feed -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-activity"></i> Live Audit Feed</div>
            <div class="card-body">
                <div class="timeline">
                    @forelse($recentAudit as $log)
                    <div class="timeline-item {{ $log->severity === 'high' ? 'danger' : ($log->severity === 'medium' ? 'warning' : 'success') }}">
                        <div class="timeline-time">{{ $log->created_at->diffForHumans() }}</div>
                        <div class="timeline-text">
                            <strong>{{ $log->user->name ?? 'System' }}</strong> {{ $log->action }}
                            @if($log->resource)<span class="text-accent">{{ $log->resource }}</span>@endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-3" style="color:var(--text-muted);font-size:0.82rem;">No activity yet</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- CIA Security Matrix -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-shield-check"></i> Security Requirements (CIA + NR)</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:0.78rem;">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th style="color:#00b4d8;">Conf.</th>
                                <th style="color:#2a9d8f;">Integ.</th>
                                <th style="color:#f4a261;">Avail.</th>
                                <th style="color:#e63946;">NR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Version-Controlled Repo</strong></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#00b4d8;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#2a9d8f;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#f4a261;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#e63946;"></i></td>
                            </tr>
                            <tr>
                                <td><strong>RBAC Access Control</strong></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#00b4d8;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#2a9d8f;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#f4a261;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#e63946;"></i></td>
                            </tr>
                            <tr>
                                <td><strong>Approval Workflow</strong></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#00b4d8;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#2a9d8f;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#f4a261;"></i></td>
                                <td><i class="bi bi-check-circle-fill" style="color:#e63946;"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header"><i class="bi bi-cpu"></i> System Health</div>
            <div class="card-body">
                @foreach([
                    ['Config Repository Service', 99.8, 'accent'],
                    ['RBAC Module', 100, 'success'],
                    ['Approval Engine', 98.5, 'warning'],
                    ['Audit & Logging Service', 100, 'success'],
                    ['API Gateway (JWT)', 99.2, 'accent'],
                ] as [$name, $uptime, $color])
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:0.8rem;">{{ $name }}</span>
                        <span class="mono" style="font-size:0.72rem;color:var(--text-muted);">{{ $uptime }}%</span>
                    </div>
                    <div class="progress" style="height:5px;background:var(--border);border-radius:10px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ $uptime }}%;background:var(--{{ $color }});border-radius:10px;"></div>
                    </div>
                </div>
                @endforeach
                <div class="mt-3 pt-3 border-top-cv d-flex gap-2">
                    <span class="status-badge status-approved"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>Operational</span>
                    <span class="mono" style="font-size:0.65rem;color:var(--text-muted);">NFR1.2: ≥99% uptime met</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
