<?php

namespace App\Models;

use App\Models\User;
use App\Traits\HasActivityLog;
use App\Traits\HasModule;
use App\Traits\HasOwner;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuickReply extends Model
{
    use HasFactory, HasStatus, HasOwner, HasModule, HasActivityLog;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owner_id',
        'platform_id',
        'module',
        'message_template',
        'status',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

}
