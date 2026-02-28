<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use App\Traits\HasModule;
use App\Traits\HasOwner;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Template extends Model
{
    use HasFactory, HasStatus, HasModule, HasOwner, HasActivityLog, HasFilter;

    /**
     * The attributes that are mass assignable.
     */
    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'array',
    ];

    public function schedule_senders(): HasMany
    {
        return $this->hasMany(Campaign::class, 'template_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * Return the meta data from the template.
     *
     * @param string|null $key
     * @return array|null
     */
    public function getMeta(?string $key = null): ?array
    {
        if ($key)
            return data_get($this->meta, $key);

        return $this->meta;
    }
}
