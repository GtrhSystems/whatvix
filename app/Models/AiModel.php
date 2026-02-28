<?php

namespace App\Models;

use App\Traits\HasFilter;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;

class AiModel extends Model
{
    use HasFilter, HasStatus;

    protected $fillable = [
        'provider',
        'name',
        'code',
        'max_token',
        'status'
    ];
}
