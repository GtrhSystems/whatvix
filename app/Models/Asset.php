<?php

namespace App\Models;

use App\Traits\UUID;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Number;

class Asset extends Model
{
    use HasFactory, UUID, HasFilter;

    protected $guarded = [];

    const DEFAULT_TYPES = ['uploaded', 'generated', 'external'];
    const ASSET_TYPES = ['image', 'video', 'document', 'audio'];


    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['dynamic_file_size', 'file_type', 'storage_path'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assetable()
    {
        return $this->morphTo();
    }

    public function dynamicFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                return Number::fileSize($this->getAttribute('file_size'), 2);
            }
        )->shouldCache();
    }

    public function fileType(): Attribute
    {
        return Attribute::make(
            get: function () {
                $mime = $this->mime_type; // image/jpeg
                return Str::before($mime, '/'); // image
            }
        )->shouldCache();
    }

    public function storagePath(): Attribute
    {
        return Attribute::make(
            get: function () {
                $filePath = parse_url($this->getAttribute('path'), PHP_URL_PATH);
                $filePath = ltrim($filePath, '/');
                $filePath = str_replace('/', DIRECTORY_SEPARATOR, $filePath);
                $filePath = Storage::path($filePath);
                return $filePath;
            }
        )->shouldCache();
    }
}
