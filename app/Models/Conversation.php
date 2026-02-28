<?php

namespace App\Models;

use App\Models\Platform;
use App\Traits\HasModule;
use App\Traits\HasOwner;
use App\Traits\HasStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
    use HasFactory, HasStatus, HasOwner, HasModule;

    protected $fillable = [
        'module',
        'owner_id',
        'customer_id',
        'platform_id',
        'badge_id',
        'auto_reply_enabled',
        'meta',
    ];

    protected $casts = [
        'auto_reply_enabled' => 'boolean',
        'meta' => 'array',
    ];


    //--------------global relations------------------//

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function owner(): BelongsTo
    {
        return $this->user();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    //---------------- helpers  --------------------------//

    // ------------- flow control start ---------------
    public function isFlowStarted(): bool
    {
        return boolval($this->getFlowId());
    }

    public function getFlowId(): ?string
    {
        return $this->meta['flow_id'] ?? null;
    }


    public function isFlowBlocked(): bool
    {
        $blockedUntil = $this->getMeta('blocked_until');

        if (!$blockedUntil) {
            return false;
        }

        $isValidBlock = now()->isBefore($blockedUntil);

        if (!$isValidBlock) {
            $this->unblockFlow();
        }

        return $isValidBlock;
    }

    public function unblockFlow(): bool
    {
        $meta = $this->meta;
        unset($meta['blocked_until']);
        return $this->update([
            'meta' => $meta
        ]);
    }

    // ------------- flow control end ---------------


    public function isAutoReplyEnabled(): bool
    {
        return (bool) $this->auto_reply_enabled;
    }

    public function readAllMessages(): void
    {
        DB::transaction(function () {
            $this->messages()->update(['status' => 'read']);
            $this->touch();
        });
    }

    public function getMeta($key, $default = null)
    {
        return data_get($this->meta, $key, $default);
    }
}
