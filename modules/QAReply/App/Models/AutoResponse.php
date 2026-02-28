<?php

namespace Modules\QAReply\App\Models;

use App\Models\User;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Modules\QAReply\App\Models\AutoResponseItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoResponse extends Model
{
    use HasFactory, HasOwner;

    protected $fillable = [
        'owner_id',
        'title',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function items()
    {
        return $this->hasMany(AutoResponseItem::class, 'auto_response_id');
    }
}
