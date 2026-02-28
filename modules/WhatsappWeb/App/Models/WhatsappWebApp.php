<?php

namespace Modules\WhatsappWeb\App\Models;

use App\Models\User;
use App\Traits\UUID;
use App\Models\Platform;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WhatsappWebApp extends Model
{
    use HasFactory, UUID, HasActivityLog;

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
        return $this->hasMany(WhatsappWebAppLog::class, 'app_id');
    }
}
