<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiTrainingCredential extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'provider', 'token', 'meta'];
    protected $casts = [
        'meta' => 'array',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
