<?php

namespace Modules\QAReply\App\Models;

use App\Models\User;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoResponseItem extends Model
{
    use HasFactory, HasOwner;

    protected $fillable = [
        'auto_response_id',
        'owner_id',
        'key',
        'value',
    ];

    public function auto_response(): BelongsTo
    {
        return $this->belongsTo(AutoResponse::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
