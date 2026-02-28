<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AiTraining extends Model
{
    use HasFactory;

    protected function defineHasOwner()
    {
        return ['column' => 'user_id',];
    }

    protected $guarded = [];
    protected $casts = [
        'meta' => 'array'
    ];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function scopeActive($query)
    {
        return $query->whereIn(DB::raw('LOWER(status)'), ['succeeded', 'active']);
    }
}
