<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageFlow extends Model
{
    protected $guarded = [];

    /**
     * Get the message that owns the MessageFlow
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the flow that owns the MessageFlow
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function flow()
    {
        return $this->belongsTo(Flow::class);
    }

}
