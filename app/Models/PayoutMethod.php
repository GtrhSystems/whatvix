<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'currency_name',
        'image',
        'min_limit',
        'max_limit',
        'delay',
        'fixed_charge',
        'percent_charge',
        'charge_type',
        'data',
        'instruction',
        'status',
        'rates',
    ];
    protected $casts = [
        'rates' => 'json',
    ];
}
