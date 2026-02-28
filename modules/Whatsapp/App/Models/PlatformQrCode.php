<?php

namespace Modules\Whatsapp\App\Models;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlatformQrCode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'prefilled_message',
        'deep_link_url',
        'qr_image_url',
    ];


    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
