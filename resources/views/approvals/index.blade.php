@extends('layouts.app')
@section('title', 'Approval Queue')
@section('page-icon', 'check2-circle')
@section('page-title', 'Approval Queue')
@section('breadcrumb', 'Workflow / Approvals')

@section('content')
<!-- Workflow Info -->
<div class="alert alert-info mb-4">
    <i class="bi bi-diagram-3 me-2"></i>
    <strong>FR3.2:</strong> Workflow requires two levels of approval — <strong>Reviewer</strong> (Level 1) then <strong>Approver</strong> (Level 2) — before any configuration is deployed.
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    @foreach([
        ['Pending Review', $counts['pending'] ?? 0, 'warning', 'hourglass-split'],
        ['Under Review', $counts['reviewing'] ?? 0, 'accent', 'eye'],
        ['Approved', $counts['approved'] ?? 0, 'success', 'check-circle'],
        ['Deployed', $counts['deployed'] ?? 0, 'accent', 'rocket'],
    ] as [$label, $count, $color, $icon])
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
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-end">
            <div>
                <label class="form-label">Status</label>
                <select name="status" class="form-select form-select-sm" style="min-width:160px;">
                    <option value="">All Status</option>
                    @foreach(['pending','reviewing','approved','rejected','deployed'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">My Role Action</label>
                <select name="my_action" class="form-select form-select-sm" style="min-width:160px;">
                    <option value="">All Requests</option>
                    <option value="action_needed" {{ request('my_action') === 'action_needed' ? 'selected' : '' }}>Action Needed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
            <a href="{{ route('approvals.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
        </form>
    </div>
</div>

<!-- Requests Table -->
<div class="card">
    <div class="card-header"><i class="bi bi-list-check"></i> Change Requests</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Configuration</th>
                        <th>Environment</th>
                        <th>Submitted By</th>
                        <th>Reviewer (L1)</th>
                        <th>Approver (L2)</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        <td class="mono text-accent">#{{ str_pad($req->id, 4, '0', STR_PAD_LEFT) }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $req->configuration->name ?? '—' }}</div>
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ Str::limit($req->change_reason, 40) }}</div>
                        </td>
                        <td>
                            <span class="mono" style="font-size:0.75rem;">{{ ucfirst($req->configuration->environment ?? '—') }}</span>
                        </td>
                        <td style="font-size:0.82rem;">{{ $req->submitter->name ?? '—' }}</td>
                        <td>
                            @if($req->reviewer)
                                <div style="font-size:0.78rem;">{{ $req->reviewer->name }}</div>
                                <span class="status-badge {{ $req->reviewer_approved ? 'status-approved' : ($req->reviewer_rejected ? 'status-rejected' : 'status-pending') }}" style="font-size:0.58rem;">
                                    {{ $req->reviewer_approved ? 'Approved' : ($req->reviewer_rejected ? 'Rejected' : 'Pending') }}
                                </span>
                            @else
                                <span style="color:var(--text-muted);font-size:0.78rem;">Awaiting</span>
                            @endif
                        </td>
                        <td>
                            @if($req->approver)
                                <div style="font-size:0.78rem;">{{ $req->approver->name }}</div>
                                <span class="status-badge {{ $req->approver_approved ? 'status-approved' : ($req->approver_rejected ? 'status-rejected' : 'status-pending') }}" style="font-size:0.58rem;">
                                    {{ $req->approver_approved ? 'Approved' : ($req->approver_rejected ? 'Rejected' : 'Pending') }}
                                </span>
                            @else
                                <span style="color:var(--text-muted);font-size:0.78rem;">—</span>
                            @endif
                        </td>
                        <td><span class="status-badge status-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                        <td class="mono" style="font-size:0.7rem;color:var(--text-muted);">{{ $req->created_at->format('d M H:i') }}</td>
                        <td>
                            <a href="{{ route('approvals.show', $req) }}" class="btn btn-sm btn-outline-accent">
                                <i class="bi bi-eye me-1"></i>Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5" style="color:var(--text-muted);">
                            <i class="bi bi-check2-circle" style="font-size:2rem;display:block;margin-bottom:12px;"></i>
                            No change requests found
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($requests->hasPages())
    <div class="card-body border-top-cv py-3">{{ $requests->links() }}</div>
    @endif
</div>
@endsection
