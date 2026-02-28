<?php

namespace Modules\WhatsappWeb\App\Models;

use App\Models\Platform;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BulkSendLog extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $casts = [
        "meta" => "array",
    ];


    public function platform()
    {
        return $this->belongsTo(Platform::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
