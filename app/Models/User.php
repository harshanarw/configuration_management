<?php
// ══════════════════════════════════════════════
//  app/Models/User.php
// ══════════════════════════════════════════════
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name','email','password','role','is_active','last_login_at'];
    protected $hidden   = ['password','remember_token'];
    protected $casts    = ['last_login_at'=>'datetime','is_active'=>'boolean'];

    public function configurations()      { return $this->hasMany(Configuration::class, 'created_by'); }
    public function submittedRequests()   { return $this->hasMany(ChangeRequest::class, 'submitted_by'); }
    public function reviewedRequests()    { return $this->hasMany(ChangeRequest::class, 'reviewer_id'); }
    public function approvedRequests()    { return $this->hasMany(ChangeRequest::class, 'approver_id'); }
    public function auditLogs()           { return $this->hasMany(AuditLog::class); }

    public function isAdmin()     { return $this->role === 'Admin'; }
    public function isReviewer()  { return in_array($this->role, ['Reviewer','Admin']); }
    public function isApprover()  { return in_array($this->role, ['Approver','Admin']); }
    public function isDeveloper() { return in_array($this->role, ['Developer','Admin']); }
    public function isAuditor()   { return $this->role === 'Auditor'; }
}
