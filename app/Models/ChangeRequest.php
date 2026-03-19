<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChangeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'configuration_id','submitted_by','reviewer_id','approver_id',
        'change_reason','review_notes','approval_notes',
        'status','reviewer_approved','reviewer_rejected','approver_approved','approver_rejected',
        'mfa_verified','deployed_at',
    ];

    protected $casts = [
        'reviewer_approved' => 'boolean',
        'reviewer_rejected' => 'boolean',
        'approver_approved' => 'boolean',
        'approver_rejected' => 'boolean',
        'mfa_verified'      => 'boolean',
        'deployed_at'       => 'datetime',
    ];

    public function configuration() { return $this->belongsTo(Configuration::class); }
    public function submitter()     { return $this->belongsTo(User::class, 'submitted_by'); }
    public function reviewer()      { return $this->belongsTo(User::class, 'reviewer_id'); }
    public function approver()      { return $this->belongsTo(User::class, 'approver_id'); }
    public function auditLogs()     { return $this->hasMany(AuditLog::class, 'resource_id')->whereIn('event_type', ['approval', 'deployment']); }

    public function isPending()    { return $this->status === 'pending'; }
    public function isApproved()   { return $this->status === 'approved'; }
    public function isDeployed()   { return $this->status === 'deployed'; }
    public function isRejected()   { return $this->status === 'rejected'; }
}
