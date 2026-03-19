<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'user_id','event_type','action','resource','resource_id',
        'ip_address','user_agent','severity','log_hash','metadata','created_at',
    ];
    protected $casts = ['metadata'=>'array','created_at'=>'datetime'];

    public function user() { return $this->belongsTo(User::class); }

    /**
     * Write an immutable audit log entry — NFR3.1, FR2.3
     * Hash chains the previous log entry for non-repudiation
     */
    public static function record(
        string $action,
        string $eventType = 'general',
        string $severity  = 'low',
        ?string $resource  = null,
        ?int $resourceId  = null,
        array $metadata   = []
    ): self {
        $lastHash = self::latest('created_at')->value('log_hash') ?? '0';

        $entry = self::create([
            'user_id'     => auth()->id(),
            'event_type'  => $eventType,
            'action'      => $action,
            'resource'    => $resource,
            'resource_id' => $resourceId,
            'ip_address'  => request()->ip(),
            'user_agent'  => substr(request()->userAgent() ?? '', 0, 255),
            'severity'    => $severity,
            'metadata'    => $metadata,
            'created_at'  => now(),
            'log_hash'    => '', // placeholder
        ]);

        // Cryptographic hash chain — NFR for non-repudiation
        $entry->log_hash = hash('sha256', $lastHash . $entry->id . $action . now()->toISOString());
        $entry->save();

        return $entry;
    }
}
