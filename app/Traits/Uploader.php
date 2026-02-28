<?php

namespace App\Traits;

use App\Exceptions\SessionException;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait Uploader
{
    public $extToMimesMap = [
        'aac' => ['audio/aac'],
        'amr' => ['audio/amr'],
        'mp3' => ['audio/mpeg'],
        'm4a' => ['audio/mp4'],
        'ogg' => ['audio/ogg'],
        'txt' => ['text/plain'],
        'xls' => ['application/vnd.ms-excel'],
        'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'ppt' => ['application/vnd.ms-powerpoint'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
        'pdf' => ['application/pdf'],
        'jpg' => ['image/jpg'],
        'jpeg' => ['image/jpeg'],
        'png' => ['image/png'],
        'webp' => ['image/webp'],
        '3gp' => ['video/3gpp'],
        'mp4' => ['video/mp4'],
    ];

    private function saveFile(Request $request, string $input, bool $absolutePath = false): string
    {
        $uploadedFile = $request->file($input);
        $extension = $uploadedFile->extension();
        $this->validateFileExt($extension);

        $randomString = Str::random(20);

        $directoryPath = 'uploads' . date('/y') . '/' . date('m');
        $directory = env('FILE_UPLOAD_PATH', $directoryPath);

        $filePath = "$directory/$randomString.$extension";

        Storage::put($filePath, $uploadedFile->get());

        $url = Storage::url($filePath);

        if ($absolutePath || env('APP_ENV') === 'demo') {
            return '/' . parse_url($filePath, PHP_URL_PATH);
        }

        return $url;
    }

    public function uploadBodyContent($contents, $extension): string
    {
        $this->validateFileExt($extension);
        $randomString = Str::random(20);
        $directoryPath = 'uploads' . date('/y') . '/' . date('m');
        $directory = env('FILE_UPLOAD_PATH', $directoryPath);
        $filePath = "$directory/$randomString.$extension";
        Storage::put($filePath, $contents);
        $url = Storage::url($filePath);
        return $url;
    }

    //upload file from url/link
    private function saveFileFromUrl($url, $ext = '.png', $file = '', $type = 'image')
    {
        $this->validateFileExt($ext);
        if (empty($file)) {
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Accept-language: en\r\n"
                ]
            ]);
            $file = fopen($url, 'r', false, $context);
        }
        $filename = $type . '_' . uniqid() . $ext;

        $filePath = "uploads/$filename";
        Storage::put($filePath, $file);

        return Storage::url($filePath);
    }

    //upload multiple files
    private function multipleSaveFile(Request $request, $input)
    {
        $files = $request->file($input);
        $filePaths = [];

        foreach ($files as $file) {
            $extension = $file->extension();
            $this->validateFileExt($extension);
            $filename = now()->timestamp . Str::random(20) . '.' . $extension;
            $directoryPath = 'uploads' . date('/y') . '/' . date('m');
            $directory = env('FILE_UPLOAD_PATH', $directoryPath);
            $filePath = "$directory/$filename.$extension";
            Storage::put($filePath, file_get_contents($file));
            $filePaths[] = Storage::url($filePath);
        }

        return $filePaths;
    }

    public function removeFile($url = null)
    {
        if (empty($url)) {
            return true;
        }

        $fileName = explode('uploads', $url);
        if (isset($fileName[1])) {
            $exists_file = "uploads$fileName[1]";
            if (Storage::exists($exists_file)) {
                Storage::delete($exists_file);
            }
            return true;
        }

        return false;
    }

    private function uploadFile($input, $fallback = null): string|null
    {
        $file = $input;

        if (!($input instanceof UploadedFile)) {
            if (!request()->hasFile($input))
                return $fallback;
            $file = request()->file($input);
        }

        $extension = $file->extension();
        $this->validateFileExt($extension);

        $filename = now()->timestamp . Str::random(20) . '.' . $extension;
        $directoryPath = 'uploads' . date('/y') . '/' . date('m');
        $directory = env('FILE_UPLOAD_PATH', $directoryPath);
        $filePath = "$directory/$filename.$extension";
        Storage::put($filePath, file_get_contents($file));
        return Storage::url($filePath);
    }

    public function unlinkPublicFile(?string $url = null): void
    {
        if ($url) {
            $file_url = public_path($url);
            if (is_file($file_url)) {
                unlink($file_url);
            }
        }
    }

    public function fileSizeInMB($url)
    {
        $filePath = str_replace(env('APP_URL'), '', $url);
        $sizeInBytes = Storage::size($filePath);
        return round($sizeInBytes / 1024 / 1024, 3);
    }

    /**
     * Converts a URL to a filesystem path.
     *
     * @param string $url
     * @return string|null filesystem path, or null if the URL is invalid.
     */
    public function urlToPath($url): ?string
    {
        return Storage::path(str_replace('/', DIRECTORY_SEPARATOR, parse_url($url, PHP_URL_PATH)));
    }

    public function saveJsonFile(Request $request, string $input): string
    {
        $randomString = Str::random(20);

        $directory = 'uploads/training_dataset' . date('/y') . '/' . date('m');

        $filePath = "$directory/$randomString.json";
        $jsonData = json_encode($request[$input], JSON_PRETTY_PRINT);
        Storage::put($filePath, $jsonData);

        return Storage::url($filePath);
    }

    private function getExtension($mime)
    {
        foreach ($this->extToMimesMap as $ext => $mimes) {
            if (in_array($mime, $mimes)) {
                return $ext;
            }
        }
        return false;
    }

    private function validateFileExt($ext): void
    {
        throw_unless(
            in_array($ext, array_keys($this->extToMimesMap) ?? []),
            new SessionException("File extension: \"$ext\" is not allowed.")
        );
    }
}
