<?php

namespace App\Models;

use App\Traits\HasOwner;
use App\Traits\HasModule;
use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory, HasModule, HasOwner, HasActivityLog, HasFilter;

    public static $STATUS_DRAFT = 'draft';

    public static $STATUS_PENDING = 'pending';

    public static $STATUS_SCHEDULED = 'scheduled';

    public static $STATUS_SEND = 'send';

    protected $guarded = ['id'];

    protected $casts = [
        'delay_between' => 'array',
        'meta' => 'array'
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class, 'platform_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class, 'template_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(CampaignLog::class, 'campaign_id');
    }
    public function scopeScheduled($query)
    {
        return $query->where('send_type', self::$STATUS_SCHEDULED)->whereNotNull('schedule_at');
    }
    public function successRate(): Attribute
    {

        return new Attribute(
            get: function () {
                $total = $this->logs()->count();
                $success = $this->logs()->whereNotNull('send_at')->count();
                if ($total == 0) {
                    return 0;
                }
                return $success / $total * 100;
            }
        );
    }
}
