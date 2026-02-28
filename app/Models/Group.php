<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasFilter;
use App\Traits\HasModule;
use App\Traits\HasOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Group extends Model
{
    use HasFactory, HasModule, HasOwner, HasFilter, HasActivityLog;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module',
        'platform_id',
        'owner_id',
        'name'
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function customers(): BelongsToMany
    {
        return $this->BelongsToMany(Customer::class);
    }
}
