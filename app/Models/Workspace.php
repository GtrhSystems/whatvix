<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    use HasFactory, HasOwner, HasActivityLog, HasFilter;

    protected $fillable = ['name', 'description', 'owner_id', 'modules'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'modules' => 'array',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'workspace_users');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
