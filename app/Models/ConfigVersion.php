<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigVersion extends Model
{
    protected $fillable = ['configuration_id','user_id','version_number','content','change_reason'];

    public function configuration() { return $this->belongsTo(Configuration::class); }
    public function user()          { return $this->belongsTo(User::class); }
}
