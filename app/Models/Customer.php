<?php

namespace App\Models;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use App\Traits\HasModule;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, HasModule, HasActivityLog, HasOwner, HasFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'owner_id',
        'platform_id',
        'name',
        'uuid',
        'picture',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];


    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this
            ->hasMany(Message::class)->where('module', 'whatsapp')->where('direction', 'in');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    public function picture(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->name),
        );
    }
}
