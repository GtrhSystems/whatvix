<?php

namespace App\Models;

use App\Traits\HasOwner;
use App\Traits\HasModule;
use App\Traits\HasStatus;
use App\Traits\HasActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoReply extends Model
{
    use HasFactory, HasStatus, HasModule, HasOwner, HasActivityLog;

    protected $fillable = [
        'module',
        'owner_id',
        'platform_id',
        'template_id',
        'keywords',
        'message_type',
        'message_template',
        'status',
        'meta',
    ];

    protected $casts = [
        'keywords' => 'array',
        'meta' => 'array',
    ];

    /**
     * Scope a query to match all the given keywords.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $keywords
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMatchKeywords($query, $keywords): Builder
    {
        return $query->where(function (Builder $q) use ($keywords) {
            foreach ($keywords as $keyword) {
                $q->orWhereRaw(
                    'LOWER(JSON_UNQUOTE(JSON_EXTRACT(keywords, ?))) LIKE ?',
                    ['$[*]', '%' . strtolower($keyword) . '%']
                );
            }
        });
    }


    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }
}
