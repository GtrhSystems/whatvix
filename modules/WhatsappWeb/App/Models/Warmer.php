<?php

namespace Modules\WhatsappWeb\App\Models;

use App\Models\User;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Warmer extends Model
{
    use HasFactory, HasFilter;

    protected $guarded = [];

    protected $casts = [
        "dataset" => "array",
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
