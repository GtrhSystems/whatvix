<?php

namespace Modules\Whatsapp\App\Models;

use App\Models\Platform;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;

class CloudAppLog extends Model
{
    use HasOwner;

    protected $table = 'whatsapp_cloud_app_logs';

    protected $fillable = [
        'owner_id',
        'platform_id',
        'app_id',
        'to',
        'status_code',
        'request',
        'response'
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];

    public function app()
    {
        return $this->belongsTo(CloudApp::class, 'app_id');
    }

    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }
}
