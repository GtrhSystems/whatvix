<?php

namespace App\Models;

use App\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class WebScraping extends Model
{
    use HasFactory, UUID;

    protected $guarded = [];

    protected $casts = [
        'parameters' => 'array',
    ];

    public function scraped_data(): HasMany
    {
        return $this->hasMany(WebScrapedData::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }
}
