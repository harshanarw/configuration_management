<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{ChangeRequest, AuditLog};

class ApprovalController extends Controller
{
    public function index(Request $request)
    {
        $query = ChangeRequest::with(['configuration','submitter','reviewer','approver']);

        if ($request->status)    $query->where('status', $request->status);
        if ($request->my_action === 'action_needed') {
            $user = auth()->user();
            $query->where(function($q) use ($user) {
                if ($user->isReviewer()) {
                    $q->orWhere(fn($s) => $s->where('status','pending')->whereNull('reviewer_id'));
                }
                if ($user->isApprover()) {
                    $q->orWhere(fn($s) => $s->where('reviewer_approved', true)->where('approver_approved', false)->where('approver_rejected', false));
                }
            });
        }

        $counts = [
            'pending'   => ChangeRequest::where('status','pending')->count(),
            'reviewing' => ChangeRequest::where('status','reviewing')->count(),
            'approved'  => ChangeRequest::where('status','approved')->count(),
            'deployed'  => ChangeRequest::where('status','deployed')->count(),
        ];

        return view('approvals.index', [
            'requests' => $query->latest()->paginate(15),
            'counts'   => $counts,
        ]);
    }

    public function show(ChangeRequest $changeRequest)
    {
        $changeRequest->load(['configuration','submitter','reviewer','approver','auditLogs.user']);
        return view('approvals.show', ['request' => $changeRequest]);
    }

    /** Level 1 — Reviewer action — FR3.2 */
    public function review(Request $request, ChangeRequest $changeRequest)
    {
        $this->authorize('review', $changeRequest);

        $request->validate([
            'decision'     => 'required|in:approve,reject',
            'review_notes' => 'required|string|min:5',
        ]);

        $approve = $request->decision === 'approve';

        $changeRequest->update([
            'reviewer_id'       => auth()->id(),
            'review_notes'      => $request->review_notes,
            'reviewer_approved' => $approve,
            'reviewer_rejected' => !$approve,
            'status'            => $approve ? 'reviewing' : 'rejected',
        ]);

        AuditLog::record(
            'Reviewer '.($approve ? 'approved' : 'rejected').' change request #'.$changeRequest->id,
            'approval',
            $approve ? 'medium' : 'high',
            'ChangeRequest',
            $changeRequest->id
        );

        $msg = $approve
            ? 'Change request approved. Now awaiting final Approver sign-off.'
            : 'Change request rejected.';

        return redirect()->route('approvals.show', $changeRequest)->with('success', $msg);
    }

    /** Level 2 — Approver action + auto-deploy — FR3.2, FR3.3 */
    public function approve(Request $request, ChangeRequest $changeRequest)
    {
        $this->authorize('approve', $changeRequest);

        $request->validate([
            'decision'       => 'required|in:approve,reject',
            'approval_notes' => 'required|string|min:5',
            'mfa_code'       => 'required|digits:6',  // NFR3.1 MFA simulation
        ]);

        $approve = $request->decision === 'approve';

        $changeRequest->update([
            'approver_id'       => auth()->id(),
            'approval_notes'    => $request->approval_notes,
            'approver_approved' => $approve,
            'approver_rejected' => !$approve,
            'mfa_verified'      => true,
            'status'            => $approve ? 'approved' : 'rejected',
        ]);

        if ($approve) {
            // Auto-deploy — FR3.3
            $this->triggerDeployment($changeRequest);
        }

        AuditLog::record(
            'Approver '.($approve ? 'approved and triggered deployment for' : 'rejected').' change request #'.$changeRequest->id,
            'approval',
            $approve ? 'high' : 'high',
            'ChangeRequest',
            $changeRequest->id,
            ['mfa_verified' => true]
        );

        $msg = $approve
            ? 'Change request fully approved. Automated deployment triggered! (FR3.3)'
            : 'Change request rejected.';

        return redirect()->route('approvals.show', $changeRequest)->with('success', $msg);
    }

    /** Simulate automated deployment — FR3.3 */
    private function triggerDeployment(ChangeRequest $changeRequest): void
    {
        $changeRequest->update([
            'status'      => 'deployed',
            'deployed_at' => now(),
        ]);

        // Mark config as active
        $changeRequest->configuration->update(['status' => 'active']);

        AuditLog::record(
            'Automated deployment executed for config: '.$changeRequest->configuration->name,
            'deployment', 'high',
            $changeRequest->configuration->name,
            $changeRequest->configuration_id
        );
    }
}
