<?php

namespace App\Models;

use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory, HasStatus;

    protected $fillable = [
        'country_id',
        'name',
        'status',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}
