<?php

namespace App\Services;

use App\Exceptions\SessionException;
use App\Models\Asset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AssetService
{

    public function upload($input = 'files', ?Model $model = null, ?string $for = null)
    {
        $files = Arr::wrap(request($input));
        try {
            $createdAssets = [];

            foreach ($files as $fileEntry) {
                $fileEntry = $this->normalizeFile($fileEntry);

                $type = $fileEntry['type'];
                $assetTypes = Asset::ASSET_TYPES;

                if (!in_array($type, $assetTypes)) {
                    throw ValidationException::withMessages([
                        'files' => 'Invalid file type.'
                    ]);
                }

                request()->replace(['file' => $fileEntry['file']]);

                $this->validateFiles('file', $type);
                $savedAsset = $this->saveFiles('file');

                $createdAsset = Asset::create([
                    'uuid' => Str::uuid(),
                    'user_id' => Auth::id(),
                    'assetable_type' => $model ? get_class($model) : null,
                    'assetable_id' => $model?->id,
                    'type' => 'uploaded',
                    'name' => $savedAsset['name'],
                    'original_name' => $savedAsset['original_name'],
                    'mime_type' => $savedAsset['mime_type'],
                    'file_type' => $type,
                    'path' => $savedAsset['url'],
                    'file_size' => $savedAsset['file_size'],
                    'for' => $for ?? $type,
                    'filesystem_driver' => config('filesystems.default', 'local')
                ]);

                $createdAssets[] = $createdAsset;
            }


            request()->replace([]);

            return count($createdAssets) > 1 ? $createdAssets : ($createdAssets[0] ?? null);
        } catch (\Exception $e) {
            Log::error('File upload failed: ' . $e->getMessage());
            throw new SessionException('File upload failed: ' . $e->getMessage());
        }
    }
    private function normalizeFile($file)
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            if (!isset($file['type']) || !isset($file['file'])) {
                throw new SessionException('Invalid request format.');
            }
            return $file;
        }
        if ($file instanceof \Illuminate\Http\UploadedFile) {
            $type = explode('/', $file->getMimeType())[0];

            return  [
                'type' => $type === 'application' ? 'document' : $type,
                'file' => $file
            ];
        }
    }
    public function updateUpload($input = 'file', ?Model $model = null, ?string $for = null)
    {
        $for ??= $input;
        if ((request()->hasFile($input) || request()->hasFile("{$input}.*")) && $model && $for) {
            if (method_exists($model, $for)) {
                $oldAssets = $model->{$for}()->get();
                $this->delete($oldAssets);
                return $this->upload($input, $model, $for);
            } else {
                throw new \Exception("The relationship '{$for}' does not exist on the model.");
            }
        }

        return;
    }
    public function attach($assets, ?Model $model, $for)
    {
        if (!$assets)
            return;
        $assets = array_is_list($assets) ? $assets : [$assets];

        foreach ($assets as $asset) {
            $assetId = is_array($asset) ? ($asset['id'] ?? null) : $asset;

            if (!$assetId)
                continue;
            try {
                $existingAsset = Asset::findOrFail($assetId);

                $newAsset = $existingAsset->replicate();
                $newAsset->assetable_type = get_class($model);
                $newAsset->assetable_id = $model->id;
                $newAsset->for = $for;
                $newAsset->original_asset_id = $existingAsset->id;
                $newAsset->save();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                Log::error("Asset not found: $assetId");
            }
        }
    }
    public function updateAttach($assets, Model $model, $for, ...$oldAssets)
    {
        if (!$assets)
            return;
        $this->delete(...$oldAssets);

        return $this->attach($assets, $model, $for);
    }
    private function saveFiles($input = 'file')
    {
        $file = request($input);

        $ext = $file->extension();
        $filename = now()->timestamp . Str::random(20) . '.' . $ext;

        $path = 'uploads' . date('/y') . '/' . date('m') . '/';
        $filePath = "$path$filename";

        Storage::put($filePath, file_get_contents($file));

        $savedFile = [
            'url' => Storage::url($filePath),
            'name' => $filename,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'original_name' => $file->getClientOriginalName() ?? $filename,
        ];

        return $savedFile;
    }

    private function validateFiles($input = 'file', $assetType = 'image')
    {
        $validations = [
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:10240',
            'video' => 'max:102400|mimes:mp4,mov,avi,mkv',
            'document' => 'max:102400|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt',
            'audio' => 'max:102400|mimes:mp3,wav,aac',
        ];
        $rules[$input] = $validations[$assetType];

        request()->validate($rules);
    }

    public function delete(...$assets)
    {
        if (empty($assets))
            return;

        $assetsToDelete = collect($assets)->flatMap(function ($asset) {
            return $this->normalizeAssets($asset);
        });
        foreach ($assetsToDelete as $asset) {
            if ($asset instanceof Asset) {
                $this->deleteAsset($asset);
            } elseif (is_numeric($asset)) {
                $assetModel = Asset::find($asset);
                if ($assetModel) {
                    $this->deleteAsset($assetModel);
                }
            }
        }
    }
    private function deleteAsset($asset)
    {
        $relatedAssetsCount = Asset::where(function ($query) use ($asset) {
            if ($asset->original_asset_id) {
                $query->where('id', $asset->original_asset_id)
                    ->orWhere('original_asset_id', $asset->original_asset_id);
            } else {
                $query->where('original_asset_id', $asset->id);
            }
        })
            ->where('id', '!=', $asset->id)
            ->count();

        if ($relatedAssetsCount === 0) {
            Storage::delete($asset->path);
        }

        $asset->delete();
    }

    private function normalizeAssets($assets)
    {
        if ($assets instanceof Asset || is_numeric($assets)) {
            return [$assets];
        }

        if ($assets instanceof MorphOne || $assets instanceof MorphMany) {
            return $assets->get();
        }

        if ($assets instanceof Collection || is_array($assets)) {
            return $assets;
        }

        return [];
    }
}
