<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiFineTuning
{
    public function createGeminiTunedModel($credential, $dataset, $title)
    {
        $newDataset = collect($dataset)->map(function ($item) {
            return [
                'text_input' => $item['question'],
                'output' => $item['answer'],
            ];
        })->toArray();
        $response = $this->makeGeminiApiRequest($credential)
            ->post("{$credential->meta['base_url']}/v1beta/tunedModels", [
                'display_name' => "$title model",
                'base_model' => 'models/gemini-1.0-pro-001',
                'tuning_task' => [
                    'hyperparameters' => [
                        'batch_size' => 1,
                        'learning_rate' => 0.001,
                        'epoch_count' => 5,
                    ],
                    'training_data' => [
                        'examples' => [
                            'examples' => $newDataset,
                        ],
                    ],
                ],
            ]);
        return $response;
    }

    public function getGeminiTunedModel($credential, $model_id, array $contents)
    {
        $response = $this->makeGeminiApiRequest($credential)
            ->post("{$credential->meta['base_url']}/v1beta/{$model_id}:generateContent?key={$credential->meta['gemini_api_key']}", [
                'contents' => $contents,
                'generationConfig' => [
                    'temperature' => 1,
                    'topK' => 64,
                    'topP' => 0.95,
                    'maxOutputTokens' => 8192,
                    'responseMimeType' => 'text/plain'
                ]
            ]);
        return $response;
    }

    public function getGeminiTunedModelState($credential, $model_id)
    {
        $response = $this->makeGeminiApiRequest($credential)
            ->get("{$credential->meta['base_url']}/v1beta/{$model_id}");

        return $response;
    }

    public function makeGeminiApiRequest($credential)
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$credential->token}",
            'x-goog-user-project' => $credential->meta['project_id'],
        ]);
    }
}
