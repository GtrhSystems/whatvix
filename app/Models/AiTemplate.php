<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use App\Helpers\ModelHelperConfig;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class AiTemplate extends Model
{
    use HasFactory, ModelHelper, HasFilter;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
        'fields' => 'array',
    ];

    public const INSTRUCTIONS = [
        'audio' => 'https://stablediffusionapi.com/docs/voice-cloning/text-to-audio',
        'voice_clone' => 'https://stablediffusionapi.com/docs/voice-cloning/text-to-voice',
    ];

    public function modelHelperConfig(): ModelHelperConfig
    {
        return ModelHelperConfig::create()
            ->generateUuid()
            ->files(['icon', 'preview']);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function generates(): MorphMany
    {
        return $this->morphMany(AiGenerate::class, 'generatable');
    }
}
