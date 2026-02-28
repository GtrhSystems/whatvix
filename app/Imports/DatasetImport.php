<?php

namespace App\Imports;

use App\Models\AiTraining;
use App\Traits\Uploader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DatasetImport
{
    use Uploader;
    public function __construct(protected $provider)
    {
    }

    public function import($request)
    {
        $jsonDataPath = $this->saveFile($request, 'dataset');
        $data = json_decode(file_get_contents($jsonDataPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format');
        }

        $validator = Validator::make($data, [
            '*' => 'required|array',
            '*.question' => 'required',
            '*.answer' => 'required',
        ], [
            '*.question.required' => 'The question field is required for all entries.',
            '*.answer.required' => 'The answer field is required for all entries.',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $aiTraining = AiTraining::create([
            'user_id' => Auth::id(),
            'provider' => $this->provider,
            'title' => $request->title,
            'status' => 'pending',
            'dataset' => $jsonDataPath,
        ]);

        return [
            'success' => true,
            'aiTraining' => $aiTraining
        ];
    }
}
