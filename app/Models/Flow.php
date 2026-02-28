<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Platform;
use App\Traits\HasFilter;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Flow extends Model
{
    use HasFactory, HasFilter;

    public function defineHasOwner()
    {
        return ['column' => 'user_id',];
    }

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array',
        'flow_data' => 'array',
        'flow_raw_data' => 'array'
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'device_id');
    }
}
