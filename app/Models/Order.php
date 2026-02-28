<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use App\Helpers\ModelHelperConfig;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory, ModelHelper, HasFilter;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];


    protected $casts = [
        'meta' => 'json'
    ];



    protected $appends = ['created_at_diff', 'amountFormat'];


    public function modelHelperConfig(): ModelHelperConfig
    {
        return ModelHelperConfig::create()->generateInvoice();
    }

    public function getAmountFormatAttribute()
    {
        return amount_format($this->amount);
    }
    public function getCreatedAtDiffAttribute()
    {
        return $this->created_at?->diffForHumans();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo('App\Models\Plan');
    }

    public function gateway()
    {
        return $this->belongsTo('App\Models\Gateway');
    }
}
