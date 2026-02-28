<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
   

    public $timestamps = false;

    public $primaryKey = 'pkId';

    protected $table = 'chat';

    protected $guarded = [];

    protected $casts = [
        'wlc_mgs_send_at' => 'datetime',
    ];

    public function isAutoReplyEnabled(): bool
    {
        return $this->auto_reply_enabled;
    }
}
