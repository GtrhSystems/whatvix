<?php

namespace Modules\Whatsapp\App\Models;

use App\Models\User;
use App\Traits\UUID;
use App\Models\Platform;
use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CloudApp extends Model
{
    use HasFactory, UUID, HasFilter, HasActivityLog;

    protected $fillable = ['platform_id', 'name', 'site_link', 'user_id', 'uuid', 'key'];

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logs()
    {
        return $this->hasMany(CloudAppLog::class, 'app_id');
    }
}
