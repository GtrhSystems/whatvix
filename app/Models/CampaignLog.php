<?php

namespace App\Models;

use App\Traits\HasModule;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignLog extends Model
{
    use HasFactory, HasModule, HasOwner;

    protected $casts = [
        'meta' => 'array',
    ];

    protected $guarded = [];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isSuccess(): bool
    {
        return boolval($this->send_at);
    }
}
