<?php

namespace Modules\NumberChecker\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NumberScanner extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['number_scanned', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
