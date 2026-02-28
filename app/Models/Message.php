<?php

namespace App\Models;

use App\Models\Customer;
use App\Traits\HasModule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Message extends Model
{
    use HasFactory, HasModule;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'owner_id',
        'module',
        'conversation_id',
        'platform_id',
        'direction',
        'customer_id',
        'type',
        'body',
        'status',
        'meta',
        'created_at',
    ];

    protected $casts = [
        'body' => 'array',
        'meta' => 'array',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['created_at_diff', 'created_at_time'];


    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Get the created_at_diff
     *
     * @param  string  $value
     * @return string
     */
    public function getCreatedAtDiffAttribute($value)
    {
        return $this->created_at?->diffForHumans([
            'short' => true
        ]);
    }

    public function getCreatedAtTimeAttribute($value)
    {
        return $this->created_at?->format('d M, y | h:i A');
    }

    public function scopeUnRead($query)
    {
        return $query->where('direction', 'in')->where('status', 'received');
    }

    /**
     * Get the message body.
     *
     * @param  string|null  $key
     * @return mixed
     */
    public function getBody(?string $key = null): mixed
    {
        if (is_null($key)) {
            return $this->body;
        }

        return data_get($this->body, $key);
    }


    /**
     * Get the text content of the message.
     *
     * If the message type is "text", this will return the body of the message.
     * Otherwise, this will return the type of the message.
     *
     * @return string
     */
    public function getText()
    {
        if ($this->module == 'whatsapp') {
            $text = match ($this->type) {
                'text' => $this->getBody('body'),
                'interactive' => $this->getBody('body.text'),
                default => 'call'
            };

            return strtolower($text);
        }

        return is_string($this->getBody('text')) ? $this->getBody('text') : null;
    }

    public function flow(): HasOne
    {
        return $this->hasOne(MessageFlow::class);
    }

}
