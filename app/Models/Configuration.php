<?php
// ══════════════════════════════════════════════
//  app/Models/Configuration.php
// ══════════════════════════════════════════════
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Configuration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name','description','type','environment','content',
        'status','version','created_by','last_modified_by',
    ];

    public function creator()       { return $this->belongsTo(User::class, 'created_by'); }
    public function lastModifier()  { return $this->belongsTo(User::class, 'last_modified_by'); }
    public function changeRequests(){ return $this->hasMany(ChangeRequest::class); }
    public function versions()      { return $this->hasMany(ConfigVersion::class)->orderBy('version_number','desc'); }
    public function auditLogs()     { return $this->hasMany(AuditLog::class, 'resource_id')->where('event_type','config'); }

    public function incrementVersion()
    {
        $parts = explode('.', $this->version);
        $parts[count($parts)-1]++;
        $this->version = implode('.', $parts);
        return $this;
    }
}
