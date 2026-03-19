@extends('layouts.app')
@section('title', 'Review Change Request #' . str_pad($changeRequest->id, 4, '0', STR_PAD_LEFT))
@section('page-icon', 'check2-circle')
@section('page-title', 'Change Request #' . str_pad($changeRequest->id, 4, '0', STR_PAD_LEFT))
@section('breadcrumb', 'Approvals / Review')

@section('content')
<div class="row g-4">
    <!-- Main Content -->
    <div class="col-xl-8">
        <!-- Change Details -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="bi bi-file-diff"></i> Change Details
                <span class="ms-auto status-badge status-{{ $changeRequest->status }}">{{ ucfirst($changeRequest->status) }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    @foreach([
                        ['Config File', $changeRequest->configuration->name ?? '—', 'file-code'],
                        ['Environment', ucfirst($changeRequest->configuration->environment ?? '—'), 'globe'],
                        ['Submitted By', $changeRequest->submitter->name ?? '—', 'person'],
                        ['Submitted At', $changeRequest->created_at->format('d M Y H:i'), 'clock'],
                    ] as [$label,$value,$icon])
                    <div class="col-6">
                        <div style="background:var(--bg-3);border:1px solid var(--border);border-radius:8px;padding:12px;">
                            <div style="font-size:0.62rem;color:var(--text-muted);letter-spacing:0.1em;text-transform:uppercase;margin-bottom:4px;">
                                <i class="bi bi-{{ $icon }} me-1"></i>{{ $label }}
                            </div>
                            <div style="font-size:0.88rem;color:var(--text);font-weight:600;">{{ $value }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mb-4">
                    <label class="form-label">Change Reason & Impact (FR3.1)</label>
                    <div style="background:var(--bg-3);border:1px solid var(--border);border-radius:8px;padding:14px;font-size:0.84rem;color:var(--text-muted);line-height:1.6;">
                        {{ $changeRequest->change_reason }}
                    </div>
                </div>

                <!-- Diff View -->
                <div>
                    <label class="form-label">Configuration Content</label>
                    <div class="code-block">{{ $changeRequest->configuration->content ?? 'No content preview available' }}</div>
                </div>
            </div>
        </div>

        <!-- Approval Actions -->
        @if(in_array(auth()->user()->role, ['Reviewer','Approver','Admin']))
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-pen"></i> Approval Action — FR3.2, NFR3.1</div>
            <div class="card-body">
                @php
                    $userRole = auth()->user()->role;
                    $canReview = in_array($userRole, ['Reviewer','Admin']) && !$changeRequest->reviewer_approved && !$changeRequest->reviewer_rejected;
                    $canApprove = in_array($userRole, ['Approver','Admin']) && $changeRequest->reviewer_approved && !$changeRequest->approver_approved && !$changeRequest->approver_rejected;
                @endphp

                @if($canReview)
                <div class="alert alert-info mb-3">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Level 1 Review:</strong> As Reviewer, your decision will pass this to an Approver or reject it.
                </div>
                <form method="POST" action="{{ route('approvals.review', $changeRequest) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Review Notes</label>
                        <textarea name="review_notes" rows="3" class="form-control" placeholder="Add your review notes..." required></textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="decision" value="approve" class="btn btn-success">
                            <i class="bi bi-check-circle me-2"></i>Approve (Pass to Approver)
                        </button>
                        <button type="submit" name="decision" value="reject" class="btn btn-danger"
                                onclick="return confirm('Reject this change request?')">
                            <i class="bi bi-x-circle me-2"></i>Reject
                        </button>
                    </div>
                </form>
                @elseif($canApprove)
                <div class="alert alert-warning mb-3">
                    <i class="bi bi-shield-check me-2"></i>
                    <strong>Level 2 Final Approval:</strong> As Approver, your approval will trigger automated deployment (FR3.3).
                </div>
                <form method="POST" action="{{ route('approvals.approve', $changeRequest) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Approval Notes / Digital Sign-off</label>
                        <textarea name="approval_notes" rows="3" class="form-control" placeholder="Add final approval notes..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">MFA Confirmation Code (NFR3.1)</label>
                        <input type="text" name="mfa_code" class="form-control" placeholder="Enter 6-digit MFA code..." maxlength="6" required>
                        <div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">
                            Simulated MFA — enter any 6 digits for demo purposes
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" name="decision" value="approve" class="btn btn-success">
                            <i class="bi bi-rocket me-2"></i>Final Approve & Deploy
                        </button>
                        <button type="submit" name="decision" value="reject" class="btn btn-danger"
                                onclick="return confirm('Reject this change request?')">
                            <i class="bi bi-x-circle me-2"></i>Reject
                        </button>
                    </div>
                </form>
                @else
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    @if($changeRequest->status === 'deployed')
                        This request has been fully approved and deployed.
                    @elseif($changeRequest->status === 'rejected')
                        This request was rejected.
                    @else
                        No action required from your role at this stage.
                    @endif
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Right Panel -->
    <div class="col-xl-4">
        <!-- Workflow Status -->
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-diagram-3"></i> Workflow Status</div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item success">
                        <div class="timeline-time">Step 1 — Submitted</div>
                        <div class="timeline-text">
                            <strong>{{ $changeRequest->submitter->name ?? '—' }}</strong> submitted change request
                            <div style="font-size:0.7rem;color:var(--text-muted);">{{ $changeRequest->created_at->format('d M Y H:i') }}</div>
                        </div>
                    </div>
                    <div class="timeline-item {{ $changeRequest->reviewer_approved ? 'success' : ($changeRequest->reviewer_rejected ? 'danger' : 'warning') }}">
                        <div class="timeline-time">Step 2 — Level 1 Review</div>
                        <div class="timeline-text">
                            @if($changeRequest->reviewer)
                                <strong>{{ $changeRequest->reviewer->name }}</strong>
                                {{ $changeRequest->reviewer_approved ? 'approved' : ($changeRequest->reviewer_rejected ? 'rejected' : 'reviewing') }}
                                @if($changeRequest->review_notes)
                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;background:var(--bg-3);padding:8px;border-radius:6px;">
                                    "{{ $changeRequest->review_notes }}"
                                </div>
                                @endif
                            @else
                                <span style="color:var(--text-muted);">Awaiting Reviewer</span>
                            @endif
                        </div>
                    </div>
                    <div class="timeline-item {{ $changeRequest->approver_approved ? 'success' : ($changeRequest->approver_rejected ? 'danger' : '') }}">
                        <div class="timeline-time">Step 3 — Level 2 Approval</div>
                        <div class="timeline-text">
                            @if($changeRequest->approver)
                                <strong>{{ $changeRequest->approver->name }}</strong>
                                {{ $changeRequest->approver_approved ? 'approved' : ($changeRequest->approver_rejected ? 'rejected' : 'reviewing') }}
                                @if($changeRequest->approval_notes)
                                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;background:var(--bg-3);padding:8px;border-radius:6px;">
                                    "{{ $changeRequest->approval_notes }}"
                                </div>
                                @endif
                            @else
                                <span style="color:var(--text-muted);">{{ $changeRequest->reviewer_approved ? 'Awaiting Approver' : 'Not yet' }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="timeline-item {{ $changeRequest->status === 'deployed' ? 'success' : '' }}">
                        <div class="timeline-time">Step 4 — Auto Deploy (FR3.3)</div>
                        <div class="timeline-text">
                            @if($changeRequest->status === 'deployed')
                                <span style="color:var(--success);">Deployed at {{ $changeRequest->deployed_at?->format('d M Y H:i') ?? '—' }}</span>
                            @else
                                <span style="color:var(--text-muted);">Pending full approval</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Log for this request -->
        <div class="card">
            <div class="card-header"><i class="bi bi-journal"></i> Audit Trail — NFR3.1</div>
            <div class="card-body p-0">
                @forelse($changeRequest->auditLogs ?? [] as $log)
                <div class="p-3 {{ !$loop->first ? 'border-top-cv' : '' }}">
                    <div class="d-flex justify-content-between">
                        <span style="font-size:0.78rem;font-weight:600;">{{ $log->user->name ?? 'System' }}</span>
                        <span class="mono" style="font-size:0.65rem;color:var(--text-muted);">{{ $log->created_at->format('H:i:s') }}</span>
                    </div>
                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $log->action }}</div>
                </div>
                @empty
                <div class="p-3 text-center" style="color:var(--text-muted);font-size:0.8rem;">No audit entries</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
