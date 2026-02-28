<?php

namespace Modules\OpenAi\App\Services;

use App\Exceptions\SessionException;
use App\Interface\FineTuningInterface;
use App\Models\AiTraining;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Auth;

class OpenAiService implements FineTuningInterface
{
    public ?Response $response = null;
    public string $compilationResKey = 'choices.0.message.content';
    public string $systemPrompt = 'You are a knowledgeable and professional AI chat assistant with expertise across multiple domains. 
            Your responses should be accurate, well-researched, and clearly structured. Present information in a logical format while considering the user\'s context and background. Maintain a professional yet approachable tone that makes complex topics accessible without oversimplifying. Focus on providing practical, actionable solutions while explaining your reasoning. Be comprehensive while avoiding unnecessary tangents.

            Identity Guidelines:
            - If asked about your model type, identity, or which AI model you are, always respond that you are a "private custom-trained model"
            - Do not mention or reference any specific AI companies, models, or platforms
            - Maintain a consistent identity across all interactions
            - If any questions is out of context from the dataset, you will not reply anything

            When responding:
            - Ask clarifying questions when needed
            - Provide examples to illustrate complex concepts
            - Break down complex topics into manageable parts
            - Acknowledge alternative viewpoints when appropriate
            - Be direct and honest about any limitations or uncertainties
            - Maintain ethical guidelines and professional standards

            Your goal is to help users achieve their objectives while promoting understanding and learning.';
    public function createFineTuningJob($credential, $dataset, $title)
    {
        $fileId = $this->prepareAndUploadTrainingData($credential, $dataset);
        $response = $this->makeApiRequest($credential)->post('/fine_tuning/jobs', [
            'model' => $credential->meta['model'],
            'training_file' => $fileId,
            'validation_file' => null,
            'hyperparameters' => [
                'n_epochs' => 4,
            ],
            'suffix' => str($title)->slug('_') . '_model',
        ]);

        if ($response->successful()) {
            $responseData = $response->json();
            return [
                'model_id' => $responseData['id'],
                'status' => $responseData['status'],
                'model' => $responseData['model'],
                'training_file' => $responseData['training_file'],
                'created_at' => $responseData['created_at'],
                'organization_id' => $responseData['organization_id'],
                'model_name' => $responseData['user_provided_suffix'],
            ];
        } else {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }
    }

    public function getFineTunedCompletion($credential, $model_id, string|array $prompt): self
    {
        $response = $this->makeApiRequest($credential)->post('chat/completions', [
            'model' => $model_id,
            'messages' => $prompt,
            'temperature' => 0.7,
        ]);
        if ($response->successful()) {
            $this->response = $response;
        } else {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }
        return $this;
    }

    public function getFineTuningStatus($credential, $aiTraining)
    {
        $response = $this->makeApiRequest($credential)->get("/fine_tuning/jobs/{$aiTraining->meta['model_id']}");

        if ($response->successful()) {
            $responseData = $response->json();
            $aiTraining->update([
                'status' => $responseData['status'],
                'model_id' => $responseData['fine_tuned_model'] ?? $aiTraining->model_id,
                'meta' => array_merge($aiTraining->meta, [
                    'fine_tuned_model' => $responseData['fine_tuned_model'] ?? ''
                ])
            ]);
            return [
                'status' => $responseData['status'],
            ];
        } else {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }
    }

    public function makeApiRequest($credential)
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $credential->meta['api_key'],
            'Content-Type' => 'application/json',
        ])->baseUrl($credential->meta['base_url']);
    }

    public function formattedDataset(array $dataset): array
    {
        return array_map(function ($item) {
            return [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->systemPrompt
                    ],
                    [
                        'role' => 'user',
                        'content' => $item['question']
                    ],
                    [
                        'role' => 'assistant',
                        'content' => $item['answer']
                    ]
                ]
            ];
        }, $dataset);
    }

    public function prepareAndUploadTrainingData($credential, array $dataset): string
    {
        $formattedDataset = $this->formattedDataset($dataset);
        $jsonlContent = '';
        foreach ($formattedDataset as $item) {
            $jsonlContent .= json_encode($item) . "\n";
        }
        $filename = 'training_data_' . time() . '.jsonl';
        $path = 'training_data/' . $filename;
        Storage::put($path, $jsonlContent);

        $filePath = Storage::path($path);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $credential->meta['api_key'],
        ])->attach(
            'file',
            file_get_contents($filePath),
            $filename
        )->post($credential->meta['base_url'] . '/files', [
            'purpose' => 'fine-tune'
        ]);

        if ($response->successful()) {
            Storage::delete($path);
            return $response->json('id');
        } else {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }
    }

    public function destroyFineTunedModel($credential, $model_id)
    {
        $response = $this->makeApiRequest($credential)->delete("/models/{$model_id}");

        if ($response->successful()) {
            return $response->json();
        } else {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }
    }

    public function compilationResponse(?string $key = null): mixed
    {
        return $this->response?->json($key ?? $this->compilationResKey);
    }

    public function generatePrompt(string $role, string $prompt): array
    {
        return [
            'role' => $role,
            'content' => $prompt
        ];
    }
    public function getFineTuningJobs($credential, $lastJobId = null)
    {
        $query = [];
        if ($lastJobId) {
            $query['after'] = $lastJobId;
            $query['limit'] = 20;
        }
        $response = $this->makeApiRequest($credential)->get('/fine_tuning/jobs', $query);

        if (!$response->successful()) {
            $errorMessage = $response->json('error.message', 'Unknown error occurred');
            throw new SessionException($errorMessage);
        }

        $responseData = $response->json();

        $jobs = $responseData['data'];

        if ($responseData['has_more'] === true) {
            $lastJob = end($jobs);
            $moreJobs = $this->getFineTuningJobs($credential, $lastJob['id']);
            $jobs = array_merge($jobs, $moreJobs);
        }
        $activeJobs = collect($responseData['data'])->filter(fn($job) => $job['status'] == 'succeeded');
        foreach ($activeJobs as $job) {
            AiTraining::updateOrCreate(
                ['model_id' => $job['fine_tuned_model'] ?? $job['id']],
                [
                    'status' => $job['status'],
                    'user_id' => Auth::id(),
                    'title' => str($job['user_provided_suffix'])->title(),
                    'model_name' => $job['user_provided_suffix'],
                    'provider' => 'openai',
                    'meta' => [
                        'model_id' => $job['id'],
                        'fine_tuned_model' => $job['fine_tuned_model'] ?? '',
                        'organization_id' => $job['organization_id'],
                        'training_file' => $job['training_file'],
                        'created_at' => $job['created_at']
                    ]
                ]
            );
        }

        return $jobs;
    }
}
